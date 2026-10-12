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
    ];

    protected $casts = [
        'variables' => 'array',
        'has_document' => 'boolean',
        'has_pay_button' => 'boolean',
    ];

    /**
     * Every message the platform can send, and who receives it. Shop owners toggle these;
     * the admin attaches one approved Meta template per event and language.
     */
    public const EVENTS = [
        'sale_invoice' => ['label' => 'Sale invoice', 'recipient' => 'customer'],
        'payment_received' => ['label' => 'Payment received', 'recipient' => 'customer'],
        'due_reminder' => ['label' => 'Due reminder', 'recipient' => 'customer'],
        'purchase_record' => ['label' => 'Purchase record', 'recipient' => 'supplier'],
        'supplier_payment' => ['label' => 'Payment made', 'recipient' => 'supplier'],
        'supplier_due' => ['label' => 'Due statement', 'recipient' => 'supplier'],
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
    ];

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
            fn ($name) => ['type' => 'text', 'text' => (string) ($values[$name] ?? '')],
            array_values($this->variables ?? [])
        );
        if ($bodyParams) {
            $components[] = ['type' => 'body', 'parameters' => $bodyParams];
        }

        if ($this->has_pay_button && $buttonSuffix !== null) {
            $components[] = [
                'type' => 'button',
                'sub_type' => 'url',
                'index' => '0',
                'parameters' => [['type' => 'text', 'text' => $buttonSuffix]],
            ];
        }

        return $components;
    }
}
