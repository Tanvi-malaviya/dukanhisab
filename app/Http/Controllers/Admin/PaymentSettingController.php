<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentSettingController extends Controller
{
    public function index()
    {
        $defaultKey = config('services.razorpay.key', env('RAZORPAY_KEY_ID', ''));
        $defaultSecret = config('services.razorpay.secret', env('RAZORPAY_KEY_SECRET', ''));
        $defaultWebhook = config('services.razorpay.webhook_secret', env('RAZORPAY_WEBHOOK_SECRET', ''));

        $settings = [
            'razorpay_enabled' => AppSetting::get('razorpay_enabled', 'yes'),
            'razorpay_mode' => AppSetting::get('razorpay_mode', str_starts_with($defaultKey, 'rzp_live_') ? 'live' : 'test'),
            'razorpay_key_id' => AppSetting::get('razorpay_key_id', $defaultKey),
            'razorpay_key_secret' => AppSetting::get('razorpay_key_secret', $defaultSecret),
            'razorpay_webhook_secret' => AppSetting::get('razorpay_webhook_secret', $defaultWebhook),
        ];

        $webhookUrl = url('/api/razorpay/webhook');

        return view('admin.settings.payment', compact('settings', 'webhookUrl'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'razorpay_enabled' => 'required|in:yes,no',
            'razorpay_mode' => 'required|in:test,live',
            'razorpay_key_id' => 'nullable|string|max:255',
            'razorpay_key_secret' => 'nullable|string|max:255',
            'razorpay_webhook_secret' => 'nullable|string|max:255',
        ]);

        $keys = [
            'razorpay_enabled',
            'razorpay_mode',
            'razorpay_key_id',
            'razorpay_key_secret',
            'razorpay_webhook_secret',
        ];

        foreach ($keys as $key) {
            AppSetting::set($key, $request->input($key, ''));
        }

        // Keep runtime config up to date immediately
        config([
            'services.razorpay.key' => $request->input('razorpay_key_id', ''),
            'services.razorpay.secret' => $request->input('razorpay_key_secret', ''),
            'services.razorpay.webhook_secret' => $request->input('razorpay_webhook_secret', ''),
            'services.razorpay.enabled' => $request->input('razorpay_enabled') === 'yes',
            'services.razorpay.mode' => $request->input('razorpay_mode', 'test'),
        ]);

        // Synchronize .env file if writable
        $this->updateEnvFile([
            'RAZORPAY_KEY_ID' => $request->input('razorpay_key_id', ''),
            'RAZORPAY_KEY_SECRET' => $request->input('razorpay_key_secret', ''),
            'RAZORPAY_WEBHOOK_SECRET' => $request->input('razorpay_webhook_secret', ''),
        ]);

        AuditLog::log('Updated platform Razorpay payment gateway credentials', [
            'mode' => $request->input('razorpay_mode'),
            'enabled' => $request->input('razorpay_enabled'),
            'key_id_masked' => substr($request->input('razorpay_key_id', ''), 0, 10) . '...',
        ]);

        return back()->with('success', 'Razorpay payment gateway credentials updated and saved successfully.');
    }

    public function testConnection(Request $request)
    {
        $keyId = $request->input('key_id') ?: AppSetting::get('razorpay_key_id', config('services.razorpay.key'));
        $keySecret = $request->input('key_secret') ?: AppSetting::get('razorpay_key_secret', config('services.razorpay.secret'));

        if (empty($keyId) || empty($keySecret)) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter both Key ID and Key Secret to test the connection.',
            ], 422);
        }

        try {
            // Test ping against Razorpay Plans endpoint
            $response = Http::withBasicAuth($keyId, $keySecret)
                ->timeout(10)
                ->get('https://api.razorpay.com/v1/plans', ['count' => 1]);

            if ($response->successful()) {
                $isLive = str_starts_with($keyId, 'rzp_live_');
                return response()->json([
                    'success' => true,
                    'message' => 'Connection verified successfully! Credentials are active and valid.',
                    'mode' => $isLive ? 'Live Mode' : 'Test Mode',
                    'key_prefix' => substr($keyId, 0, 8),
                ]);
            }

            $body = $response->json();
            $errorDesc = $body['error']['description'] ?? $response->body() ?? 'Authentication failed.';

            return response()->json([
                'success' => false,
                'message' => 'Razorpay API Error: ' . $errorDesc,
                'status' => $response->status(),
            ], 400);
        } catch (\Throwable $e) {
            Log::error('Razorpay test connection error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Could not connect to Razorpay: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function updateEnvFile(array $values): void
    {
        try {
            $envPath = base_path('.env');
            if (!file_exists($envPath) || !is_writable($envPath)) {
                return;
            }

            $content = file_get_contents($envPath);

            foreach ($values as $key => $val) {
                $val = (string) $val;
                $formattedVal = preg_match('/\s/', $val) ? '"' . addcslashes($val, '"') . '"' : $val;

                if (preg_match("/^{$key}=.*/m", $content)) {
                    $content = preg_replace("/^{$key}=.*/m", "{$key}={$formattedVal}", $content);
                } else {
                    $content .= PHP_EOL . "{$key}={$formattedVal}";
                }
            }

            file_put_contents($envPath, $content);
        } catch (\Throwable $e) {
            Log::warning('Unable to write to .env file: ' . $e->getMessage());
        }
    }
}
