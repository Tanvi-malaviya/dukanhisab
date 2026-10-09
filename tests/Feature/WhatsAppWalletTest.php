<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\Shop;
use App\Models\User;
use App\Models\WhatsAppCreditLedger;
use App\Models\WhatsAppMessageLog;
use App\Models\WhatsAppPack;
use App\Models\WhatsAppPackPurchase;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsApp\InsufficientCreditsException;
use App\Services\WhatsApp\WhatsAppMessenger;
use App\Services\WhatsApp\WhatsAppWallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Message credits: buying packs through Razorpay, one credit per message, refunds on failure.
 */
class WhatsAppWalletTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Shop $shop;
    private WhatsAppPack $pack;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.razorpay.enabled' => true, 'services.razorpay.key' => 'rzp_test_key', 'services.razorpay.secret' => 'rzp_secret', 'services.razorpay.webhook_secret' => 'hook_secret']);

        $this->user = User::factory()->create();
        $this->shop = Shop::create(['owner_id' => $this->user->id, 'name' => 'Shree Traders', 'status' => 'active']);
        $this->pack = WhatsAppPack::create(['name' => 'Starter', 'credits' => 100, 'price' => 99, 'status' => 'active']);
        Sanctum::actingAs($this->user);
    }

    private function h(): array
    {
        return ['X-Shop-ID' => $this->shop->id];
    }

    private function wallet(): WhatsAppWallet
    {
        return app(WhatsAppWallet::class);
    }

    private function startPurchase(): string
    {
        Http::fake(['api.razorpay.com/v1/orders' => Http::response(['id' => 'order_ABC'])]);

        return $this->withHeaders($this->h())->postJson("/api/v1/whatsapp/packs/{$this->pack->id}/purchase")
            ->assertOk()->json('order_id');
    }

    private function enableSending(): void
    {
        AppSetting::set('whatsapp_enabled', 'yes');
        AppSetting::set('whatsapp_phone_number_id', '106540352242922');
        AppSetting::set('whatsapp_access_token', Crypt::encryptString('EAAGtoken'));
        WhatsAppTemplate::create([
            'key' => 'payment_received', 'language' => 'en', 'meta_template_name' => 'payment_received_en', 'meta_language_code' => 'en',
            'body' => 'Hi {{1}}', 'variables' => ['party_name'], 'status' => 'active',
        ]);
    }

    // ------------------------------------------------------------ purchase

    public function test_wallet_endpoint_shows_balance_and_active_packs(): void
    {
        WhatsAppPack::create(['name' => 'Hidden', 'credits' => 5, 'price' => 5, 'status' => 'inactive']);

        $this->withHeaders($this->h())->getJson('/api/v1/whatsapp/wallet')
            ->assertOk()
            ->assertJson(['balance' => 0, 'low_balance' => true])
            ->assertJsonCount(1, 'packs')
            ->assertJsonPath('packs.0.name', 'Starter');
    }

    public function test_purchase_creates_order_with_server_side_price_and_verify_adds_credits_once(): void
    {
        $orderId = $this->startPurchase();
        Http::assertSent(fn ($r) => $r['amount'] === 9900 && $r['notes']['type'] === 'whatsapp_pack');
        $this->assertDatabaseHas('whatsapp_pack_purchases', ['razorpay_order_id' => 'order_ABC', 'credits' => 100, 'status' => 'pending']);

        $verify = fn () => $this->withHeaders($this->h())->postJson('/api/v1/whatsapp/packs/verify-payment', [
            'razorpay_order_id' => $orderId,
            'razorpay_payment_id' => 'pay_1',
            'razorpay_signature' => hash_hmac('sha256', $orderId . '|pay_1', 'rzp_secret'),
        ]);

        $verify()->assertOk()->assertJson(['balance' => 100]);
        $verify()->assertOk()->assertJson(['balance' => 100]); // replay doesn't double-credit

        $this->assertSame(100, $this->wallet()->balance($this->shop->id));
        $this->assertSame(1, WhatsAppCreditLedger::where('type', 'purchase')->count());
    }

    public function test_verify_rejects_a_bad_signature(): void
    {
        $orderId = $this->startPurchase();

        $this->withHeaders($this->h())->postJson('/api/v1/whatsapp/packs/verify-payment', [
            'razorpay_order_id' => $orderId, 'razorpay_payment_id' => 'pay_1', 'razorpay_signature' => 'forged',
        ])->assertStatus(400);

        $this->assertSame(0, $this->wallet()->balance($this->shop->id));
    }

    public function test_another_shops_order_cannot_be_verified(): void
    {
        $orderId = $this->startPurchase();
        $other = User::factory()->create();
        $otherShop = Shop::create(['owner_id' => $other->id, 'name' => 'Other', 'status' => 'active']);
        Sanctum::actingAs($other);

        $this->withHeaders(['X-Shop-ID' => $otherShop->id])->postJson('/api/v1/whatsapp/packs/verify-payment', [
            'razorpay_order_id' => $orderId, 'razorpay_payment_id' => 'pay_1',
            'razorpay_signature' => hash_hmac('sha256', $orderId . '|pay_1', 'rzp_secret'),
        ])->assertNotFound();
    }

    public function test_inactive_pack_cannot_be_bought(): void
    {
        $this->pack->update(['status' => 'inactive']);

        $this->withHeaders($this->h())->postJson("/api/v1/whatsapp/packs/{$this->pack->id}/purchase")->assertNotFound();
    }

    public function test_razorpay_order_paid_webhook_credits_the_pack(): void
    {
        $orderId = $this->startPurchase();
        $body = json_encode(['event' => 'order.paid', 'payload' => [
            'order' => ['entity' => ['id' => $orderId, 'notes' => ['type' => 'whatsapp_pack']]],
            'payment' => ['entity' => ['id' => 'pay_hook']],
        ]]);

        $this->call('POST', '/api/v1/shopowner/razorpay/webhook', [], [], [], $this->transformHeadersToServerVars([
            'Content-Type' => 'application/json',
            'X-Razorpay-Signature' => hash_hmac('sha256', $body, 'hook_secret'),
        ]), $body)->assertOk();

        $this->assertSame(100, $this->wallet()->balance($this->shop->id));
        $this->assertSame('pay_hook', WhatsAppPackPurchase::first()->razorpay_payment_id);
    }

    // ------------------------------------------------------------ spending

    public function test_each_message_uses_one_credit_and_none_are_queued_without_credits(): void
    {
        Queue::fake();
        $this->enableSending();
        $customer = Customer::create(['shop_id' => $this->shop->id, 'name' => 'Ramesh', 'mobile' => '9876543210']);
        $this->wallet()->adjust($this->shop, 1, 'test', null);

        app(WhatsAppMessenger::class)->send($this->shop, 'payment_received', $customer);
        $this->assertSame(0, $this->wallet()->balance($this->shop->id));

        try {
            app(WhatsAppMessenger::class)->send($this->shop, 'payment_received', $customer);
            $this->fail('Expected InsufficientCreditsException.');
        } catch (InsufficientCreditsException) {
        }

        $this->assertSame(1, WhatsAppMessageLog::count()); // the refused message left no log behind
    }

    public function test_failed_message_refunds_its_credit_only_once(): void
    {
        $this->enableSending();
        $customer = Customer::create(['shop_id' => $this->shop->id, 'name' => 'Ramesh', 'mobile' => '9876543210']);
        $this->wallet()->adjust($this->shop, 5, 'test', null);
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['message' => 'Bad template', 'code' => 132001]], 400)]);

        $log = app(WhatsAppMessenger::class)->send($this->shop, 'payment_received', $customer);

        $this->assertSame('failed', $log->fresh()->status);
        $this->assertSame(5, $this->wallet()->balance($this->shop->id));

        $log->fresh()->update(['status' => 'sent']);
        $log->fresh()->markFailed('Again'); // e.g. a later webhook failure
        $this->assertSame(5, $this->wallet()->balance($this->shop->id));
        $this->assertSame(1, WhatsAppCreditLedger::where('type', 'refund')->count());
    }

    public function test_adjustment_cannot_go_below_zero(): void
    {
        $this->wallet()->adjust($this->shop, 3, 'gift', null);

        $this->expectException(InsufficientCreditsException::class);
        $this->wallet()->adjust($this->shop, -4, 'oops', null);
    }
}
