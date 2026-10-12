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
        $message = $error['error_data']['details'] ?? $error['message'] ?? ('HTTP ' . $response->status());
        $code = (int) ($error['code'] ?? $response->status());

        return new self($message, $code, $response->serverError() || in_array($code, self::RETRYABLE_CODES, true));
    }
}
