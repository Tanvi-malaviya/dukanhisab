<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppTemplate extends Model
{
    protected $table = 'whatsapp_templates';

    protected $fillable = [
        'key',
        'language',
        'meta_template_name',
        'meta_language_code',
        'body',
        'variables',
        'has_document',
        'has_pay_button',
        'status',
        'meta_template_id',
        'meta_status',
        'meta_category',
        'meta_rejected_reason',
        'meta_body',
        'meta_synced_at',
    ];

    protected $casts = [
        'variables' => 'array',
        'has_document' => 'boolean',
        'has_pay_button' => 'boolean',
        'meta_synced_at' => 'datetime',
    ];

    /** Meta review states in which a template can still be edited and resubmitted. */
    public const META_EDITABLE_STATUSES = ['APPROVED', 'REJECTED', 'PAUSED'];

    /** Label on the due reminder's Pay Now URL button as submitted to Meta. */
    public const PAY_BUTTON_TEXT = 'Pay Now';

    /**
     * Shop-level events toggled by shop owners in app settings.
     */
    public const SHOP_EVENTS = [
        'sale_invoice' => ['label' => 'Sale invoice', 'recipient' => 'customer', 'type' => 'shop'],
        'payment_received' => ['label' => 'Payment received', 'recipient' => 'customer', 'type' => 'shop'],
        'due_reminder' => ['label' => 'Due reminder', 'recipient' => 'customer', 'type' => 'shop'],
        'purchase_record' => ['label' => 'Purchase record', 'recipient' => 'supplier', 'type' => 'shop'],
        'supplier_payment' => ['label' => 'Payment made', 'recipient' => 'supplier', 'type' => 'shop'],
        'supplier_due' => ['label' => 'Due statement', 'recipient' => 'supplier', 'type' => 'shop'],
    ];

    /**
     * Platform lifecycle events triggered automatically by system actions.
     */
    public const PLATFORM_EVENTS = [
        'user_registered' => ['label' => 'User Welcome', 'recipient' => 'user', 'type' => 'platform'],
        'shop_added' => ['label' => 'Shop Added', 'recipient' => 'user', 'type' => 'platform'],
        'website_purchased' => ['label' => 'Shop Website Purchased', 'recipient' => 'user', 'type' => 'platform'],
        'whatsapp_service_purchased' => ['label' => 'WhatsApp Service Purchased', 'recipient' => 'user', 'type' => 'platform'],
        'whatsapp_low_credits' => ['label' => 'WhatsApp Low Credits (<50)', 'recipient' => 'user', 'type' => 'platform'],
        'plan_purchased' => ['label' => 'Plan Purchased', 'recipient' => 'user', 'type' => 'platform'],
        'plan_upgraded' => ['label' => 'Plan Upgraded', 'recipient' => 'user', 'type' => 'platform'],
        'plan_downgraded' => ['label' => 'Plan Downgraded', 'recipient' => 'user', 'type' => 'platform'],
        'plan_cancelled' => ['label' => 'Plan Cancelled', 'recipient' => 'user', 'type' => 'platform'],
    ];

    /**
     * Every message the platform can send, combining shop and platform events.
     */
    public const EVENTS = [
        // Shop Events
        'sale_invoice' => ['label' => 'Sale invoice', 'recipient' => 'customer', 'type' => 'shop'],
        'payment_received' => ['label' => 'Payment received', 'recipient' => 'customer', 'type' => 'shop'],
        'due_reminder' => ['label' => 'Due reminder', 'recipient' => 'customer', 'type' => 'shop'],
        'purchase_record' => ['label' => 'Purchase record', 'recipient' => 'supplier', 'type' => 'shop'],
        'supplier_payment' => ['label' => 'Payment made', 'recipient' => 'supplier', 'type' => 'shop'],
        'supplier_due' => ['label' => 'Due statement', 'recipient' => 'supplier', 'type' => 'shop'],
        // Platform Lifecycle Events
        'user_registered' => ['label' => 'User Welcome', 'recipient' => 'user', 'type' => 'platform'],
        'shop_added' => ['label' => 'Shop Added', 'recipient' => 'user', 'type' => 'platform'],
        'website_purchased' => ['label' => 'Shop Website Purchased', 'recipient' => 'user', 'type' => 'platform'],
        'whatsapp_service_purchased' => ['label' => 'WhatsApp Service Purchased', 'recipient' => 'user', 'type' => 'platform'],
        'whatsapp_low_credits' => ['label' => 'WhatsApp Low Credits (<50)', 'recipient' => 'user', 'type' => 'platform'],
        'plan_purchased' => ['label' => 'Plan Purchased', 'recipient' => 'user', 'type' => 'platform'],
        'plan_upgraded' => ['label' => 'Plan Upgraded', 'recipient' => 'user', 'type' => 'platform'],
        'plan_downgraded' => ['label' => 'Plan Downgraded', 'recipient' => 'user', 'type' => 'platform'],
        'plan_cancelled' => ['label' => 'Plan Cancelled', 'recipient' => 'user', 'type' => 'platform'],
    ];

    public const LANGUAGES = [
        'en' => 'English',
        'gu' => 'Gujarati',
        'hi' => 'Hindi',
    ];

    /** Values a template body can use, with the sample shown in previews and test sends. */
    public const VARIABLES = [
        'party_name' => ['label' => 'Customer / supplier name', 'sample' => 'Ramesh'],
        'shop_name' => ['label' => 'Shop name', 'sample' => 'Shree Traders'],
        'shop_mobile' => ['label' => 'Shop mobile', 'sample' => '9876543210'],
        'invoice_no' => ['label' => 'Invoice / bill number', 'sample' => 'INV-1024'],
        'amount' => ['label' => 'Amount', 'sample' => '₹2,450.00'],
        'due_amount' => ['label' => 'Pending due', 'sample' => '₹1,200.00'],
        'date' => ['label' => 'Date', 'sample' => '01 Oct 2026'],
        // Platform lifecycle variables
        'name' => ['label' => 'User full name', 'sample' => 'Rajesh Sharma'],
        'plan_name' => ['label' => 'Plan name', 'sample' => 'Premium Yearly'],
        'old_plan' => ['label' => 'Previous plan', 'sample' => 'Basic'],
        'new_plan' => ['label' => 'New plan', 'sample' => 'Premium Yearly'],
        'expiry_date' => ['label' => 'Plan expiry date', 'sample' => '28 Oct 2027'],
        'effective_date' => ['label' => 'Effective date', 'sample' => '28 Oct 2026'],
        'current_expiry_date' => ['label' => 'Current plan expiry', 'sample' => '28 Nov 2026'],
        'remaining_credits' => ['label' => 'Remaining credits', 'sample' => '42'],
        'website_plan' => ['label' => 'Website plan title', 'sample' => 'Online Store Starter'],
        'service_name' => ['label' => 'Service name', 'sample' => 'WhatsApp Messaging'],
        'whatsapp_plan' => ['label' => 'WhatsApp pack name', 'sample' => 'Pro Pack (5,000 Credits)'],
        'order_id' => ['label' => 'Order / Transaction ID', 'sample' => 'ORD-100293'],
        'payment_status' => ['label' => 'Payment status', 'sample' => 'Paid'],
        'subscription_status' => ['label' => 'Subscription status', 'sample' => 'Active'],
        'cancellation_date' => ['label' => 'Cancellation date', 'sample' => '28 Oct 2026'],
        'access_end_date' => ['label' => 'Access end date', 'sample' => '28 Nov 2026'],
        'cancellation_status' => ['label' => 'Cancellation status', 'sample' => 'Cancelled'],
        'downgrade_effect_note' => ['label' => 'Downgrade effect note', 'sample' => 'Your new plan benefits will become effective on the scheduled date.'],
        'cancellation_effect_note' => ['label' => 'Cancellation effect note', 'sample' => 'Your plan benefits will remain active until the end of your billing period.'],
    ];

    /**
     * Templates shops may send with: active here and not blocked in Meta. Rows never submitted or
     * synced (meta_status null) predate the Meta sync and are trusted as approved.
     */
    public function scopeSendable($query)
    {
        return $query->where('status', 'active')
            ->where(fn ($q) => $q->whereNull('meta_status')->orWhereIn('meta_status', ['APPROVED', 'PENDING']));
    }

    public function isSendable(): bool
    {
        return $this->status === 'active' && in_array($this->meta_status, [null, 'APPROVED', 'PENDING'], true);
    }

    /** Submitted before, so a resubmission edits the same Meta template rather than creating one. */
    public function canEditInMeta(): bool
    {
        return $this->meta_template_id !== null && in_array($this->meta_status, self::META_EDITABLE_STATUSES, true);
    }

    /** The body here was changed after Meta last reported it — it needs resubmitting. */
    public function differsFromMeta(): bool
    {
        return $this->meta_body !== null && trim($this->meta_body) !== trim($this->body);
    }

    public function getRecipientAttribute(): ?string
    {
        return self::EVENTS[$this->key]['recipient'] ?? null;
    }

    public static function sampleValues(): array
    {
        return array_map(fn ($v) => $v['sample'], self::VARIABLES);
    }

    /** Body text with {{1}}, {{2}} ... replaced by the given values (keyed by variable name). */
    public function renderBody(array $values): string
    {
        $replacements = [];
        foreach (array_values($this->variables ?? []) as $i => $name) {
            $replacements['{{' . ($i + 1) . '}}'] = (string) ($values[$name] ?? '');
        }

        return strtr($this->body, $replacements);
    }

    /**
     * Cloud API "components" for this template. The document header and the Pay Now URL-button
     * suffix are only included when the template was approved with them.
     */
    public function buildComponents(array $values, ?string $documentUrl = null, ?string $documentName = null, ?string $buttonSuffix = null): array
    {
        $components = [];

        if ($this->has_document && $documentUrl) {
            $components[] = [
                'type' => 'header',
                'parameters' => [[
                    'type' => 'document',
                    'document' => array_filter(['link' => $documentUrl, 'filename' => $documentName]),
                ]],
            ];
        }

        $bodyParams = array_map(
            function ($name) use ($values) {
                $val = trim((string) ($values[$name] ?? ''));
                if ($val === '') {
                    $val = match ($name) {
                        'shop_mobile' => 'Contact Shop',
                        'party_name' => 'Customer',
                        'shop_name' => 'Our Shop',
                        'amount', 'due_amount' => '₹0.00',
                        'date' => date('d M Y'),
                        'invoice_no' => 'N/A',
                        default => '-',
                    };
                }

                return ['type' => 'text', 'text' => $val];
            },
            array_values($this->variables ?? [])
        );
        if ($bodyParams) {
            $components[] = ['type' => 'body', 'parameters' => $bodyParams];
        }

        if ($this->has_pay_button) {
            $suffix = trim((string) $buttonSuffix);
            if ($suffix === '') {
                $suffix = 'pay';
            }
            $components[] = [
                'type' => 'button',
                'sub_type' => 'url',
                'index' => '0',
                'parameters' => [['type' => 'text', 'text' => $suffix]],
            ];
        }

        return $components;
    }

    /**
     * Components for Meta's template create/edit API: an optional DOCUMENT header (Meta needs an
     * uploaded sample, passed as $documentHandle), the body with sample values for each {{n}},
     * and the due reminder's Pay Now URL button with a dynamic suffix.
     */
    public function metaComponents(string $payUrlBase, ?string $documentHandle = null): array
    {
        $components = [];

        if ($this->has_document) {
            $components[] = array_filter([
                'type' => 'HEADER',
                'format' => 'DOCUMENT',
                'example' => $documentHandle ? ['header_handle' => [$documentHandle]] : null,
            ]);
        }

        $body = ['type' => 'BODY', 'text' => $this->body];
        $samples = self::sampleValues();
        $examples = array_map(fn ($name) => (string) ($samples[$name] ?? $name), array_values($this->variables ?? []));
        if ($examples) {
            $body['example'] = ['body_text' => [$examples]];
        }
        $components[] = $body;

        if ($this->has_pay_button) {
            $components[] = [
                'type' => 'BUTTONS',
                'buttons' => [[
                    'type' => 'URL',
                    'text' => self::PAY_BUTTON_TEXT,
                    'url' => $payUrlBase . '{{1}}',
                    'example' => ['sample-token'],
                ]],
            ];
        }

        return $components;
    }

    /** Body text out of the components Meta returns when listing templates. */
    public static function bodyFromMetaComponents(array $components): ?string
    {
        foreach ($components as $component) {
            if (strtoupper($component['type'] ?? '') === 'BODY') {
                return $component['text'] ?? null;
            }
        }

        return null;
    }
}
