<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AppSetting;
use App\Models\User;
use App\Models\WhatsAppPack;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsApp\WhatsAppClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Admin → Settings → WhatsApp: Cloud API credentials, fixed templates and message packs.
 */
class WhatsAppAdminSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        // actingAs() on the admin guard also makes it the default, so AuditLog::log() records the
        // admin id as user_id too — a matching users row keeps that foreign key valid (as in AdminGrantsTest).
        User::factory()->create();
        $admin = Admin::create(['name' => 'Root', 'email' => 'root@test.com', 'password' => 'secret123', 'role' => 'superadmin', 'status' => 'active']);
        $this->actingAs($admin, 'admin');
    }

    private function saveSettings(array $o = []): void
    {
        $this->post(route('admin.settings.whatsapp.update'), $o + [
            'whatsapp_enabled' => 'yes',
            'whatsapp_api_version' => 'v23.0',
            'whatsapp_phone_number_id' => '106540352242922',
            'whatsapp_waba_id' => '102290129340398',
            'whatsapp_access_token' => 'EAAGsecrettoken1234',
            'whatsapp_webhook_verify_token' => 'verify-me',
        ])->assertRedirect()->assertSessionHasNoErrors();
    }

    private function templatePayload(array $o = []): array
    {
        return $o + [
            'key' => 'due_reminder',
            'language' => 'en',
            'meta_template_name' => 'due_reminder_en',
            'meta_language_code' => 'en',
            'body' => 'Hello {{1}}, your pending balance at {{2}} is {{3}}.',
            'variables' => ['party_name', 'shop_name', 'due_amount'],
            'has_pay_button' => '1',
            'status' => 'active',
        ];
    }

    public function test_settings_page_renders(): void
    {
        WhatsAppTemplate::create($this->templatePayload(['variables' => ['party_name', 'shop_name', 'due_amount'], 'has_pay_button' => true]));
        WhatsAppPack::create(['name' => 'Starter', 'credits' => 100, 'price' => 99, 'status' => 'active']);

        $this->get(route('admin.settings.whatsapp'))
            ->assertOk()
            ->assertSee('Meta WhatsApp Cloud API')
            ->assertSee('Hello Ramesh, your pending balance at Shree Traders is ₹1,200.00.')
            ->assertSee('Starter');
    }

    public function test_access_token_is_stored_encrypted_and_never_echoed(): void
    {
        $this->saveSettings();

        $raw = AppSetting::get('whatsapp_access_token');
        $this->assertNotSame('EAAGsecrettoken1234', $raw);
        $this->assertSame('EAAGsecrettoken1234', WhatsAppClient::storedAccessToken());

        $this->get(route('admin.settings.whatsapp'))
            ->assertOk()
            ->assertDontSee('EAAGsecrettoken1234')
            ->assertSee('••••1234');
    }

    public function test_blank_token_keeps_the_saved_one(): void
    {
        $this->saveSettings();
        $this->saveSettings(['whatsapp_access_token' => '', 'whatsapp_enabled' => 'no']);

        $this->assertSame('EAAGsecrettoken1234', WhatsAppClient::storedAccessToken());
        $this->assertFalse(WhatsAppClient::isEnabled());
    }

    public function test_connection_test_uses_saved_credentials(): void
    {
        $this->saveSettings();
        Http::fake(['graph.facebook.com/*' => Http::response([
            'verified_name' => 'DukanHisab', 'display_phone_number' => '+91 98765 43210', 'quality_rating' => 'GREEN',
        ])]);

        $this->postJson(route('admin.settings.whatsapp.test'))
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonFragment(['message' => 'Connected to DukanHisab (+91 98765 43210). Quality rating: GREEN.']);

        Http::assertSent(fn (HttpRequest $r) => str_contains($r->url(), 'v23.0/106540352242922')
            && $r->hasHeader('Authorization', 'Bearer EAAGsecrettoken1234'));
    }

    public function test_connection_test_reports_meta_errors(): void
    {
        $this->saveSettings();
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['message' => 'Invalid OAuth access token.', 'code' => 190]], 401)]);

        $this->postJson(route('admin.settings.whatsapp.test'))
            ->assertStatus(400)
            ->assertJson(['success' => false, 'message' => 'WhatsApp API error: Invalid OAuth access token.']);
    }

    public function test_send_test_with_hello_world(): void
    {
        $this->saveSettings();
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.TEST']]])]);

        $this->postJson(route('admin.settings.whatsapp.test_send'), ['phone' => '98765 43210', 'template_id' => 'hello_world'])
            ->assertOk()
            ->assertJson(['success' => true]);

        Http::assertSent(fn (HttpRequest $r) => $r['to'] === '919876543210'
            && $r['template']['name'] === 'hello_world'
            && $r['template']['language']['code'] === 'en_US');
    }

    public function test_send_test_with_admin_template_fills_sample_values_and_pay_button(): void
    {
        $this->saveSettings();
        $this->post(route('admin.settings.whatsapp.templates.store'), $this->templatePayload())->assertSessionHasNoErrors();
        $template = WhatsAppTemplate::firstOrFail();
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.TEST']]])]);

        $this->postJson(route('admin.settings.whatsapp.test_send'), ['phone' => '+919876543210', 'template_id' => (string) $template->id])
            ->assertOk();

        Http::assertSent(function (HttpRequest $r) {
            $components = collect($r['template']['components']);
            $body = $components->firstWhere('type', 'body');
            $button = $components->firstWhere('type', 'button');

            return $r['template']['name'] === 'due_reminder_en'
                && array_column($body['parameters'], 'text') === ['Ramesh', 'Shree Traders', '₹1,200.00']
                && $button['sub_type'] === 'url'
                && $button['parameters'][0]['text'] === 'test';
        });
    }

    public function test_template_variables_must_match_body_placeholders(): void
    {
        $this->post(route('admin.settings.whatsapp.templates.store'), $this->templatePayload(['variables' => ['party_name']]))
            ->assertSessionHasErrors('variables');

        $this->post(route('admin.settings.whatsapp.templates.store'), $this->templatePayload([
            'body' => 'Thank you for your business.', 'variables' => [], 'has_pay_button' => '0',
        ]))->assertSessionHasNoErrors();

        $this->assertSame([], WhatsAppTemplate::firstOrFail()->variables);
    }

    public function test_pay_button_only_allowed_on_due_reminder(): void
    {
        $this->post(route('admin.settings.whatsapp.templates.store'), $this->templatePayload(['key' => 'sale_invoice']))
            ->assertSessionHasErrors('has_pay_button');
    }

    public function test_one_template_per_event_and_language(): void
    {
        $this->post(route('admin.settings.whatsapp.templates.store'), $this->templatePayload())->assertSessionHasNoErrors();
        $this->post(route('admin.settings.whatsapp.templates.store'), $this->templatePayload(['meta_template_name' => 'other']))
            ->assertSessionHasErrors('language');

        // Editing the existing one keeps its own (key, language).
        $template = WhatsAppTemplate::firstOrFail();
        $this->post(route('admin.settings.whatsapp.templates.update', $template->id), $this->templatePayload(['status' => 'inactive']))
            ->assertSessionHasNoErrors();
        $this->assertSame('inactive', $template->fresh()->status);

        $this->delete(route('admin.settings.whatsapp.templates.destroy', $template->id))->assertRedirect();
        $this->assertDatabaseCount('whatsapp_templates', 0);
    }

    public function test_pack_crud(): void
    {
        $this->post(route('admin.settings.whatsapp.packs.store'), ['name' => 'Starter', 'credits' => 100, 'price' => 99, 'status' => 'active'])
            ->assertSessionHasNoErrors();
        $pack = WhatsAppPack::firstOrFail();

        $this->post(route('admin.settings.whatsapp.packs.update', $pack->id), ['name' => 'Starter', 'credits' => 120, 'price' => 99, 'status' => 'inactive'])
            ->assertSessionHasNoErrors();
        $this->assertSame(120, $pack->fresh()->credits);

        $this->post(route('admin.settings.whatsapp.packs.store'), ['name' => 'Bad', 'credits' => 0, 'price' => 10, 'status' => 'active'])
            ->assertSessionHasErrors('credits');

        $this->delete(route('admin.settings.whatsapp.packs.destroy', $pack->id))->assertRedirect();
        $this->assertDatabaseCount('whatsapp_packs', 0);
    }

    public function test_normalize_phone(): void
    {
        $this->assertSame('919876543210', WhatsAppClient::normalizePhone('98765 43210'));
        $this->assertSame('919876543210', WhatsAppClient::normalizePhone('09876543210'));
        $this->assertSame('919876543210', WhatsAppClient::normalizePhone('+91-98765-43210'));
        $this->assertNull(WhatsAppClient::normalizePhone('12345'));
        $this->assertNull(WhatsAppClient::normalizePhone(null));
    }
}
