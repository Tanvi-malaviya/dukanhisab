<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\WhatsAppMessageLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

/**
 * Meta WhatsApp Cloud API webhook: the one-time subscription handshake (GET) and message
 * status callbacks (POST) that move WhatsAppMessageLog rows through sent → delivered → read / failed.
 */
class WhatsAppWebhookController extends Controller
{
    public function verify(Request $request)
    {
        // PHP turns the "hub.mode" query keys into "hub_mode".
        $expected = (string) AppSetting::get('whatsapp_webhook_verify_token', '');

        if ($expected !== ''
            && $request->query('hub_mode') === 'subscribe'
            && hash_equals($expected, (string) $request->query('hub_verify_token'))) {
            return response((string) $request->query('hub_challenge'), 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    public function handle(Request $request)
    {
        if (!$this->hasValidSignature($request)) {
            return response()->json(['message' => 'Invalid signature.'], 403);
        }

        foreach ($request->input('entry', []) as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                foreach ($change['value']['statuses'] ?? [] as $status) {
                    $this->applyStatus($status);
                }
            }
        }

        return response()->json(['received' => true]);
    }

    private function applyStatus(array $status): void
    {
        $log = WhatsAppMessageLog::where('provider_message_id', $status['id'] ?? null)->first();
        if (!$log) {
            return;
        }

        $at = isset($status['timestamp']) ? Carbon::createFromTimestamp((int) $status['timestamp']) : now();

        if (($status['status'] ?? null) === 'failed') {
            $error = $status['errors'][0] ?? [];
            $log->markFailed($error['error_data']['details'] ?? $error['message'] ?? $error['title'] ?? 'Delivery failed.');
            return;
        }

        $log->advanceTo((string) ($status['status'] ?? ''), $at);
    }

    /** Meta signs every callback with the App Secret: X-Hub-Signature-256 = sha256=HMAC(body). */
    private function hasValidSignature(Request $request): bool
    {
        $encrypted = AppSetting::get('whatsapp_app_secret');
        if (!$encrypted) {
            Log::warning('WhatsApp webhook rejected: App Secret is not configured.');
            return false;
        }

        try {
            $secret = Crypt::decryptString($encrypted);
        } catch (\Throwable) {
            return false;
        }

        $signature = (string) $request->header('X-Hub-Signature-256');
        $expected = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }
}
