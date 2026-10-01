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
    ) {
    }

    public static function fromSettings(): self
    {
        return new self(
            self::storedAccessToken(),
            (string) AppSetting::get('whatsapp_phone_number_id', ''),
            AppSetting::get('whatsapp_api_version') ?: self::DEFAULT_API_VERSION,
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
