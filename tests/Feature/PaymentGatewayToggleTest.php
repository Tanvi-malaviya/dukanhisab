<?php

namespace Tests\Feature;

use App\Models\AddOn;
use App\Models\Shop;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Admin > Settings > Payment Gateway has a "Gateway Enable/Disable" toggle. It used to only set
 * the runtime config and was never actually checked anywhere — a shop owner could still buy a
 * plan or add-on with the gateway "disabled". These lock in the fix: every purchase entry point
 * refuses once it's off, except downgrading to Free, which never touches Razorpay anyway.
 */
class PaymentGatewayToggleTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        Shop::create(['owner_id' => $this->user->id, 'name' => 'S', 'status' => 'active']);
        Sanctum::actingAs($this->user);
        SubscriptionPlan::updateOrCreate(['slug' => 'free'], ['name' => 'Free', 'billing_period' => 'free', 'price' => 0, 'status' => 'active']);
        SubscriptionPlan::updateOrCreate(['slug' => 'premium'], ['name' => 'Premium', 'billing_period' => 'yearly', 'price' => 499, 'status' => 'active']);
        SubscriptionPlan::updateOrCreate(['slug' => 'business'], ['name' => 'Business', 'billing_period' => 'lifetime', 'price' => 2499, 'status' => 'active']);
        AddOn::updateOrCreate(['slug' => 'shop'], ['title' => 'Extra Shop', 'type' => 'shop', 'price' => 200, 'billing_period' => 'yearly', 'status' => 'active']);
        AddOn::updateOrCreate(['slug' => 'website'], ['title' => 'Shop Website', 'type' => 'website', 'price' => 200, 'billing_period' => 'lifetime', 'status' => 'active']);
    }

    public function test_gateway_disabled_blocks_subscription_upgrade(): void
    {
        config(['services.razorpay.enabled' => false]);
        $this->postJson('/api/v1/shopowner/subscription/upgrade', ['plan_slug' => 'premium'])->assertStatus(503);
    }

    public function test_gateway_disabled_blocks_subscription_create_order(): void
    {
        config(['services.razorpay.enabled' => false]);
        $this->postJson('/api/v1/shopowner/subscription/create-order', ['plan_slug' => 'premium'])->assertStatus(503);
    }

    public function test_gateway_disabled_still_allows_downgrading_to_free(): void
    {
        config(['services.razorpay.enabled' => false]);
        $this->postJson('/api/v1/shopowner/subscription/upgrade', ['plan_slug' => 'free'])->assertStatus(200);
    }

    public function test_gateway_disabled_blocks_addon_purchase(): void
    {
        config(['services.razorpay.enabled' => false]);
        $this->postJson('/api/v1/shopowner/add-ons/purchase', ['slug' => 'shop', 'quantity' => 1])->assertStatus(503);
        $this->postJson('/api/v1/shopowner/add-ons/purchase', ['slug' => 'website'])->assertStatus(503);
    }

    public function test_gateway_enabled_allows_purchases_through_to_the_mock_payment_path(): void
    {
        config(['services.razorpay.enabled' => true, 'services.razorpay.key' => null, 'services.razorpay.secret' => null]);
        $this->postJson('/api/v1/shopowner/subscription/upgrade', ['plan_slug' => 'premium'])->assertStatus(200);
        $this->postJson('/api/v1/shopowner/add-ons/purchase', ['slug' => 'shop', 'quantity' => 1])->assertStatus(200);
    }

    public function test_gateway_toggle_defaults_to_enabled_when_never_configured(): void
    {
        // No explicit config(['services.razorpay.enabled' => ...]) call at all — simulates a shop
        // that predates this setting ever being saved.
        config(['services.razorpay.key' => null, 'services.razorpay.secret' => null]);
        $this->postJson('/api/v1/shopowner/subscription/upgrade', ['plan_slug' => 'premium'])->assertStatus(200);
    }
}
