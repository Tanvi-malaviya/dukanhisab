<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\AuditLog;
use App\Models\WhatsAppPack;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsApp\WhatsAppClient;
use App\Services\WhatsApp\WhatsAppException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Admin → Settings → WhatsApp: Cloud API credentials for the platform's single DukanHisab
 * number, the fixed message templates (mirroring the ones approved in Meta), and the
 * message-credit packs shop owners can buy.
 */
class WhatsAppSettingController extends Controller
{
    public function index()
    {
        $token = WhatsAppClient::storedAccessToken();

        $settings = [
            'whatsapp_enabled' => AppSetting::get('whatsapp_enabled', 'no'),
            'whatsapp_api_version' => AppSetting::get('whatsapp_api_version') ?: WhatsAppClient::DEFAULT_API_VERSION,
            'whatsapp_phone_number_id' => AppSetting::get('whatsapp_phone_number_id', ''),
            'whatsapp_waba_id' => AppSetting::get('whatsapp_waba_id', ''),
            'whatsapp_webhook_verify_token' => AppSetting::get('whatsapp_webhook_verify_token', ''),
            'has_access_token' => $token !== '',
            'access_token_hint' => $token !== '' ? '••••' . substr($token, -4) : '',
            'has_app_secret' => (bool) AppSetting::get('whatsapp_app_secret'),
        ];

        $templates = WhatsAppTemplate::orderBy('key')->orderBy('language')->get();
        $packs = WhatsAppPack::orderBy('credits')->get();

        // Which (event, language) pairs still have no active template — those messages can't be sent.
        $missing = [];
        foreach (array_keys(WhatsAppTemplate::EVENTS) as $key) {
            foreach (array_keys(WhatsAppTemplate::LANGUAGES) as $lang) {
                if (!$templates->first(fn ($t) => $t->key === $key && $t->language === $lang && $t->status === 'active')) {
                    $missing[] = WhatsAppTemplate::EVENTS[$key]['label'] . ' (' . WhatsAppTemplate::LANGUAGES[$lang] . ')';
                }
            }
        }

        return view('admin.settings.whatsapp', [
            'settings' => $settings,
            'templates' => $templates,
            'packs' => $packs,
            'missing' => $missing,
            'events' => WhatsAppTemplate::EVENTS,
            'languages' => WhatsAppTemplate::LANGUAGES,
            'variables' => WhatsAppTemplate::VARIABLES,
            'sampleValues' => WhatsAppTemplate::sampleValues(),
            'webhookUrl' => url('/api/v1/whatsapp/webhook'),
            'payUrlBase' => url('/pay') . '/',
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'whatsapp_enabled' => 'required|in:yes,no',
            'whatsapp_api_version' => ['required', 'regex:/^v\d+\.\d+$/'],
            'whatsapp_phone_number_id' => 'nullable|digits_between:5,30',
            'whatsapp_waba_id' => 'nullable|digits_between:5,30',
            'whatsapp_access_token' => 'nullable|string|max:1000',
            'whatsapp_webhook_verify_token' => 'nullable|string|max:255',
            'whatsapp_app_secret' => 'nullable|string|max:255',
        ]);

        foreach (['whatsapp_enabled', 'whatsapp_api_version', 'whatsapp_phone_number_id', 'whatsapp_waba_id', 'whatsapp_webhook_verify_token'] as $key) {
            AppSetting::set($key, (string) $request->input($key, ''));
        }

        // Blank secret fields mean "keep the saved one" — the page never echoes them back.
        foreach (['whatsapp_access_token', 'whatsapp_app_secret'] as $key) {
            if ($request->filled($key)) {
                AppSetting::set($key, Crypt::encryptString(trim($request->input($key))));
            }
        }

        AuditLog::log('Updated WhatsApp Cloud API settings', [
            'enabled' => $request->input('whatsapp_enabled'),
            'phone_number_id' => $request->input('whatsapp_phone_number_id'),
            'token_changed' => $request->filled('whatsapp_access_token'),
            'app_secret_changed' => $request->filled('whatsapp_app_secret'),
        ]);

        return back()->with('success', 'WhatsApp settings saved successfully.');
    }

    public function testConnection(Request $request)
    {
        $client = new WhatsAppClient(
            trim((string) $request->input('access_token')) ?: WhatsAppClient::storedAccessToken(),
            trim((string) $request->input('phone_number_id')) ?: (string) AppSetting::get('whatsapp_phone_number_id', ''),
            trim((string) $request->input('api_version')) ?: (AppSetting::get('whatsapp_api_version') ?: WhatsAppClient::DEFAULT_API_VERSION),
        );

        if (!$client->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Please enter the Phone Number ID and Access Token to test the connection.',
            ], 422);
        }

        try {
            $info = $client->phoneNumberInfo();

            return response()->json([
                'success' => true,
                'message' => sprintf(
                    'Connected to %s (%s). Quality rating: %s.',
                    $info['verified_name'] ?? 'WhatsApp number',
                    $info['display_phone_number'] ?? '-',
                    $info['quality_rating'] ?? 'unknown'
                ),
            ]);
        } catch (WhatsAppException $e) {
            return response()->json(['success' => false, 'message' => 'WhatsApp API error: ' . $e->getMessage()], 400);
        } catch (\Throwable $e) {
            Log::error('WhatsApp test connection error: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Could not connect to WhatsApp: ' . $e->getMessage()], 500);
        }
    }

    /** Sends Meta's built-in hello_world, or one of our templates filled with sample values. */
    public function sendTest(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'template_id' => 'required|string',
            'document_url' => 'nullable|url|max:1000',
        ]);

        $to = WhatsAppClient::normalizePhone($request->input('phone'));
        if (!$to) {
            return response()->json(['success' => false, 'message' => 'Please enter a valid mobile number.'], 422);
        }

        $client = WhatsAppClient::fromSettings();
        if (!$client->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'Save the Phone Number ID and Access Token first.'], 422);
        }

        try {
            if ($request->input('template_id') === 'hello_world') {
                $messageId = $client->sendTemplate($to, 'hello_world', 'en_US');
            } else {
                $template = WhatsAppTemplate::findOrFail($request->input('template_id'));
                if ($template->has_document && !$request->filled('document_url')) {
                    return response()->json(['success' => false, 'message' => 'This template has a PDF header — enter a public sample PDF URL.'], 422);
                }

                $messageId = $client->sendTemplate(
                    $to,
                    $template->meta_template_name,
                    $template->meta_language_code,
                    $template->buildComponents(
                        WhatsAppTemplate::sampleValues(),
                        $request->input('document_url'),
                        'INV-1024.pdf',
                        $template->has_pay_button ? 'test' : null
                    )
                );
            }

            AuditLog::log('Sent WhatsApp test message', ['to' => $to, 'template' => $request->input('template_id')]);

            return response()->json(['success' => true, 'message' => "Test message sent to +{$to} (id: {$messageId})."]);
        } catch (WhatsAppException $e) {
            return response()->json(['success' => false, 'message' => 'WhatsApp API error: ' . $e->getMessage()], 400);
        } catch (\Throwable $e) {
            Log::error('WhatsApp test send error: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Could not send the message: ' . $e->getMessage()], 500);
        }
    }

    // ------------------------------------------------------------ templates

    public function storeTemplate(Request $request)
    {
        $template = WhatsAppTemplate::create($this->validateTemplate($request));
        AuditLog::log('Created WhatsApp template', ['id' => $template->id, 'key' => $template->key, 'language' => $template->language]);

        return back()->with('success', 'WhatsApp template added.');
    }

    public function updateTemplate(Request $request, $id)
    {
        $template = WhatsAppTemplate::findOrFail($id);
        $template->update($this->validateTemplate($request, $template));
        AuditLog::log('Updated WhatsApp template', ['id' => $template->id, 'key' => $template->key, 'language' => $template->language]);

        return back()->with('success', 'WhatsApp template updated.');
    }

    public function destroyTemplate($id)
    {
        $template = WhatsAppTemplate::findOrFail($id);
        $template->delete();
        AuditLog::log('Deleted WhatsApp template', ['id' => $template->id, 'key' => $template->key, 'language' => $template->language]);

        return back()->with('success', 'WhatsApp template deleted.');
    }

    private function validateTemplate(Request $request, ?WhatsAppTemplate $template = null): array
    {
        $data = $request->validate([
            'key' => ['required', Rule::in(array_keys(WhatsAppTemplate::EVENTS))],
            'language' => [
                'required',
                Rule::in(array_keys(WhatsAppTemplate::LANGUAGES)),
                Rule::unique('whatsapp_templates')->where('key', $request->input('key'))->ignore($template?->id),
            ],
            'meta_template_name' => ['required', 'max:512', 'regex:/^[a-z0-9_]+$/'],
            'meta_language_code' => ['required', 'regex:/^[a-z]{2,3}(_[A-Z]{2})?$/'],
            'body' => 'required|string|max:1024',
            'variables' => 'nullable|array',
            'variables.*' => ['required', Rule::in(array_keys(WhatsAppTemplate::VARIABLES))],
            'has_document' => 'nullable|boolean',
            'has_pay_button' => 'nullable|boolean',
            'status' => 'required|in:active,inactive',
        ], [
            'language.unique' => 'A template for this event and language already exists — edit that one instead.',
            'meta_template_name.regex' => 'Meta template names use only lowercase letters, numbers and underscores.',
        ]);

        $data['variables'] = array_values($data['variables'] ?? []);
        $data['has_document'] = $request->boolean('has_document');
        $data['has_pay_button'] = $request->boolean('has_pay_button');

        // The body's {{1}}..{{n}} placeholders must line up one-to-one with the chosen variables.
        preg_match_all('/\{\{(\d+)\}\}/', $data['body'], $m);
        $placeholders = array_unique(array_map('intval', $m[1]));
        sort($placeholders);
        $count = count($data['variables']);
        if ($placeholders !== ($count ? range(1, $count) : [])) {
            throw ValidationException::withMessages([
                'variables' => 'The body uses ' . count($placeholders) . ' placeholder(s) ({{1}}, {{2}} ...) — pick exactly one variable for each, numbered from {{1}} without gaps.',
            ]);
        }

        if ($data['has_pay_button'] && $data['key'] !== 'due_reminder') {
            throw ValidationException::withMessages([
                'has_pay_button' => 'The Pay Now button is only available on the customer due reminder.',
            ]);
        }

        return $data;
    }

    // ------------------------------------------------------------ packs

    public function storePack(Request $request)
    {
        $pack = WhatsAppPack::create($this->validatePack($request));
        AuditLog::log('Created WhatsApp message pack', ['id' => $pack->id, 'credits' => $pack->credits, 'price' => $pack->price]);

        return back()->with('success', 'Message pack added.');
    }

    public function updatePack(Request $request, $id)
    {
        $pack = WhatsAppPack::findOrFail($id);
        $pack->update($this->validatePack($request));
        AuditLog::log('Updated WhatsApp message pack', ['id' => $pack->id, 'credits' => $pack->credits, 'price' => $pack->price]);

        return back()->with('success', 'Message pack updated.');
    }

    public function destroyPack($id)
    {
        $pack = WhatsAppPack::findOrFail($id);
        $pack->delete();
        AuditLog::log('Deleted WhatsApp message pack', ['id' => $pack->id]);

        return back()->with('success', 'Message pack deleted.');
    }

    private function validatePack(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:100',
            'credits' => 'required|integer|min:1|max:1000000',
            'price' => 'required|numeric|min:0|max:10000000',
            'status' => 'required|in:active,inactive',
        ]);
    }
}
