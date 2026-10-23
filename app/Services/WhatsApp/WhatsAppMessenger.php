<?php

namespace App\Services\WhatsApp;

use App\Jobs\SendWhatsAppMessage;
use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\WhatsAppMessageLog;
use App\Models\WhatsAppTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

/**
 * Entry point for every WhatsApp message a shop sends: picks the admin template for the event
 * in the shop's language, fills in the values, writes a queued log row and dispatches the job.
 */
class WhatsAppMessenger
{
    /** How long Meta has to fetch an invoice PDF from the signed link (covers job retries). */
    private const DOCUMENT_LINK_TTL_HOURS = 48;

    public function __construct(private WhatsAppWallet $wallet)
    {
    }

    /**
     * @param  array{sale?: Sale, purchase?: Purchase, amount?: float, button_suffix?: string}  $context
     *
     * @throws WhatsAppException when the message can't be sent (reason is safe to show the shop owner);
     *                            InsufficientCreditsException when the shop has no credits left
     */
    public function send(Shop $shop, string $event, Customer|Supplier $recipient, array $context = []): WhatsAppMessageLog
    {
        if (!isset(WhatsAppTemplate::EVENTS[$event])) {
            throw new \InvalidArgumentException("Unknown WhatsApp event [{$event}].");
        }
        $recipientType = $recipient instanceof Customer ? 'customer' : 'supplier';
        if (WhatsAppTemplate::EVENTS[$event]['recipient'] !== $recipientType) {
            throw new \InvalidArgumentException("Event [{$event}] is not sent to a {$recipientType}.");
        }

        if (!WhatsAppClient::isEnabled()) {
            throw new WhatsAppException('WhatsApp messaging is currently unavailable.');
        }

        if ($recipient->whatsapp_opt_out) {
            throw new WhatsAppException("{$recipient->name} has turned off WhatsApp messages.");
        }

        $phone = WhatsAppClient::normalizePhone($recipient->mobile);
        if (!$phone) {
            throw new WhatsAppException("{$recipient->name} does not have a valid mobile number.");
        }

        $preferredLang = $context['language'] ?? null;
        $template = $this->templateFor($shop, $event, $preferredLang);
        if (!$template) {
            throw new WhatsAppException('This WhatsApp message is not available yet.');
        }

        $sale = $context['sale'] ?? null;
        $purchase = $context['purchase'] ?? null;

        $payload = ['values' => $this->values($shop, $recipient, $sale, $purchase, $context['amount'] ?? null)];
        if ($template->has_document && ($document = $this->document($sale, $purchase, $template->language))) {
            $payload += $document;
        }
        if (isset($context['button_suffix']) && trim((string) $context['button_suffix']) !== '') {
            $payload['button_suffix'] = trim((string) $context['button_suffix']);
        }

        // The log row and its one-credit debit commit together; no credits → nothing is queued.
        $log = DB::transaction(function () use ($shop, $event, $recipientType, $recipient, $phone, $template, $sale, $purchase, $payload) {
            $log = WhatsAppMessageLog::create([
                'shop_id' => $shop->id,
                'event' => $event,
                'recipient_type' => $recipientType,
                'recipient_id' => $recipient->id,
                'phone' => $phone,
                'whatsapp_template_id' => $template->id,
                'template_name' => $template->meta_template_name,
                'language' => $template->language,
                'sale_id' => $sale?->id,
                'purchase_id' => $purchase?->id,
                'payload' => $payload,
                'status' => 'queued',
            ]);
            $this->wallet->debitForMessage($log);

            return $log;
        });

        DB::afterCommit(function () use ($log) {
            try {
                SendWhatsAppMessage::dispatchSync($log->id);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("WhatsApp message send error for log #{$log->id}: " . $e->getMessage());
            }
        });

        return $log;
    }

    /** The active, Meta-approved template for the event in the given or shop owner's language, falling back to English. */
    public function templateFor(Shop $shop, string $event, ?string $preferredLang = null): ?WhatsAppTemplate
    {
        $language = $preferredLang
            ?: request()?->header('X-Locale')
            ?: request()?->query('locale')
            ?: request()?->input('locale')
            ?: app()->getLocale()
            ?: $shop->owner?->language
            ?: 'en';

        $language = strtolower(trim((string) $language));
        $supportedLanguages = ['en', 'gu', 'hi', 'bn', 'brx', 'doi', 'kn', 'ks', 'kok', 'mai', 'ml', 'mni', 'mr', 'ne', 'or', 'pa', 'sa', 'sat', 'sd', 'ta', 'te', 'ur'];
        if (!in_array($language, $supportedLanguages, true)) {
            $language = 'en';
        }

        return WhatsAppTemplate::sendable()->where('key', $event)
            ->whereIn('language', array_unique([$language, 'en']))
            ->get()
            ->sortBy(fn ($t) => $t->language === $language ? 0 : 1)
            ->first();
    }

    public function values(Shop $shop, Customer|Supplier $recipient, ?Sale $sale = null, ?Purchase $purchase = null, ?float $amount = null): array
    {
        $amount ??= $sale ? (float) $sale->grand_total : ($purchase ? (float) $purchase->total_amount : null);
        $date = $sale?->sale_date ?? $purchase?->purchase_date ?? now();

        $shopMobile = trim((string) ($shop->mobile ?: ($shop->owner?->mobile ?: '')));
        if ($shopMobile === '') {
            $shopMobile = 'Contact Shop';
        }

        $partyName = trim((string) $recipient->name);
        if ($partyName === '') {
            $partyName = 'Customer';
        }

        $shopName = trim((string) $shop->name);
        if ($shopName === '') {
            $shopName = 'Our Shop';
        }

        $invoiceNo = trim((string) ($sale?->sale_number ?? $purchase?->purchase_number ?? ''));
        if ($invoiceNo === '') {
            $invoiceNo = 'N/A';
        }

        // Use net_balance which matches the Customer tab (Due minus Credit / Advance)
        $dueAmount = (float) ($recipient->net_balance !== null ? $recipient->net_balance : ($recipient->due_amount ?? 0));

        return [
            'party_name' => $partyName,
            'shop_name' => $shopName,
            'shop_mobile' => $shopMobile,
            'invoice_no' => $invoiceNo,
            'amount' => $amount !== null ? $this->money($shop, $amount) : '₹0.00',
            'due_amount' => $this->money($shop, max(0, $dueAmount)),
            'date' => $date->format('d M Y'),
        ];
    }

    private function document(?Sale $sale, ?Purchase $purchase, ?string $locale = null): ?array
    {
        // Signed over the path only (signed:relative), so a proxy changing scheme/host can't break it.
        $expires = now()->addHours(self::DOCUMENT_LINK_TTL_HOURS);
        $params = $locale ? ['locale' => $locale] : [];

        if ($sale) {
            return [
                'document_url' => url(URL::temporarySignedRoute('whatsapp.invoice.sale', $expires, ['id' => $sale->id] + $params, false)),
                'document_name' => 'Invoice-' . $sale->sale_number . '.pdf',
            ];
        }
        if ($purchase) {
            return [
                'document_url' => url(URL::temporarySignedRoute('whatsapp.invoice.purchase', $expires, ['id' => $purchase->id] + $params, false)),
                'document_name' => 'PurchaseInvoice-' . $purchase->purchase_number . '.pdf',
            ];
        }

        return null;
    }

    private function money(Shop $shop, float $amount): string
    {
        $currency = strtoupper((string) ($shop->currency ?: 'INR'));
        $symbol = $currency === 'INR' ? '₹' : $currency . ' ';

        return $symbol . number_format($amount, 2);
    }
}
