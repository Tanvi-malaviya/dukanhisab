<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsAppMessage;
use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WhatsAppMessageLog;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsApp\WhatsAppException;
use App\Services\WhatsApp\WhatsAppMessenger;
use App\Services\WhatsApp\WhatsAppWallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Sending engine: WhatsAppMessenger → queued log → SendWhatsAppMessage job → Cloud API, the
 * signed invoice PDF links, and the Meta status webhook.
 */
class WhatsAppSendingTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;
    private Customer $customer;
    private WhatsAppTemplate $invoiceTemplate;

    protected function setUp(): void
    {
        parent::setUp();

        AppSetting::set('whatsapp_enabled', 'yes');
        AppSetting::set('whatsapp_phone_number_id', '106540352242922');
        AppSetting::set('whatsapp_access_token', Crypt::encryptString('EAAGtoken'));
        AppSetting::set('whatsapp_app_secret', Crypt::encryptString('app-secret'));
        AppSetting::set('whatsapp_webhook_verify_token', 'verify-me');

        $owner = User::factory()->create(['language' => 'gu']);
        $this->shop = Shop::create(['owner_id' => $owner->id, 'name' => 'Shree Traders', 'mobile' => '9999999999', 'currency' => 'INR']);
        $this->customer = Customer::create(['shop_id' => $this->shop->id, 'name' => 'Ramesh', 'mobile' => '98765 43210', 'due_amount' => 1200]);
        app(WhatsAppWallet::class)->adjust($this->shop, 10, 'test credits', null);

        $this->invoiceTemplate = WhatsAppTemplate::create([
            'key' => 'sale_invoice', 'language' => 'en', 'meta_template_name' => 'sale_invoice_en', 'meta_language_code' => 'en',
            'body' => 'Hi {{1}}, invoice {{2}} from {{3}} for {{4}}. Due: {{5}}.',
            'variables' => ['party_name', 'invoice_no', 'shop_name', 'amount', 'due_amount'],
            'has_document' => true, 'status' => 'active',
        ]);
    }

    private function sale(): Sale
    {
        return Sale::create([
            'shop_id' => $this->shop->id, 'customer_id' => $this->customer->id, 'sale_number' => 'INV-1024',
            'subtotal' => 2450, 'discount' => 0, 'grand_total' => 2450, 'paid_amount' => 1250,
            'payment_type' => 'Credit', 'status' => 'completed', 'sale_date' => '2026-10-01 10:00:00',
        ]);
    }

    private function postWebhook(array $payload, ?string $secret = 'app-secret')
    {
        $body = json_encode($payload);
        $headers = ['Content-Type' => 'application/json'];
        if ($secret) {
            $headers['X-Hub-Signature-256'] = 'sha256=' . hash_hmac('sha256', $body, $secret);
        }

        return $this->call('POST', '/api/v1/whatsapp/webhook', [], [], [], $this->transformHeadersToServerVars($headers), $body);
    }

    private function statusPayload(string $wamid, string $status, array $extra = []): array
    {
        return ['object' => 'whatsapp_business_account', 'entry' => [['changes' => [['field' => 'messages', 'value' => [
            'statuses' => [['id' => $wamid, 'status' => $status, 'timestamp' => '1790000000'] + $extra],
        ]]]]]];
    }

    // ------------------------------------------------------------ messenger

    public function test_send_queues_a_log_with_values_and_a_signed_pdf_link(): void
    {
        Queue::fake();

        $log = app(WhatsAppMessenger::class)->send($this->shop, 'sale_invoice', $this->customer, ['sale' => $this->sale()]);

        Queue::assertPushed(SendWhatsAppMessage::class, fn ($job) => $job->logId === $log->id);
        $this->assertSame('queued', $log->status);
        $this->assertSame('919876543210', $log->phone);
        $this->assertSame('en', $log->language); // owner prefers gu, but only an English template exists
        $this->assertSame([
            'party_name' => 'Ramesh', 'shop_name' => 'Shree Traders', 'shop_mobile' => '9999999999',
            'invoice_no' => 'INV-1024', 'amount' => '₹2,450.00', 'due_amount' => '₹1,200.00', 'date' => '01 Oct 2026',
        ], $log->payload['values']);
        $this->assertSame('Invoice-INV-1024.pdf', $log->payload['document_name']);
        $this->assertStringContainsString('/api/v1/whatsapp/invoices/sale/', $log->payload['document_url']);
        $this->assertStringContainsString('signature=', $log->payload['document_url']);
    }

    public function test_send_prefers_the_shop_owners_language(): void
    {
        Queue::fake();
        $gu = WhatsAppTemplate::create($this->invoiceTemplate->only(['key', 'body', 'variables', 'has_document', 'status'])
            + ['language' => 'gu', 'meta_template_name' => 'sale_invoice_gu', 'meta_language_code' => 'gu']);

        $log = app(WhatsAppMessenger::class)->send($this->shop, 'sale_invoice', $this->customer, ['sale' => $this->sale()]);

        $this->assertSame($gu->id, $log->whatsapp_template_id);
    }

    public function test_send_refuses_without_mobile_template_or_when_disabled(): void
    {
        Queue::fake();
        $messenger = app(WhatsAppMessenger::class);

        $noMobile = Customer::create(['shop_id' => $this->shop->id, 'name' => 'Walk-in']);
        $this->assertThrowsMessage(fn () => $messenger->send($this->shop, 'sale_invoice', $noMobile), 'does not have a valid mobile number');

        $this->assertThrowsMessage(fn () => $messenger->send($this->shop, 'payment_received', $this->customer), 'not available yet');

        AppSetting::set('whatsapp_enabled', 'no');
        $this->assertThrowsMessage(fn () => $messenger->send($this->shop, 'sale_invoice', $this->customer), 'currently unavailable');

        Queue::assertNothingPushed();
        $this->assertDatabaseCount('whatsapp_message_logs', 0);
    }

    public function test_event_must_match_recipient_type(): void
    {
        $supplier = Supplier::create(['shop_id' => $this->shop->id, 'name' => 'Wholesaler', 'mobile' => '9876500000']);

        $this->expectException(\InvalidArgumentException::class);
        app(WhatsAppMessenger::class)->send($this->shop, 'sale_invoice', $supplier);
    }

    private function assertThrowsMessage(callable $fn, string $needle): void
    {
        try {
            $fn();
            $this->fail('Expected a WhatsAppException.');
        } catch (WhatsAppException $e) {
            $this->assertStringContainsString($needle, $e->getMessage());
        }
    }

    // ------------------------------------------------------------ job

    public function test_job_sends_template_with_document_and_marks_sent(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.ABC']]])]);

        // Queue is sync in tests, so the job runs right away.
        $log = app(WhatsAppMessenger::class)->send($this->shop, 'sale_invoice', $this->customer, ['sale' => $this->sale()]);

        $log->refresh();
        $this->assertSame('sent', $log->status);
        $this->assertSame('wamid.ABC', $log->provider_message_id);
        $this->assertNotNull($log->sent_at);

        Http::assertSent(function (HttpRequest $r) {
            $components = collect($r['template']['components']);
            $header = $components->firstWhere('type', 'header');

            return $r['to'] === '919876543210'
                && $r['template']['name'] === 'sale_invoice_en'
                && $header['parameters'][0]['document']['filename'] === 'Invoice-INV-1024.pdf'
                && str_contains($header['parameters'][0]['document']['link'], 'signature=')
                && array_column($components->firstWhere('type', 'body')['parameters'], 'text')
                    === ['Ramesh', 'INV-1024', 'Shree Traders', '₹2,450.00', '₹1,200.00'];
        });
    }

    public function test_job_marks_permanent_errors_failed(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => [
            'message' => '(#132001) Template name does not exist in the translation', 'code' => 132001,
        ]], 400)]);

        $log = app(WhatsAppMessenger::class)->send($this->shop, 'sale_invoice', $this->customer, ['sale' => $this->sale()]);

        $log->refresh();
        $this->assertSame('failed', $log->status);
        $this->assertStringContainsString('Template name does not exist', $log->error);
        Http::assertSentCount(1);
    }

    public function test_job_releases_temporary_errors_for_retry(): void
    {
        Queue::fake();
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['message' => 'Rate limit hit', 'code' => 130429]], 400)]);
        $log = app(WhatsAppMessenger::class)->send($this->shop, 'sale_invoice', $this->customer, ['sale' => $this->sale()]);

        $job = (new SendWhatsAppMessage($log->id))->withFakeQueueInteractions();
        $job->handle();

        $job->assertReleased(60);
        $this->assertSame('queued', $log->fresh()->status);
    }

    public function test_job_fails_when_template_was_deactivated_after_queueing(): void
    {
        Queue::fake();
        Http::fake();
        $log = app(WhatsAppMessenger::class)->send($this->shop, 'sale_invoice', $this->customer, ['sale' => $this->sale()]);
        $this->invoiceTemplate->update(['status' => 'inactive']);

        (new SendWhatsAppMessage($log->id))->handle();

        $this->assertSame('failed', $log->fresh()->status);
        Http::assertNothingSent();
    }

    // ------------------------------------------------------------ signed invoice links

    public function test_signed_invoice_link_serves_pdf_and_rejects_tampering(): void
    {
        $sale = $this->sale();
        $url = URL::temporarySignedRoute('whatsapp.invoice.sale', now()->addHour(), ['id' => $sale->id], false);

        $this->get($url)->assertOk()->assertHeader('Content-Type', 'application/pdf');

        $this->get(route('whatsapp.invoice.sale', ['id' => $sale->id]))->assertForbidden();
        $this->get(str_replace("/sale/{$sale->id}", '/sale/999', $url))->assertForbidden();

        $this->travel(2)->hours();
        $this->get($url)->assertForbidden();
    }

    // ------------------------------------------------------------ webhook

    public function test_webhook_verification_handshake(): void
    {
        $this->get('/api/v1/whatsapp/webhook?hub.mode=subscribe&hub.verify_token=verify-me&hub.challenge=12345')
            ->assertOk()->assertSee('12345');

        $this->get('/api/v1/whatsapp/webhook?hub.mode=subscribe&hub.verify_token=wrong&hub.challenge=12345')
            ->assertForbidden();
    }

    public function test_webhook_rejects_bad_or_missing_signature(): void
    {
        $this->postWebhook($this->statusPayload('wamid.X', 'delivered'), 'wrong-secret')->assertForbidden();
        $this->postWebhook($this->statusPayload('wamid.X', 'delivered'), null)->assertForbidden();
    }

    public function test_webhook_moves_status_forward_only(): void
    {
        $log = WhatsAppMessageLog::create([
            'shop_id' => $this->shop->id, 'event' => 'sale_invoice', 'recipient_type' => 'customer', 'recipient_id' => $this->customer->id,
            'phone' => '919876543210', 'template_name' => 'sale_invoice_en', 'language' => 'en', 'payload' => [],
            'status' => 'sent', 'provider_message_id' => 'wamid.ABC', 'sent_at' => now(),
        ]);

        $this->postWebhook($this->statusPayload('wamid.ABC', 'read'))->assertOk();
        $log->refresh();
        $this->assertSame('read', $log->status);
        $this->assertNotNull($log->delivered_at); // filled even though "delivered" never arrived
        $this->assertNotNull($log->read_at);

        // A late "delivered" must not move it back.
        $this->postWebhook($this->statusPayload('wamid.ABC', 'delivered'))->assertOk();
        $this->assertSame('read', $log->fresh()->status);
    }

    public function test_webhook_records_delivery_failure(): void
    {
        $log = WhatsAppMessageLog::create([
            'shop_id' => $this->shop->id, 'event' => 'sale_invoice', 'recipient_type' => 'customer', 'recipient_id' => $this->customer->id,
            'phone' => '919876543210', 'template_name' => 'sale_invoice_en', 'language' => 'en', 'payload' => [],
            'status' => 'sent', 'provider_message_id' => 'wamid.ABC',
        ]);

        $this->postWebhook($this->statusPayload('wamid.ABC', 'failed', ['errors' => [[
            'code' => 131026, 'title' => 'Message undeliverable', 'error_data' => ['details' => 'Recipient is not on WhatsApp.'],
        ]]]))->assertOk();

        $log->refresh();
        $this->assertSame('failed', $log->status);
        $this->assertSame('Recipient is not on WhatsApp.', $log->error);
    }
}
