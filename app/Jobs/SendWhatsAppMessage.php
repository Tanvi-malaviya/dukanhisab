<?php

namespace App\Jobs;

use App\Models\WhatsAppMessageLog;
use App\Services\WhatsApp\WhatsAppClient;
use App\Services\WhatsApp\WhatsAppException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Sends one queued WhatsAppMessageLog through the Cloud API. Temporary Meta / network errors
 * are retried with backoff; anything else marks the message failed straight away.
 */
class SendWhatsAppMessage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $logId)
    {
    }

    public function backoff(): array
    {
        return [60, 300];
    }

    public function handle(): void
    {
        $log = WhatsAppMessageLog::with('template')->find($this->logId);
        if (!$log || $log->status !== 'queued') {
            return;
        }

        if (!WhatsAppClient::isEnabled()) {
            $log->markFailed('WhatsApp messaging is turned off by the administrator.');
            return;
        }
        if (!$log->template || $log->template->status !== 'active') {
            $log->markFailed('The WhatsApp template for this message is no longer available.');
            return;
        }

        $client = WhatsAppClient::fromSettings();
        if (!$client->isConfigured()) {
            $log->markFailed('WhatsApp is not configured.');
            return;
        }

        $payload = $log->payload;

        try {
            $messageId = $client->sendTemplate(
                $log->phone,
                $log->template->meta_template_name,
                $log->template->meta_language_code,
                $log->template->buildComponents(
                    $payload['values'] ?? [],
                    $payload['document_url'] ?? null,
                    $payload['document_name'] ?? null,
                    $payload['button_suffix'] ?? null,
                )
            );
            $log->markSent($messageId);
        } catch (WhatsAppException $e) {
            if ($e->retryable && $this->attempts() < $this->tries) {
                $this->release($this->backoff()[$this->attempts() - 1] ?? 300);
                return;
            }
            $log->markFailed($e->getMessage());
        } catch (ConnectionException $e) {
            if ($this->attempts() < $this->tries) {
                $this->release($this->backoff()[$this->attempts() - 1] ?? 300);
                return;
            }
            $log->markFailed('Could not reach WhatsApp: ' . $e->getMessage());
        }
    }

    public function failed(\Throwable $e): void
    {
        WhatsAppMessageLog::find($this->logId)?->markFailed($e->getMessage());
    }
}
