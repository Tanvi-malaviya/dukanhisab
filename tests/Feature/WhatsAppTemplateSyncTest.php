<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AppSetting;
use App\Models\User;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsApp\WhatsAppTemplateSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Admin → WhatsApp templates: submitting to Meta for review, "Sync from Meta", and the
 * template status / category webhooks that report Meta's decision back.
 */
class WhatsAppTemplateSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        User::factory()->create();
        $admin = Admin::create(['name' => 'Root', 'email' => 'root@test.com', 'password' => 'secret123', 'role' => 'superadmin', 'status' => 'active']);
        $this->actingAs($admin, 'admin');

        AppSetting::set('whatsapp_waba_id', '102290129340398');
        AppSetting::set('whatsapp_access_token', Crypt::encryptString('EAAGtoken'));
        AppSetting::set('whatsapp_app_secret', Crypt::encryptString('app-secret'));
    }

    private function template(array $o = []): WhatsAppTemplate
    {
        return WhatsAppTemplate::create($o + [
            'key' => 'due_reminder', 'language' => 'en',
            'meta_template_name' => 'due_reminder_en', 'meta_language_code' => 'en',
            'body' => 'Hello {{1}}, your pending balance at {{2}} is {{3}}.',
            'variables' => ['party_name', 'shop_name', 'due_amount'],
            'has_pay_button' => true, 'status' => 'active',
        ]);
    }

    private function postWebhook(array $payload)
    {
        $body = json_encode($payload);
        $headers = ['Content-Type' => 'application/json', 'X-Hub-Signature-256' => 'sha256=' . hash_hmac('sha256', $body, 'app-secret')];

        return $this->call('POST', '/api/v1/whatsapp/webhook', [], [], [], $this->transformHeadersToServerVars($headers), $body);
    }

    private function templateChange(string $field, array $value): array
    {
        return ['object' => 'whatsapp_business_account', 'entry' => [['changes' => [['field' => $field, 'value' => $value]]]]];
    }

    // ------------------------------------------------------------ submit

    public function test_submit_creates_the_template_in_meta_with_examples_and_pay_button(): void
    {
        $t = $this->template();
        Http::fake(['graph.facebook.com/*' => Http::response(['id' => '594425479261596', 'status' => 'PENDING', 'category' => 'UTILITY'])]);

        $this->post(route('admin.settings.whatsapp.templates.submit', $t->id))->assertRedirect()->assertSessionHas('success');

        Http::assertSent(function (HttpRequest $r) {
            $components = collect($r['components']);
            $body = $components->firstWhere('type', 'BODY');
            $button = $components->firstWhere('type', 'BUTTONS')['buttons'][0] ?? null;

            return $r->url() === 'https://graph.facebook.com/v23.0/102290129340398/message_templates'
                && $r['name'] === 'due_reminder_en' && $r['language'] === 'en' && $r['category'] === 'UTILITY'
                && $body['example']['body_text'][0] === ['Ramesh', 'Shree Traders', '₹1,200.00']
                && $button['type'] === 'URL' && str_ends_with($button['url'], '/pay/{{1}}');
        });

        $t->refresh();
        $this->assertSame('594425479261596', $t->meta_template_id);
        $this->assertSame('PENDING', $t->meta_status);
        $this->assertSame($t->body, $t->meta_body);
        $this->assertFalse($t->isSendable());
    }

    public function test_resubmitting_a_rejected_template_edits_it_in_meta(): void
    {
        $t = $this->template(['meta_template_id' => '555', 'meta_status' => 'REJECTED', 'meta_category' => 'UTILITY', 'meta_rejected_reason' => 'INVALID_FORMAT']);
        Http::fake(['graph.facebook.com/*' => Http::response(['success' => true])]);

        $this->post(route('admin.settings.whatsapp.templates.submit', $t->id))->assertSessionHas('success');

        Http::assertSent(fn (HttpRequest $r) => $r->url() === 'https://graph.facebook.com/v23.0/555' && isset($r['components']));
        $this->assertSame('PENDING', $t->fresh()->meta_status);
        $this->assertNull($t->fresh()->meta_rejected_reason);
    }

    public function test_pending_template_cannot_be_resubmitted(): void
    {
        $t = $this->template(['meta_template_id' => '555', 'meta_status' => 'PENDING']);
        Http::fake();

        $this->post(route('admin.settings.whatsapp.templates.submit', $t->id))->assertSessionHas('error');
        Http::assertNothingSent();
    }

    public function test_submit_reports_meta_errors_and_keeps_the_template(): void
    {
        $t = $this->template();
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['message' => 'Invalid parameter', 'code' => 100, 'error_data' => ['details' => 'Variables can\'t be at the start or end of the template.']]], 400)]);

        $this->post(route('admin.settings.whatsapp.templates.submit', $t->id))
            ->assertSessionHas('error', fn ($m) => str_contains($m, "Variables can't be at the start"));
        $this->assertNull($t->fresh()->meta_status);
    }

    public function test_pdf_header_template_needs_the_app_id(): void
    {
        $t = $this->template(['key' => 'sale_invoice', 'meta_template_name' => 'sale_invoice_en', 'has_document' => true, 'has_pay_button' => false]);
        Http::fake();

        $this->post(route('admin.settings.whatsapp.templates.submit', $t->id))->assertSessionHas('error', fn ($m) => str_contains($m, 'Meta App ID'));
        Http::assertNothingSent();
    }

    public function test_pdf_header_template_uploads_a_sample_pdf_as_the_header_example(): void
    {
        $t = $this->template(['key' => 'sale_invoice', 'meta_template_name' => 'sale_invoice_en', 'has_document' => true, 'has_pay_button' => false]);
        AppSetting::set('whatsapp_app_id', '1234567890');
        Http::fake([
            'graph.facebook.com/v23.0/1234567890/uploads*' => Http::response(['id' => 'upload:abc']),
            'graph.facebook.com/v23.0/upload:abc' => Http::response(['h' => '4::handle']),
            'graph.facebook.com/*' => Http::response(['id' => '777', 'status' => 'PENDING', 'category' => 'UTILITY']),
        ]);

        $this->post(route('admin.settings.whatsapp.templates.submit', $t->id))->assertSessionHas('success');

        Http::assertSent(fn (HttpRequest $r) => str_ends_with($r->url(), '/message_templates')
            && collect($r['components'])->firstWhere('type', 'HEADER')['example']['header_handle'] === ['4::handle']);
        $this->assertSame('777', $t->fresh()->meta_template_id);
    }

    public function test_save_and_submit_from_the_modal(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['id' => '901', 'status' => 'PENDING', 'category' => 'UTILITY'])]);

        $this->post(route('admin.settings.whatsapp.templates.store'), [
            'key' => 'payment_received', 'language' => 'en', 'meta_template_name' => 'payment_received_en', 'meta_language_code' => 'en',
            'body' => 'Thanks {{1}}, we received {{2}}.', 'variables' => ['party_name', 'amount'], 'status' => 'active',
            'submit_to_meta' => '1',
        ])->assertSessionHas('success', fn ($m) => str_contains($m, 'Submitted to Meta'));

        $this->assertSame('901', WhatsAppTemplate::where('meta_template_name', 'payment_received_en')->value('meta_template_id'));
    }

    public function test_renaming_a_template_drops_the_old_meta_link(): void
    {
        $t = $this->template(['meta_template_id' => '555', 'meta_status' => 'APPROVED']);

        $this->post(route('admin.settings.whatsapp.templates.update', $t->id), [
            'key' => 'due_reminder', 'language' => 'en', 'meta_template_name' => 'due_reminder_v2', 'meta_language_code' => 'en',
            'body' => $t->body, 'variables' => $t->variables, 'has_pay_button' => '1', 'status' => 'active',
        ])->assertSessionHasNoErrors();

        $this->assertNull($t->fresh()->meta_template_id);
        $this->assertNull($t->fresh()->meta_status);
    }

    // ------------------------------------------------------------ sync

    public function test_sync_updates_statuses_marks_missing_and_lists_unlinked(): void
    {
        $approved = $this->template();
        $gone = $this->template(['language' => 'gu', 'meta_template_name' => 'due_reminder_gu', 'meta_language_code' => 'gu']);

        Http::fake(['graph.facebook.com/*' => Http::sequence()
            ->push(['data' => [
                ['id' => '111', 'name' => 'due_reminder_en', 'language' => 'en', 'status' => 'APPROVED', 'category' => 'UTILITY',
                    'components' => [['type' => 'BODY', 'text' => 'Hello {{1}}, your pending balance at {{2}} is {{3}}.']]],
            ], 'paging' => ['next' => 'https://graph.facebook.com/v23.0/102290129340398/message_templates?after=xyz']])
            ->push(['data' => [
                ['id' => '222', 'name' => 'sale_invoice_hi', 'language' => 'hi', 'status' => 'REJECTED', 'category' => 'UTILITY', 'rejected_reason' => 'INVALID_FORMAT',
                    'components' => [['type' => 'HEADER', 'format' => 'DOCUMENT'], ['type' => 'BODY', 'text' => 'नमस्ते {{1}}']]],
            ]]),
        ]);

        $this->post(route('admin.settings.whatsapp.templates.sync'))
            ->assertSessionHas('success', fn ($m) => str_contains($m, '1 template(s) updated') && str_contains($m, '1 not found in Meta'));

        Http::assertSentCount(2);
        $this->assertSame('APPROVED', $approved->fresh()->meta_status);
        $this->assertSame('111', $approved->fresh()->meta_template_id);
        $this->assertTrue($approved->fresh()->isSendable());
        $this->assertSame('NOT_IN_META', $gone->fresh()->meta_status);
        $this->assertFalse($gone->fresh()->isSendable());

        $unlinked = Cache::get(WhatsAppTemplateSync::UNLINKED_CACHE_KEY);
        $this->assertCount(1, $unlinked);
        $this->assertSame('sale_invoice_hi', $unlinked[0]['name']);
        $this->assertTrue($unlinked[0]['has_document']);

        $this->get(route('admin.settings.whatsapp'))->assertOk()->assertSee('sale_invoice_hi')->assertSee('Not in Meta');
    }

    public function test_sync_requires_the_waba_id(): void
    {
        AppSetting::set('whatsapp_waba_id', '');
        Http::fake();

        $this->post(route('admin.settings.whatsapp.templates.sync'))->assertSessionHas('error', fn ($m) => str_contains($m, 'Business Account ID'));
        Http::assertNothingSent();
    }

    // ------------------------------------------------------------ webhook

    public function test_webhook_reports_approval_and_rejection(): void
    {
        $t = $this->template(['meta_template_id' => '594425479261596', 'meta_status' => 'PENDING']);

        $this->postWebhook($this->templateChange('message_template_status_update', [
            'event' => 'REJECTED', 'message_template_id' => 594425479261596,
            'message_template_name' => 'due_reminder_en', 'message_template_language' => 'en', 'reason' => 'INCORRECT_CATEGORY',
        ]))->assertOk();
        $this->assertSame('REJECTED', $t->fresh()->meta_status);
        $this->assertSame('INCORRECT_CATEGORY', $t->fresh()->meta_rejected_reason);

        $this->postWebhook($this->templateChange('message_template_status_update', [
            'event' => 'APPROVED', 'message_template_id' => 594425479261596,
            'message_template_name' => 'due_reminder_en', 'message_template_language' => 'en', 'reason' => 'NONE',
        ]))->assertOk();
        $this->assertSame('APPROVED', $t->fresh()->meta_status);
        $this->assertNull($t->fresh()->meta_rejected_reason);

        $this->getJson(route('admin.settings.whatsapp.templates.statuses'))
            ->assertOk()->assertJsonPath('templates.0.meta_status', 'APPROVED');
    }

    public function test_webhook_matches_by_name_and_updates_category(): void
    {
        $t = $this->template();

        $this->postWebhook($this->templateChange('message_template_status_update', [
            'event' => 'APPROVED', 'message_template_id' => 999, 'message_template_name' => 'due_reminder_en', 'message_template_language' => 'en',
        ]))->assertOk();
        $this->assertSame('999', $t->fresh()->meta_template_id);

        $this->postWebhook($this->templateChange('template_category_update', [
            'message_template_id' => 999, 'previous_category' => 'UTILITY', 'new_category' => 'MARKETING',
        ]))->assertOk();
        $this->assertSame('MARKETING', $t->fresh()->meta_category);
    }

    public function test_paused_template_is_not_used_for_sending(): void
    {
        $this->template(['meta_status' => 'PAUSED']);
        $this->assertSame(0, WhatsAppTemplate::sendable()->count());

        $this->template(['language' => 'gu', 'meta_template_name' => 'due_reminder_gu', 'meta_language_code' => 'gu']);
        $this->assertSame(1, WhatsAppTemplate::sendable()->count());
    }

    // ------------------------------------------------------------ test send with real Meta templates

    private function metaList(): array
    {
        return ['data' => [
            ['id' => '1', 'name' => 'due_reminder_en', 'language' => 'en', 'status' => 'APPROVED', 'category' => 'UTILITY',
                'components' => [['type' => 'BODY', 'text' => 'Hello {{1}}, your pending balance at {{2}} is {{3}}.'],
                    ['type' => 'BUTTONS', 'buttons' => [['type' => 'URL', 'text' => 'Pay Now', 'url' => 'https://x.test/pay/{{1}}']]]]],
            ['id' => '2', 'name' => 'order_update', 'language' => 'en_US', 'status' => 'APPROVED', 'category' => 'UTILITY',
                'components' => [['type' => 'HEADER', 'format' => 'TEXT', 'text' => 'Order {{1}}'],
                    ['type' => 'BODY', 'text' => 'Hi {{party_name}}, your order from {{shop_name}} is ready.'],
                    ['type' => 'BUTTONS', 'buttons' => [['type' => 'QUICK_REPLY', 'text' => 'OK'], ['type' => 'URL', 'text' => 'Track', 'url' => 'https://x.test/t/{{1}}']]]]],
            ['id' => '3', 'name' => 'invoice_pdf', 'language' => 'hi', 'status' => 'APPROVED', 'category' => 'UTILITY',
                'components' => [['type' => 'HEADER', 'format' => 'DOCUMENT'], ['type' => 'BODY', 'text' => 'Invoice {{1}}']]],
            ['id' => '4', 'name' => 'promo', 'language' => 'en', 'status' => 'REJECTED', 'category' => 'MARKETING',
                'components' => [['type' => 'BODY', 'text' => 'Sale!']]],
        ]];
    }

    public function test_meta_templates_lists_real_templates_with_links(): void
    {
        $t = $this->template();
        Http::fake(['graph.facebook.com/*' => Http::response($this->metaList())]);

        $res = $this->getJson(route('admin.settings.whatsapp.meta_templates'))->assertOk()->json('templates');

        $this->assertSame('due_reminder_en', $res[0]['name']);           // approved + linked first
        $this->assertSame($t->id, $res[0]['linked_id']);
        $this->assertSame('Due reminder', $res[0]['linked_event']);
        $this->assertSame('promo', end($res)['name']);                     // not approved last
        $this->assertSame('DOCUMENT', collect($res)->firstWhere('name', 'invoice_pdf')['header_format']);
    }

    public function test_meta_templates_needs_the_waba_id(): void
    {
        AppSetting::set('whatsapp_waba_id', '');
        $this->getJson(route('admin.settings.whatsapp.meta_templates'))->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_test_send_of_a_linked_template_uses_its_variable_samples(): void
    {
        AppSetting::set('whatsapp_phone_number_id', '106540352242922');
        $this->template();
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.T']]])]);

        $this->postJson(route('admin.settings.whatsapp.test_send'), ['phone' => '9876543210', 'template_name' => 'due_reminder_en', 'language_code' => 'en'])
            ->assertOk()->assertJsonPath('success', true);

        Http::assertSent(fn (HttpRequest $r) => str_ends_with($r->url(), '/messages')
            && array_column(collect($r['template']['components'])->firstWhere('type', 'body')['parameters'], 'text') === ['Ramesh', 'Shree Traders', '₹1,200.00']);
    }

    public function test_test_send_of_an_unlinked_template_fills_named_params_header_and_url_button(): void
    {
        AppSetting::set('whatsapp_phone_number_id', '106540352242922');
        Http::fake([
            'graph.facebook.com/v23.0/102290129340398/message_templates*' => Http::response($this->metaList()),
            'graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.T']]]),
        ]);

        $this->postJson(route('admin.settings.whatsapp.test_send'), ['phone' => '9876543210', 'template_name' => 'order_update', 'language_code' => 'en_US'])
            ->assertOk()->assertJsonPath('success', true);

        Http::assertSent(function (HttpRequest $r) {
            if (!str_ends_with($r->url(), '/messages')) {
                return false;
            }
            $c = collect($r['template']['components']);

            return $r['template']['name'] === 'order_update' && $r['template']['language']['code'] === 'en_US'
                && $c->firstWhere('type', 'header')['parameters'] === [['type' => 'text', 'text' => 'Sample 1']]
                && $c->firstWhere('type', 'body')['parameters'] === [
                    ['type' => 'text', 'parameter_name' => 'party_name', 'text' => 'Ramesh'],
                    ['type' => 'text', 'parameter_name' => 'shop_name', 'text' => 'Shree Traders'],
                ]
                && $c->firstWhere('type', 'button')['index'] === '1';
        });
    }

    public function test_test_send_requires_media_url_and_approval(): void
    {
        AppSetting::set('whatsapp_phone_number_id', '106540352242922');
        Http::fake(['graph.facebook.com/*' => Http::response($this->metaList())]);

        $this->postJson(route('admin.settings.whatsapp.test_send'), ['phone' => '9876543210', 'template_name' => 'invoice_pdf', 'language_code' => 'hi'])
            ->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'document header'));

        $this->postJson(route('admin.settings.whatsapp.test_send'), ['phone' => '9876543210', 'template_name' => 'promo', 'language_code' => 'en'])
            ->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'only approved'));

        $this->postJson(route('admin.settings.whatsapp.test_send'), ['phone' => '9876543210', 'template_name' => 'nope', 'language_code' => 'en'])
            ->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'not found in Meta'));

        Http::assertNotSent(fn (HttpRequest $r) => str_ends_with($r->url(), '/messages'));
    }
}
