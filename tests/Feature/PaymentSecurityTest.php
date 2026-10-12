<?php

namespace Tests\Feature;

use App\Models\AddOn;
use App\Models\Payment;
use App\Models\Shop;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserAddOn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Verify used to activate whatever plan / add-on / quantity the client named, for whichever account
 * sent it. These lock in that only what was sold at checkout, to that buyer, once, gets activated —
 * and that the webhook covers customers whose browser never reached verify.
 */
class PaymentSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private SubscriptionPlan $premium;
    private SubscriptionPlan $business;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.razorpay.enabled' => true, 'services.razorpay.key' => null, 'services.razorpay.secret' => null, 'services.razorpay.webhook_secret' => 'whsec_test']);
        SubscriptionPlan::updateOrCreate(['slug' => 'free'], ['name' => 'Free', 'billing_period' => 'free', 'price' => 0, 'status' => 'active']);
        $this->premium = SubscriptionPlan::updateOrCreate(['slug' => 'premium'], ['name' => 'Premium', 'billing_period' => 'yearly', 'price' => 499, 'status' => 'active']);
        $this->business = SubscriptionPlan::updateOrCreate(['slug' => 'business'], ['name' => 'Business', 'billing_period' => 'lifetime', 'price' => 2499, 'status' => 'active']);
        AddOn::updateOrCreate(['slug' => 'shop'], ['title' => 'Extra Shop', 'type' => 'shop', 'price' => 200, 'billing_period' => 'yearly', 'status' => 'active']);
        AddOn::updateOrCreate(['slug' => 'website'], ['title' => 'Shop Website', 'type' => 'website', 'price' => 300, 'billing_period' => 'lifetime', 'status' => 'active']);

        $this->user = $this->makeUser();
        Sanctum::actingAs($this->user);
    }

    private function makeUser(): User
    {
        $user = User::factory()->create();
        Shop::create(['owner_id' => $user->id, 'name' => 'S', 'status' => 'active']);
        return $user;
    }

    private function verifyPlan(string $slug, array $checkout, ?string $paymentId = null)
    {
        return $this->postJson('/api/v1/shopowner/subscription/verify', [
            'plan_slug' => $slug,
            'razorpay_order_id' => $checkout['order_id'] ?? '',
            'razorpay_subscription_id' => $checkout['subscription_id'] ?? '',
            'razorpay_payment_id' => $paymentId ?? 'pay_' . uniqid(),
            'razorpay_signature' => 'sig',
        ]);
    }

    private function webhook(array $body)
    {
        $json = json_encode($body);
        return $this->call('POST', '/api/v1/shopowner/razorpay/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_RAZORPAY_SIGNATURE' => hash_hmac('sha256', $json, 'whsec_test'),
        ], $json);
    }

    public function test_paying_for_premium_cannot_be_claimed_as_lifetime(): void
    {
        $order = $this->postJson('/api/v1/shopowner/subscription/create-order', ['plan_slug' => 'premium'])->assertStatus(200)->json();

        $this->verifyPlan('business', $order)->assertStatus(200);

        $this->assertSame($this->premium->id, $this->user->fresh()->active_plan_id);
    }

    public function test_paying_for_one_shop_cannot_be_claimed_as_twenty(): void
    {
        $sub = $this->postJson('/api/v1/shopowner/add-ons/purchase', ['slug' => 'shop', 'quantity' => 1])->json();

        $this->postJson('/api/v1/shopowner/add-ons/verify-payment', [
            'slug' => 'shop', 'quantity' => 20,
            'razorpay_subscription_id' => $sub['subscription_id'],
            'razorpay_payment_id' => 'pay_' . uniqid(), 'razorpay_signature' => 'sig',
        ])->assertStatus(200);

        $this->assertSame(1, UserAddOn::where('user_id', $this->user->id)->sum('quantity'));
        $this->assertEquals(200, Payment::first()->amount);
    }

    public function test_another_account_cannot_reuse_someone_elses_payment(): void
    {
        $order = $this->postJson('/api/v1/shopowner/subscription/create-order', ['plan_slug' => 'premium'])->json();
        $this->verifyPlan('premium', $order, 'pay_A')->assertStatus(200);

        $other = $this->makeUser();
        Sanctum::actingAs($other);
        $this->verifyPlan('premium', $order, 'pay_A')->assertStatus(400);

        $this->assertNotSame($this->premium->id, $other->fresh()->active_plan_id);
    }

    public function test_a_checkout_activates_once_and_retries_are_harmless(): void
    {
        $order = $this->postJson('/api/v1/shopowner/subscription/create-order', ['plan_slug' => 'premium'])->json();

        $this->verifyPlan('premium', $order, 'pay_1')->assertStatus(200);
        $this->verifyPlan('premium', $order, 'pay_1')->assertStatus(200); // network retry
        $this->verifyPlan('premium', $order, 'pay_2')->assertStatus(400); // replay with another payment

        $this->assertSame(1, Payment::count());
        $this->assertSame('pay_1', Payment::first()->transaction_id);
    }

    public function test_verify_without_a_checkout_we_created_is_rejected(): void
    {
        $this->verifyPlan('business', ['order_id' => 'order_made_up'])->assertStatus(400);
        $this->assertNotSame($this->business->id, $this->user->fresh()->active_plan_id);
    }

    public function test_webhook_activates_lifetime_when_the_browser_never_reached_verify(): void
    {
        $order = $this->postJson('/api/v1/shopowner/subscription/create-order', ['plan_slug' => 'business'])->json();

        $this->webhook(['event' => 'order.paid', 'payload' => [
            'order' => ['entity' => ['id' => $order['order_id'], 'notes' => ['user_id' => (string) $this->user->id, 'plan_id' => (string) $this->business->id]]],
            'payment' => ['entity' => ['id' => 'pay_wh', 'amount' => 249900]],
        ]])->assertStatus(200);

        $this->assertSame($this->business->id, $this->user->fresh()->active_plan_id);

        // The late verify call is then a harmless no-op: still one payment row.
        $this->verifyPlan('business', $order, 'pay_wh')->assertStatus(200);
        $this->assertSame(1, Payment::count());
    }

    public function test_webhook_activates_one_time_addon_when_the_browser_never_reached_verify(): void
    {
        $order = $this->postJson('/api/v1/shopowner/add-ons/purchase', ['slug' => 'website'])->json();

        $this->webhook(['event' => 'order.paid', 'payload' => [
            'order' => ['entity' => ['id' => $order['order_id'], 'notes' => ['type' => 'addon_one_time', 'user_id' => (string) $this->user->id]]],
            'payment' => ['entity' => ['id' => 'pay_wh2', 'amount' => 30000]],
        ]])->assertStatus(200);

        $this->assertTrue($this->user->fresh()->hasActiveWebsiteAddon());
        $this->assertEquals(300, Payment::first()->amount);
    }

    public function test_leftover_yearly_charge_does_not_downgrade_a_lifetime_owner(): void
    {
        $order = $this->postJson('/api/v1/shopowner/subscription/create-order', ['plan_slug' => 'business'])->json();
        $this->verifyPlan('business', $order)->assertStatus(200);

        $this->webhook(['event' => 'subscription.charged', 'payload' => [
            'subscription' => ['entity' => ['id' => 'sub_mock_old', 'notes' => ['user_id' => (string) $this->user->id, 'plan_slug' => 'premium']]],
            'payment' => ['entity' => ['id' => 'pay_renew', 'amount' => 49900]],
        ]])->assertStatus(200);

        $this->assertSame($this->business->id, $this->user->fresh()->active_plan_id);
    }

    public function test_first_payment_is_recorded_once_not_twice(): void
    {
        $sub = $this->postJson('/api/v1/shopowner/subscription/upgrade', ['plan_slug' => 'premium'])->json();
        $this->verifyPlan('premium', $sub, 'pay_first')->assertStatus(200);

        // Razorpay's subscription.charged for that same first payment.
        $ref = $sub['subscription_id'] ?? $sub['order_id'];
        $this->webhook(['event' => 'subscription.charged', 'payload' => [
            'subscription' => ['entity' => ['id' => $ref, 'notes' => ['user_id' => (string) $this->user->id, 'plan_slug' => 'premium']]],
            'payment' => ['entity' => ['id' => 'pay_first', 'amount' => 49900]],
        ]])->assertStatus(200);

        $this->assertSame(1, Payment::count());
    }

    public function test_production_never_offers_a_simulated_payment(): void
    {
        $this->app['env'] = 'production';

        $this->postJson('/api/v1/shopowner/subscription/create-order', ['plan_slug' => 'premium'])->assertStatus(503);
        $this->postJson('/api/v1/shopowner/add-ons/purchase', ['slug' => 'website'])->assertStatus(502);
        $this->postJson('/api/v1/shopowner/add-ons/purchase', ['slug' => 'shop', 'quantity' => 1])->assertStatus(502);
    }

    public function test_refunding_an_old_premium_payment_keeps_a_later_lifetime_plan(): void
    {
        $order = $this->postJson('/api/v1/shopowner/subscription/create-order', ['plan_slug' => 'premium'])->json();
        $this->verifyPlan('premium', $order, 'pay_old')->assertStatus(200);
        $order = $this->postJson('/api/v1/shopowner/subscription/create-order', ['plan_slug' => 'business'])->json();
        $this->verifyPlan('business', $order, 'pay_life')->assertStatus(200);

        $admin = \App\Models\Admin::create(['name' => 'R', 'email' => 'r@test.com', 'password' => 'secret123', 'role' => 'superadmin', 'status' => 'active']);
        $old = Payment::where('transaction_id', 'pay_old')->first();
        $this->actingAs($admin, 'admin')->post(route('admin.payments.refund', $old->id), ['reason' => 'test', 'status' => 'successful']);

        $this->assertSame($this->business->id, $this->user->fresh()->active_plan_id);
        $this->assertTrue(Subscription::where('user_id', $this->user->id)->where('plan_id', $this->business->id)->where('status', 'active')->exists());
    }
}
