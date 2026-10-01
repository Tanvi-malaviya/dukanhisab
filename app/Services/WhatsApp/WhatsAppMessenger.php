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

        $phone = WhatsAppClient::normalizePhone($recipient->mobile);
        if (!$phone) {
            throw new WhatsAppException("{$recipient->name} does not have a valid mobile number.");
        }

        $template = $this->templateFor($shop, $event);
        if (!$template) {
            throw new WhatsAppException('This WhatsApp message is not available yet.');
        }

        $sale = $context['sale'] ?? null;
        $purchase = $context['purchase'] ?? null;

        $payload = ['values' => $this->values($shop, $recipient, $sale, $purchase, $context['amount'] ?? null)];
        if ($template->has_document && ($document = $this->document($sale, $purchase))) {
            $payload += $document;
        }
        if ($template->has_pay_button && isset($context['button_suffix'])) {
            $payload['button_suffix'] = $context['button_suffix'];
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

        SendWhatsAppMessage::dispatch($log->id)->afterCommit();

        return $log;
    }

    /** The active template for the event in the shop owner's language, falling back to English. */
    public function templateFor(Shop $shop, string $event): ?WhatsAppTemplate
    {
        $language = $shop->owner?->language ?: 'en';

        return WhatsAppTemplate::where('key', $event)->where('status', 'active')
            ->whereIn('language', array_unique([$language, 'en']))
            ->get()
            ->sortBy(fn ($t) => $t->language === $language ? 0 : 1)
            ->first();
    }

    public function values(Shop $shop, Customer|Supplier $recipient, ?Sale $sale = null, ?Purchase $purchase = null, ?float $amount = null): array
    {
        $amount ??= $sale ? (float) $sale->grand_total : ($purchase ? (float) $purchase->total_amount : null);
        $date = $sale?->sale_date ?? $purchase?->purchase_date ?? now();

        return [
            'party_name' => $recipient->name,
            'shop_name' => $shop->name,
            'shop_mobile' => (string) $shop->mobile,
            'invoice_no' => (string) ($sale?->sale_number ?? $purchase?->purchase_number ?? ''),
            'amount' => $amount !== null ? $this->money($shop, $amount) : '',
            'due_amount' => $this->money($shop, max(0, (float) $recipient->net_balance)),
            'date' => $date->format('d M Y'),
        ];
    }

    private function document(?Sale $sale, ?Purchase $purchase): ?array
    {
        // Signed over the path only (signed:relative), so a proxy changing scheme/host can't break it.
        $expires = now()->addHours(self::DOCUMENT_LINK_TTL_HOURS);

        if ($sale) {
            return [
                'document_url' => url(URL::temporarySignedRoute('whatsapp.invoice.sale', $expires, ['id' => $sale->id], false)),
                'document_name' => 'Invoice-' . $sale->sale_number . '.pdf',
            ];
        }
        if ($purchase) {
            return [
                'document_url' => url(URL::temporarySignedRoute('whatsapp.invoice.purchase', $expires, ['id' => $purchase->id], false)),
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
