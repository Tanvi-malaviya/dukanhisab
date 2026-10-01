<?php

namespace Tests\Feature;

use App\Models\AddOn;
use App\Models\Payment;
use App\Models\Shop;
use App\Models\User;
use App\Models\UserAddOn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WebsiteAddonOneTimeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        // Force the mock-payment path regardless of real Razorpay keys present in this machine's
        // .env — tests must be deterministic and never make live calls to a payment gateway.
        config(['services.razorpay.key' => null, 'services.razorpay.secret' => null]);
        $this->user = User::factory()->create();
        Shop::create(['owner_id' => $this->user->id, 'name' => 'S', 'status' => 'active']);
        Sanctum::actingAs($this->user);
        AddOn::updateOrCreate(['slug' => 'website'], ['title' => 'Shop Website', 'type' => 'website', 'price' => 200, 'billing_period' => 'lifetime', 'status' => 'active']);
        AddOn::updateOrCreate(['slug' => 'shop'], ['title' => 'Extra Shop', 'type' => 'shop', 'price' => 200, 'billing_period' => 'yearly', 'status' => 'active']);
    }

    public function test_website_addon_purchase_returns_a_one_time_order_not_a_subscription(): void
    {
        $r = $this->postJson('/api/v1/shopowner/add-ons/purchase', ['slug' => 'website'])->assertStatus(200)->json();
        $this->assertTrue($r['is_one_time']);
        $this->assertArrayHasKey('order_id', $r);
        $this->assertArrayNotHasKey('subscription_id', $r);
    }

    public function test_shop_addon_purchase_still_returns_a_subscription(): void
    {
        $r = $this->postJson('/api/v1/shopowner/add-ons/purchase', ['slug' => 'shop', 'quantity' => 1])->assertStatus(200)->json();
        $this->assertArrayNotHasKey('is_one_time', $r);
        $this->assertArrayHasKey('subscription_id', $r);
    }

    public function test_verifying_website_addon_with_order_id_activates_it_forever_with_no_autorenew(): void
    {
        $order = $this->postJson('/api/v1/shopowner/add-ons/purchase', ['slug' => 'website'])->json();
        $r = $this->postJson('/api/v1/shopowner/add-ons/verify-payment', [
            'slug' => 'website',
            'razorpay_order_id' => $order['order_id'],
            'razorpay_payment_id' => 'pay_mock_' . uniqid(),
            'razorpay_signature' => 'sig_mock',
        ])->assertStatus(200);

        $grant = UserAddOn::where('user_id', $this->user->id)->first();
        $this->assertNull($grant->ends_at, 'a one-time purchase must never expire');
        $this->assertFalse($grant->auto_renew);
        $this->assertTrue($this->user->fresh()->hasActiveWebsiteAddon());
        $this->assertSame(1, Payment::count());
    }

    public function test_verifying_website_addon_without_order_id_is_rejected(): void
    {
        $this->postJson('/api/v1/shopowner/add-ons/verify-payment', [
            'slug' => 'website',
            'razorpay_subscription_id' => 'sub_mock_123',
            'razorpay_payment_id' => 'pay_mock_' . uniqid(),
            'razorpay_signature' => 'sig_mock',
        ])->assertStatus(422);
    }

    public function test_shop_addon_still_verifies_with_a_subscription_id_and_expires_yearly(): void
    {
        $sub = $this->postJson('/api/v1/shopowner/add-ons/purchase', ['slug' => 'shop', 'quantity' => 1])->json();
        $this->postJson('/api/v1/shopowner/add-ons/verify-payment', [
            'slug' => 'shop',
            'razorpay_subscription_id' => $sub['subscription_id'],
            'razorpay_payment_id' => 'pay_mock_' . uniqid(),
            'razorpay_signature' => 'sig_mock',
        ])->assertStatus(200);

        $grant = UserAddOn::where('user_id', $this->user->id)->first();
        $this->assertNotNull($grant->ends_at);
        $this->assertTrue($grant->auto_renew);
    }

    public function test_cancelling_a_one_time_website_addon_is_rejected(): void
    {
        $order = $this->postJson('/api/v1/shopowner/add-ons/purchase', ['slug' => 'website'])->json();
        $this->postJson('/api/v1/shopowner/add-ons/verify-payment', [
            'slug' => 'website',
            'razorpay_order_id' => $order['order_id'],
            'razorpay_payment_id' => 'pay_mock_' . uniqid(),
            'razorpay_signature' => 'sig_mock',
        ]);
        $grant = UserAddOn::where('user_id', $this->user->id)->first();
        $this->postJson("/api/v1/shopowner/add-ons/{$grant->id}/cancel")->assertStatus(400);
    }

    public function test_admin_created_website_addon_defaults_to_lifetime_billing(): void
    {
        $admin = \App\Models\Admin::create(['name' => 'R', 'email' => 'r2@test.com', 'password' => 'secret123', 'role' => 'superadmin', 'status' => 'active']);
        $this->actingAs($admin, 'admin')->post(route('admin.addons.store'), [
            'title' => 'New Website', 'type' => 'website', 'price' => 300,
        ])->assertSessionHasNoErrors();
        $this->assertSame('lifetime', AddOn::where('type', 'website')->first()->billing_period);

        $this->actingAs($admin, 'admin')->post(route('admin.addons.store'), [
            'title' => 'New Shop Slot', 'type' => 'shop', 'price' => 150,
        ])->assertSessionHasNoErrors();
        $this->assertSame('yearly', AddOn::where('type', 'shop')->first()->billing_period);
    }

    public function test_admin_chosen_billing_type_drives_purchase_on_web_and_app(): void
    {
        $admin = \App\Models\Admin::create(['name' => 'R', 'email' => 'r3@test.com', 'password' => 'secret123', 'role' => 'superadmin', 'status' => 'active']);
        $shopAddOn = AddOn::where('slug', 'shop')->first();

        // Admin switches the Shop add-on to a one-time purchase.
        $this->actingAs($admin, 'admin')->post(route('admin.addons.update', $shopAddOn->id), [
            'title' => 'Extra Shop', 'price' => 200, 'status' => 'active', 'billing_period' => 'lifetime',
        ])->assertSessionHasNoErrors();
        $this->assertSame('lifetime', $shopAddOn->fresh()->billing_period);

        // The plans list both clients render carries it…
        Sanctum::actingAs($this->user);
        $plans = collect($this->getJson('/api/v1/shopowner/add-ons')->assertStatus(200)->json('add_ons'))->keyBy('slug');
        $this->assertSame('lifetime', $plans['shop']['billing_period']);

        // …and purchase + verify follow it: a one-time order that never expires.
        $order = $this->postJson('/api/v1/shopowner/add-ons/purchase', ['slug' => 'shop', 'quantity' => 2])->assertStatus(200)->json();
        $this->assertTrue($order['is_one_time']);
        $this->postJson('/api/v1/shopowner/add-ons/verify-payment', [
            'slug' => 'shop', 'quantity' => 2,
            'razorpay_order_id' => $order['order_id'],
            'razorpay_payment_id' => 'pay_mock_' . uniqid(),
            'razorpay_signature' => 'sig_mock',
        ])->assertStatus(200);
        $grant = UserAddOn::where('user_id', $this->user->id)->first();
        $this->assertNull($grant->ends_at);
        $this->assertSame(2, $grant->quantity);

        // Switching back makes new purchases recurring again.
        $this->actingAs($admin, 'admin')->post(route('admin.addons.update', $shopAddOn->id), [
            'title' => 'Extra Shop', 'price' => 200, 'status' => 'active', 'billing_period' => 'yearly',
        ])->assertSessionHasNoErrors();
        Sanctum::actingAs($this->user);
        $this->assertArrayHasKey('subscription_id', $this->postJson('/api/v1/shopowner/add-ons/purchase', ['slug' => 'shop'])->json());
    }

    public function test_admin_billing_type_rejects_unknown_values(): void
    {
        $admin = \App\Models\Admin::create(['name' => 'R', 'email' => 'r4@test.com', 'password' => 'secret123', 'role' => 'superadmin', 'status' => 'active']);
        $this->actingAs($admin, 'admin')->post(route('admin.addons.store'), [
            'title' => 'X', 'type' => 'shop', 'price' => 150, 'billing_period' => 'monthly',
        ])->assertSessionHasErrors('billing_period');
    }
}
