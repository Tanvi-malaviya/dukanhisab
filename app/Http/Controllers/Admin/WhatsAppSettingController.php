<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\AuditLog;
use App\Models\WhatsAppPack;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsApp\WhatsAppClient;
use App\Services\WhatsApp\WhatsAppException;
use App\Services\WhatsApp\WhatsAppTemplateSync;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Admin → Settings → WhatsApp: Cloud API credentials for the platform's single DukanHisab
 * number, the fixed message templates (submitted to and synced with Meta), and the
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
            'whatsapp_app_id' => AppSetting::get('whatsapp_app_id', ''),
            'whatsapp_webhook_verify_token' => AppSetting::get('whatsapp_webhook_verify_token', ''),
            'has_access_token' => $token !== '',
            'access_token_hint' => $token !== '' ? '••••' . substr($token, -4) : '',
            'has_app_secret' => (bool) AppSetting::get('whatsapp_app_secret'),
        ];

        $templates = WhatsAppTemplate::orderBy('key')->orderBy('language')->get();
        $packs = WhatsAppPack::orderBy('credits')->get();

        // Which (event, language) pairs still have no sendable template — those messages can't be sent.
        $missing = [];
        foreach (array_keys(WhatsAppTemplate::EVENTS) as $key) {
            foreach (array_keys(WhatsAppTemplate::LANGUAGES) as $lang) {
                if (!$templates->first(fn ($t) => $t->key === $key && $t->language === $lang && $t->isSendable())) {
                    $missing[] = WhatsAppTemplate::EVENTS[$key]['label'] . ' (' . WhatsAppTemplate::LANGUAGES[$lang] . ')';
                }
            }
        }

        return view('admin.settings.whatsapp', [
            'settings' => $settings,
            'templates' => $templates,
            'packs' => $packs,
            'missing' => $missing,
            'unlinked' => Cache::get(WhatsAppTemplateSync::UNLINKED_CACHE_KEY, []),
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
            'whatsapp_app_id' => 'nullable|digits_between:5,30',
            'whatsapp_access_token' => 'nullable|string|max:1000',
            'whatsapp_webhook_verify_token' => 'nullable|string|max:255',
            'whatsapp_app_secret' => 'nullable|string|max:255',
        ]);

        foreach (['whatsapp_enabled', 'whatsapp_api_version', 'whatsapp_phone_number_id', 'whatsapp_waba_id'] as $key) {
            AppSetting::set($key, (string) $request->input($key, ''));
        }

        // The webhook tab's form doesn't carry the App ID — only overwrite it when it was posted.
        if ($request->has('whatsapp_app_id')) {
            AppSetting::set('whatsapp_app_id', (string) $request->input('whatsapp_app_id', ''));
        }

        if ($request->has('whatsapp_webhook_verify_token')) {
            AppSetting::set('whatsapp_webhook_verify_token', (string) $request->input('whatsapp_webhook_verify_token', ''));
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

    /** The templates that really exist in Meta, for the test-send picker. */
    public function metaTemplates()
    {
        try {
            return response()->json(['success' => true, 'templates' => WhatsAppTemplateSync::fromSettings()->remoteTemplates()]);
        } catch (WhatsAppException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('WhatsApp template list error: ' . $e->getMessage());

            return response()->json(['success' => false, 'message' => 'Could not load templates from Meta: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Sends a test of a real Meta template (picked by name + language code), or — as before —
     * Meta's hello_world / one of our rows by id. A Meta template linked to one of our rows is
     * filled with that row's sample values; any other gets generic samples for its variables.
     */
    public function sendTest(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20',
            'template_id' => 'required_without:template_name|nullable|string',
            'template_name' => 'required_without:template_id|nullable|string|max:512',
            'language_code' => 'required_with:template_name|nullable|string|max:15',
            'document_url' => 'nullable|url|max:1000',
        ]);

        if ($request->filled('template_name')) {
            $linked = WhatsAppTemplate::where('meta_template_name', $request->input('template_name'))
                ->where('meta_language_code', $request->input('language_code'))
                ->first();
            if ($linked) {
                $request->merge(['template_id' => (string) $linked->id]);
            } elseif ($request->input('template_name') === 'hello_world') {
                $request->merge(['template_id' => 'hello_world']);
            } else {
                return $this->sendRemoteTest($request);
            }
        }

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

    /** Test send of a Meta template that has no row here, using generic sample values. */
    private function sendRemoteTest(Request $request)
    {
        $to = WhatsAppClient::normalizePhone($request->input('phone'));
        if (!$to) {
            return response()->json(['success' => false, 'message' => 'Please enter a valid mobile number.'], 422);
        }

        $client = WhatsAppClient::fromSettings();
        if (!$client->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'Save the Phone Number ID and Access Token first.'], 422);
        }

        $name = (string) $request->input('template_name');
        $language = (string) $request->input('language_code');

        try {
            $meta = (new WhatsAppTemplateSync($client))->findRemote($name, $language);
            if (!$meta) {
                return response()->json(['success' => false, 'message' => "“{$name}” ({$language}) was not found in Meta — refresh the list."], 422);
            }
            if (strtoupper((string) $meta['status']) !== 'APPROVED') {
                return response()->json(['success' => false, 'message' => "“{$name}” is {$meta['status']} in Meta — only approved templates can be sent."], 422);
            }

            try {
                $components = WhatsAppTemplateSync::testComponents($meta, $request->input('document_url'));
            } catch (WhatsAppException $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            $messageId = $client->sendTemplate($to, $name, $language, $components);
            AuditLog::log('Sent WhatsApp test message', ['to' => $to, 'template' => $name, 'language' => $language]);

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

        return $this->afterTemplateSave($request, $template, 'WhatsApp template added.');
    }

    public function updateTemplate(Request $request, $id)
    {
        $template = WhatsAppTemplate::findOrFail($id);
        $template->fill($this->validateTemplate($request, $template));

        // A new name or language is a different template in Meta — forget the old link.
        if ($template->isDirty(['meta_template_name', 'meta_language_code'])) {
            $template->fill([
                'meta_template_id' => null,
                'meta_status' => null,
                'meta_category' => null,
                'meta_rejected_reason' => null,
                'meta_body' => null,
                'meta_synced_at' => null,
            ]);
        }

        $template->save();
        AuditLog::log('Updated WhatsApp template', ['id' => $template->id, 'key' => $template->key, 'language' => $template->language]);

        return $this->afterTemplateSave($request, $template, 'WhatsApp template updated.');
    }

    /** "Save & Submit to Meta" in the template modal submits right after saving. */
    private function afterTemplateSave(Request $request, WhatsAppTemplate $template, string $message)
    {
        $redirect = redirect()->to(route('admin.settings.whatsapp') . '#templates');

        if (!$request->boolean('submit_to_meta')) {
            return $redirect->with('success', $message);
        }

        try {
            WhatsAppTemplateSync::fromSettings()->submit($template);
            AuditLog::log('Submitted WhatsApp template to Meta', ['id' => $template->id, 'name' => $template->meta_template_name]);

            return $redirect->with('success', $message . ' Submitted to Meta for review.');
        } catch (WhatsAppException $e) {
            return $redirect->with('error', $message . ' But Meta rejected the submission: ' . $e->getMessage());
        }
    }

    /** Sends one template to Meta for review (new, or a resubmission after edits / rejection). */
    public function submitTemplate($id)
    {
        $template = WhatsAppTemplate::findOrFail($id);
        $redirect = redirect()->to(route('admin.settings.whatsapp') . '#templates');

        try {
            WhatsAppTemplateSync::fromSettings()->submit($template);
        } catch (WhatsAppException $e) {
            return $redirect->with('error', 'Meta did not accept the template: ' . $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('WhatsApp template submit error: ' . $e->getMessage());

            return $redirect->with('error', 'Could not submit the template: ' . $e->getMessage());
        }

        AuditLog::log('Submitted WhatsApp template to Meta', ['id' => $template->id, 'name' => $template->meta_template_name]);

        return $redirect->with('success', "“{$template->meta_template_name}” submitted to Meta — status: {$template->meta_status}. You'll see the result here as soon as Meta reviews it.");
    }

    /** Pulls every template's review status from Meta. */
    public function syncTemplates()
    {
        $redirect = redirect()->to(route('admin.settings.whatsapp') . '#templates');

        try {
            $result = WhatsAppTemplateSync::fromSettings()->sync();
        } catch (WhatsAppException $e) {
            return $redirect->with('error', 'Sync failed: ' . $e->getMessage());
        } catch (\Throwable $e) {
            Log::error('WhatsApp template sync error: ' . $e->getMessage());

            return $redirect->with('error', 'Sync failed: ' . $e->getMessage());
        }

        AuditLog::log('Synced WhatsApp templates from Meta', ['updated' => $result['updated'], 'not_in_meta' => $result['not_in_meta'], 'unlinked' => count($result['unlinked'])]);

        $message = "Synced with Meta: {$result['updated']} template(s) updated";
        if ($result['not_in_meta']) {
            $message .= ", {$result['not_in_meta']} not found in Meta";
        }
        if ($result['unlinked']) {
            $message .= ', ' . count($result['unlinked']) . ' in Meta not added here yet';
        }

        return $redirect->with('success', $message . '.');
    }

    /** Current Meta review state of every template — polled by the page so webhook updates show live. */
    public function templateStatuses()
    {
        return response()->json([
            'templates' => WhatsAppTemplate::get(['id', 'meta_status', 'meta_category', 'meta_rejected_reason', 'meta_synced_at'])
                ->map(fn ($t) => [
                    'id' => $t->id,
                    'meta_status' => $t->meta_status,
                    'meta_category' => $t->meta_category,
                    'meta_rejected_reason' => $t->meta_rejected_reason,
                    'meta_synced_at' => $t->meta_synced_at?->diffForHumans(),
                ]),
        ]);
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
