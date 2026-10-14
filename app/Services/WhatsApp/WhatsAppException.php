<?php

namespace App\Services\WhatsApp;

use Illuminate\Http\Client\Response;

class WhatsAppException extends \RuntimeException
{
    /**
     * Meta error codes that are temporary (throttling / service hiccups) — worth retrying.
     * https://developers.facebook.com/docs/whatsapp/cloud-api/support/error-codes
     */
    private const RETRYABLE_CODES = [1, 2, 4, 80007, 130429, 131016, 131048, 131056];

    public function __construct(string $message, int $code = 0, public readonly bool $retryable = false)
    {
        parent::__construct($message, $code);
    }

    public static function fromResponse(Response $response): self
    {
        $error = $response->json('error') ?? [];
        $detail = $error['error_user_msg']
            ?? $error['error_data']['details']
            ?? null;
        $title = $error['error_user_title'] ?? null;
        $base = $error['message'] ?? ('HTTP ' . $response->status());

        $parts = array_filter([$title, $detail ?: $base]);
        $message = implode(': ', $parts);
        if (empty($message)) {
            $message = $base;
        }

        if (!empty($error['error_subcode'])) {
            $message .= ' (subcode: ' . $error['error_subcode'] . ')';
        }

        $code = (int) ($error['code'] ?? $response->status());

        return new self($message, $code, $response->serverError() || in_array($code, self::RETRYABLE_CODES, true));
    }
}
