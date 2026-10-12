<?php

namespace App\Services\WhatsApp;

use App\Models\AppSetting;
use App\Models\WhatsAppTemplate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Cache;

/**
 * Keeps whatsapp_templates in step with the templates on the Meta WhatsApp Business Account:
 * submitting them for review from the admin panel, pulling their review status back on demand
 * ("Sync from Meta"), and applying the status / category updates Meta pushes to the webhook.
 */
class WhatsAppTemplateSync
{
    /** Templates in Meta that no row here points at, kept from the last sync so the admin can import them. */
    public const UNLINKED_CACHE_KEY = 'whatsapp:meta_unlinked_templates';

    public const CATEGORY = 'UTILITY';

    public function __construct(private WhatsAppClient $client)
    {
    }

    public static function fromSettings(): self
    {
        return new self(WhatsAppClient::fromSettings());
    }

    /**
     * Pulls every template from Meta and updates the matching rows (same name + language code).
     * Rows Meta no longer has are marked NOT_IN_META so shops stop sending with them.
     *
     * @return array{updated: int, not_in_meta: int, unlinked: array}
     */
    public function sync(): array
    {
        $this->ensureConfigured();

        $remote = [];
        foreach ($this->client->listTemplates() as $t) {
            $remote[$t['name'] . '|' . $t['language']] = $t;
        }

        $updated = 0;
        $notInMeta = 0;
        $linked = [];

        foreach (WhatsAppTemplate::all() as $template) {
            $key = $template->meta_template_name . '|' . $template->meta_language_code;
            $meta = $remote[$key] ?? null;

            if (!$meta) {
                $template->update([
                    'meta_template_id' => null,
                    'meta_status' => 'NOT_IN_META',
                    'meta_rejected_reason' => null,
                    'meta_body' => null,
                    'meta_synced_at' => now(),
                ]);
                $notInMeta++;
                continue;
            }

            $linked[$key] = true;
            $template->update([
                'meta_template_id' => (string) $meta['id'],
                'meta_status' => strtoupper((string) $meta['status']),
                'meta_category' => $meta['category'] ?? $template->meta_category,
                'meta_rejected_reason' => $this->reason($meta['rejected_reason'] ?? null),
                'meta_body' => WhatsAppTemplate::bodyFromMetaComponents($meta['components'] ?? []),
                'meta_synced_at' => now(),
            ]);
            $updated++;
        }

        $unlinked = [];
        foreach (array_diff_key($remote, $linked) as $meta) {
            $unlinked[] = $this->describeRemote($meta);
        }
        Cache::forever(self::UNLINKED_CACHE_KEY, $unlinked);

        return ['updated' => $updated, 'not_in_meta' => $notInMeta, 'unlinked' => $unlinked];
    }

    /**
     * Every template currently on the WABA, described for the admin's test-send picker and
     * marked with the local row it is linked to (same name + language code), if any.
     */
    public function remoteTemplates(): array
    {
        $this->ensureConfigured();

        $local = WhatsAppTemplate::get(['id', 'key', 'language', 'meta_template_name', 'meta_language_code'])
            ->keyBy(fn ($t) => $t->meta_template_name . '|' . $t->meta_language_code);

        return collect($this->client->listTemplates())
            ->map(function ($meta) use ($local) {
                $described = $this->describeRemote($meta);
                $linked = $local->get($meta['name'] . '|' . $meta['language']);
                $described['linked_id'] = $linked?->id;
                $described['linked_event'] = $linked ? (WhatsAppTemplate::EVENTS[$linked->key]['label'] ?? $linked->key) : null;

                return $described;
            })
            ->sortBy(fn ($t) => [$t['status'] === 'APPROVED' ? 0 : 1, $t['linked_id'] ? 0 : 1, $t['name'], $t['language']])
            ->values()
            ->all();
    }

    /** Finds one template on the WABA by name + language code, with its raw components. */
    public function findRemote(string $name, string $languageCode): ?array
    {
        $this->ensureConfigured();

        foreach ($this->client->listTemplates() as $meta) {
            if ($meta['name'] === $name && $meta['language'] === $languageCode) {
                return $meta;
            }
        }

        return null;
    }

    /**
     * Send-time components for a test of any Meta template: every body / text-header variable
     * gets a sample value (positional {{1}} or named {{customer_name}}), a media header uses
     * $mediaUrl, and a dynamic URL button gets the suffix "test".
     */
    public static function testComponents(array $meta, ?string $mediaUrl = null): array
    {
        $components = [];

        foreach ($meta['components'] ?? [] as $c) {
            $type = strtoupper($c['type'] ?? '');

            if ($type === 'HEADER') {
                $format = strtoupper($c['format'] ?? 'TEXT');
                if (in_array($format, ['DOCUMENT', 'IMAGE', 'VIDEO'], true)) {
                    if (!$mediaUrl) {
                        throw new WhatsAppException('This template has a ' . strtolower($format) . ' header — enter a public sample URL for it.');
                    }
                    $media = strtolower($format);
                    $components[] = ['type' => 'header', 'parameters' => [[
                        'type' => $media,
                        $media => array_filter(['link' => $mediaUrl, 'filename' => $media === 'document' ? 'INV-1024.pdf' : null]),
                    ]]];
                } elseif ($params = self::sampleParameters($c['text'] ?? '')) {
                    $components[] = ['type' => 'header', 'parameters' => $params];
                }
            }

            if ($type === 'BODY' && ($params = self::sampleParameters($c['text'] ?? ''))) {
                $components[] = ['type' => 'body', 'parameters' => $params];
            }

            if ($type === 'BUTTONS') {
                foreach (array_values($c['buttons'] ?? []) as $i => $button) {
                    if (strtoupper($button['type'] ?? '') === 'URL' && str_contains($button['url'] ?? '', '{{')) {
                        $components[] = ['type' => 'button', 'sub_type' => 'url', 'index' => (string) $i, 'parameters' => [['type' => 'text', 'text' => 'test']]];
                    }
                }
            }
        }

        return $components;
    }

    /** Sample text parameters for the {{n}} / {{name}} placeholders in a component's text. */
    private static function sampleParameters(string $text): array
    {
        preg_match_all('/\{\{\s*([a-z0-9_]+)\s*\}\}/i', $text, $m);
        $names = array_values(array_unique($m[1]));
        if (!$names) {
            return [];
        }

        $samples = WhatsAppTemplate::sampleValues();

        // Positional templates are filled in {{1}}, {{2}} ... order; named ones carry their name.
        if (ctype_digit($names[0])) {
            sort($names, SORT_NUMERIC);

            return array_map(fn ($n) => ['type' => 'text', 'text' => 'Sample ' . $n], $names);
        }

        return array_map(fn ($n) => [
            'type' => 'text',
            'parameter_name' => $n,
            'text' => (string) ($samples[$n] ?? 'Sample ' . str_replace('_', ' ', $n)),
        ], $names);
    }

    /**
     * Sends the template to Meta for review — a new template, or a resubmission of one Meta
     * already holds (approved / rejected / paused). The row goes to PENDING until Meta decides.
     */
    public function submit(WhatsAppTemplate $template): void
    {
        $this->ensureConfigured();

        if ($template->meta_template_id && !$template->canEditInMeta()) {
            throw new WhatsAppException("This template is {$template->meta_status} in Meta and can't be resubmitted right now — sync again once Meta has reviewed it.");
        }

        $components = $template->metaComponents(url('/pay') . '/', $template->has_document ? $this->sampleDocumentHandle() : null);

        if ($template->canEditInMeta()) {
            $this->client->editTemplate($template->meta_template_id, $template->meta_category ?: self::CATEGORY, $components);
            $result = ['id' => $template->meta_template_id, 'status' => 'PENDING', 'category' => $template->meta_category];
        } else {
            $result = $this->client->createTemplate($template->meta_template_name, $template->meta_language_code, self::CATEGORY, $components);
        }

        $template->update([
            'meta_template_id' => (string) ($result['id'] ?? $template->meta_template_id),
            'meta_status' => strtoupper((string) ($result['status'] ?? 'PENDING')),
            'meta_category' => $result['category'] ?? self::CATEGORY,
            'meta_rejected_reason' => null,
            'meta_body' => $template->body,
            'meta_synced_at' => now(),
        ]);
    }

    /**
     * Applies a webhook change Meta pushed for the WABA. Returns the template it updated, if any.
     * Fields: message_template_status_update (review result) and template_category_update.
     */
    public function applyWebhookChange(string $field, array $value): ?WhatsAppTemplate
    {
        if (!in_array($field, ['message_template_status_update', 'template_category_update'], true)) {
            return null;
        }

        $template = $this->findForWebhook($value);
        if (!$template) {
            return null;
        }

        if ($field === 'template_category_update') {
            $template->update(['meta_category' => $value['new_category'] ?? $template->meta_category, 'meta_synced_at' => now()]);

            return $template;
        }

        $status = strtoupper((string) ($value['event'] ?? ''));
        if ($status === '') {
            return null;
        }

        $template->update([
            'meta_template_id' => $template->meta_template_id ?: (isset($value['message_template_id']) ? (string) $value['message_template_id'] : null),
            'meta_status' => $status,
            'meta_rejected_reason' => $status === 'REJECTED' ? $this->reason($value['reason'] ?? null) : null,
            'meta_synced_at' => now(),
        ]);

        return $template;
    }

    private function findForWebhook(array $value): ?WhatsAppTemplate
    {
        if (!empty($value['message_template_id'])) {
            $template = WhatsAppTemplate::where('meta_template_id', (string) $value['message_template_id'])->first();
            if ($template) {
                return $template;
            }
        }

        if (!empty($value['message_template_name']) && !empty($value['message_template_language'])) {
            return WhatsAppTemplate::where('meta_template_name', $value['message_template_name'])
                ->where('meta_language_code', $value['message_template_language'])
                ->first();
        }

        return null;
    }

    /** What the admin needs to import a template that exists only in Meta. */
    private function describeRemote(array $meta): array
    {
        $components = $meta['components'] ?? [];
        $hasDocument = false;
        $hasPayButton = false;
        foreach ($components as $c) {
            $type = strtoupper($c['type'] ?? '');
            if ($type === 'HEADER' && strtoupper($c['format'] ?? '') === 'DOCUMENT') {
                $hasDocument = true;
            }
            if ($type === 'BUTTONS') {
                foreach ($c['buttons'] ?? [] as $b) {
                    $hasPayButton = $hasPayButton || (strtoupper($b['type'] ?? '') === 'URL' && str_contains($b['url'] ?? '', '{{1}}'));
                }
            }
        }

        $headerFormat = null;
        foreach ($components as $c) {
            if (strtoupper($c['type'] ?? '') === 'HEADER') {
                $headerFormat = strtoupper($c['format'] ?? 'TEXT');
            }
        }

        return [
            'name' => $meta['name'],
            'language' => $meta['language'],
            'status' => strtoupper((string) $meta['status']),
            'category' => $meta['category'] ?? null,
            'body' => WhatsAppTemplate::bodyFromMetaComponents($components) ?? '',
            'header_format' => $headerFormat,
            'has_document' => $hasDocument,
            'has_pay_button' => $hasPayButton,
        ];
    }

    /** Meta needs an uploaded sample PDF for a document header; this one is generated on the fly. */
    private function sampleDocumentHandle(): string
    {
        $appId = (string) AppSetting::get('whatsapp_app_id', '');
        if ($appId === '') {
            throw new WhatsAppException('Templates with a PDF header need the Meta App ID (Setup tab) to upload a sample PDF.');
        }

        $pdf = Pdf::loadHTML('<h2>Invoice INV-1024</h2><p>Sample invoice from Shree Traders — Amount ₹2,450.00</p>')->output();

        return $this->client->uploadSampleMedia($appId, 'INV-1024.pdf', 'application/pdf', $pdf);
    }

    private function ensureConfigured(): void
    {
        if (!$this->client->canManageTemplates()) {
            throw new WhatsAppException('Save the WhatsApp Business Account ID and Access Token first.');
        }
    }

    /** Meta sends "NONE" when there is no rejection reason. */
    private function reason(?string $reason): ?string
    {
        return $reason && strtoupper($reason) !== 'NONE' ? $reason : null;
    }
}
