<?php

namespace App\Services\WhatsApp;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper over the Meta WhatsApp Cloud API, sending from the single platform-owned
 * DukanHisab number. Credentials come from the admin WhatsApp settings (AppSetting), with the
 * access token stored encrypted.
 */
class WhatsAppClient
{
    public const DEFAULT_API_VERSION = 'v23.0';
    private const BASE_URL = 'https://graph.facebook.com';

    public function __construct(
        private string $accessToken,
        private string $phoneNumberId,
        private string $apiVersion = self::DEFAULT_API_VERSION,
        private string $wabaId = '',
    ) {
    }

    public static function fromSettings(): self
    {
        return new self(
            self::storedAccessToken(),
            (string) AppSetting::get('whatsapp_phone_number_id', ''),
            AppSetting::get('whatsapp_api_version') ?: self::DEFAULT_API_VERSION,
            (string) AppSetting::get('whatsapp_waba_id', ''),
        );
    }

    public static function storedAccessToken(): string
    {
        $encrypted = AppSetting::get('whatsapp_access_token');
        if (!$encrypted) {
            return '';
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (\Throwable) {
            return '';
        }
    }

    public static function isEnabled(): bool
    {
        return AppSetting::get('whatsapp_enabled', 'no') === 'yes';
    }

    public function isConfigured(): bool
    {
        return $this->accessToken !== '' && $this->phoneNumberId !== '';
    }

    /**
     * Returns the Meta App ID from settings, or auto-discovers it from Meta API using the access token.
     */
    public function getAppId(): ?string
    {
        $appId = trim((string) AppSetting::get('whatsapp_app_id', ''));
        if ($appId !== '') {
            return $appId;
        }

        if ($this->accessToken === '') {
            return null;
        }

        try {
            $resp = Http::withToken($this->accessToken)
                ->timeout(10)
                ->get($this->url('app'));
            if ($resp->successful() && !empty($resp->json('id'))) {
                $discovered = (string) $resp->json('id');
                AppSetting::set('whatsapp_app_id', $discovered);
                return $discovered;
            }
        } catch (\Throwable) {
            // fallback if app inspection fails
        }

        return null;
    }

    /** Details of the sending number — used to verify the credentials from the admin panel. */
    public function phoneNumberInfo(): array
    {
        $response = Http::withToken($this->accessToken)
            ->timeout(10)
            ->get($this->url($this->phoneNumberId), [
                'fields' => 'display_phone_number,verified_name,quality_rating',
            ]);

        if (!$response->successful()) {
            throw WhatsAppException::fromResponse($response);
        }

        return $response->json();
    }

    /**
     * Sends an approved template message and returns Meta's message id (wamid...).
     *
     * @param  array  $components  Cloud API template components (see WhatsAppTemplate::buildComponents)
     */
    public function sendTemplate(string $to, string $templateName, string $languageCode, array $components = []): string
    {
        // Ensure Meta never receives an empty text value in any text parameter
        if (!empty($components)) {
            foreach ($components as &$comp) {
                if (!empty($comp['parameters']) && is_array($comp['parameters'])) {
                    foreach ($comp['parameters'] as &$param) {
                        if (($param['type'] ?? '') === 'text' && (!isset($param['text']) || trim((string) $param['text']) === '')) {
                            $param['text'] = '-';
                        }
                    }
                    unset($param);
                }
            }
            unset($comp);
        }

        $template = [
            'name' => $templateName,
            'language' => ['code' => $languageCode],
        ];
        if ($components) {
            $template['components'] = $components;
        }

        $response = Http::withToken($this->accessToken)
            ->timeout(15)
            ->post($this->url($this->phoneNumberId . '/messages'), [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'template',
                'template' => $template,
            ]);

        if (!$response->successful()) {
            throw WhatsAppException::fromResponse($response);
        }

        $messageId = $response->json('messages.0.id');
        if (!$messageId) {
            throw new WhatsAppException('WhatsApp did not return a message id.');
        }

        return $messageId;
    }

    /** Template management works on the WhatsApp Business Account, not the phone number. */
    public function canManageTemplates(): bool
    {
        return $this->accessToken !== '' && $this->wabaId !== '';
    }

    /**
     * Every message template on the WABA, following Meta's pagination.
     *
     * @return array<int, array{id: string, name: string, language: string, status: string, category?: string, rejected_reason?: string, components?: array}>
     */
    public function listTemplates(): array
    {
        $templates = [];
        $url = $this->url($this->wabaId . '/message_templates');
        $query = ['fields' => 'id,name,language,status,category,rejected_reason,components', 'limit' => 100];

        // Meta's "next" link already carries the query; the guard stops a misbehaving cursor.
        for ($page = 0; $url && $page < 50; $page++) {
            $response = Http::withToken($this->accessToken)->timeout(20)->get($url, $query);
            if (!$response->successful()) {
                throw WhatsAppException::fromResponse($response);
            }

            array_push($templates, ...($response->json('data') ?? []));
            $url = $response->json('paging.next');
            $query = [];
        }

        return $templates;
    }

    /**
     * Submits a new template for Meta's review. Returns Meta's {id, status, category}.
     */
    public function createTemplate(string $name, string $languageCode, string $category, array $components): array
    {
        $response = Http::withToken($this->accessToken)
            ->timeout(20)
            ->post($this->url($this->wabaId . '/message_templates'), [
                'name' => $name,
                'language' => $languageCode,
                'category' => $category,
                'components' => $components,
            ]);

        if (!$response->successful()) {
            throw WhatsAppException::fromResponse($response);
        }

        return $response->json();
    }

    /** Resubmits the components of an existing (approved / rejected / paused) template for review. */
    public function editTemplate(string $templateId, string $category, array $components): void
    {
        $response = Http::withToken($this->accessToken)
            ->timeout(20)
            ->post($this->url($templateId), [
                'category' => $category,
                'components' => $components,
            ]);

        if (!$response->successful()) {
            throw WhatsAppException::fromResponse($response);
        }
    }

    /**
     * Uploads a sample file through Meta's Resumable Upload API and returns its handle — Meta
     * requires one as the example for a template's media (document) header.
     */
    public function uploadSampleMedia(string $appId, string $fileName, string $mimeType, string $contents): string
    {
        $session = Http::withToken($this->accessToken)
            ->timeout(20)
            ->post($this->url($appId . '/uploads') . '?' . http_build_query([
                'file_name' => $fileName,
                'file_length' => strlen($contents),
                'file_type' => $mimeType,
            ]));

        if (!$session->successful()) {
            throw WhatsAppException::fromResponse($session);
        }

        $uploadId = (string) $session->json('id');
        if ($uploadId === '') {
            throw new WhatsAppException('Meta did not return an upload session.');
        }

        $upload = Http::withHeaders(['Authorization' => 'OAuth ' . $this->accessToken, 'file_offset' => '0'])
            ->withBody($contents, $mimeType)
            ->timeout(30)
            ->post($this->url($uploadId));

        if (!$upload->successful()) {
            throw WhatsAppException::fromResponse($upload);
        }

        $handle = (string) $upload->json('h');
        if ($handle === '') {
            throw new WhatsAppException('Meta did not return a handle for the sample file.');
        }

        return $handle;
    }

    /**
     * Normalises a stored mobile number to WhatsApp's international digits-only format.
     * Bare 10-digit numbers are treated as Indian. Returns null when it can't be a valid number.
     */
    public static function normalizePhone(?string $mobile, string $defaultCountryCode = '91'): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $mobile);
        $digits = ltrim($digits, '0');

        if (strlen($digits) === 10) {
            $digits = $defaultCountryCode . $digits;
        }

        return strlen($digits) >= 11 && strlen($digits) <= 15 ? $digits : null;
    }

    private function url(string $path): string
    {
        return self::BASE_URL . '/' . $this->apiVersion . '/' . $path;
    }
}
