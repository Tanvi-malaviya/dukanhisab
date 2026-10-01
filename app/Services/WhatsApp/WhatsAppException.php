<?php

namespace App\Services\WhatsApp;

use Illuminate\Http\Client\Response;

class WhatsAppException extends \RuntimeException
{
    public static function fromResponse(Response $response): self
    {
        $error = $response->json('error') ?? [];
        $message = $error['error_data']['details'] ?? $error['message'] ?? ('HTTP ' . $response->status());

        return new self($message, (int) ($error['code'] ?? $response->status()));
    }
}
