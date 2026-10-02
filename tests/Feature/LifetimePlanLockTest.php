<?php

namespace Tests\Feature;

use App\Models\Shop;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * A Lifetime plan is bought once and never expires, so its owner has nothing to switch to.
 * Downgrading to Free used to be one click away and permanently lost the plan, since the
 * Lifetime offer is only open for 7 days after registration.
 */
class LifetimePlanLockTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private SubscriptionPlan $business;

    protected function setUp(): void
    {
        parent::setUp();
        SubscriptionPlan::updateOrCreate(['slug' => 'free'], ['name' => 'Free', 'billing_period' => 'free', 'price' => 0, 'status' => 'active']);
        SubscriptionPlan::updateOrCreate(['slug' => 'premium'], ['name' => 'Premium', 'billing_period' => 'yearly', 'price' => 499, 'status' => 'active']);
        $this->business = SubscriptionPlan::updateOrCreate(['slug' => 'business'], ['name' => 'Business', 'billing_period' => 'lifetime', 'price' => 2499, 'status' => 'active']);

        $this->user = User::factory()->create(['active_plan_id' => $this->business->id]);
        Shop::create(['owner_id' => $this->user->id, 'name' => 'S', 'status' => 'active']);
        Subscription::create(['user_id' => $this->user->id, 'plan_id' => $this->business->id, 'status' => 'active', 'starts_at' => now()]);
        Sanctum::actingAs($this->user);
        config(['services.razorpay.enabled' => true, 'services.razorpay.key' => null, 'services.razorpay.secret' => null]);
    }

    public function test_lifetime_owner_cannot_downgrade_to_free(): void
    {
        $this->postJson('/api/v1/shopowner/subscription/upgrade', ['plan_slug' => 'free'])->assertStatus(400);
        $this->assertSame($this->business->id, $this->user->fresh()->active_plan_id);
    }

    public function test_lifetime_owner_cannot_switch_to_premium(): void
    {
        $this->postJson('/api/v1/shopowner/subscription/upgrade', ['plan_slug' => 'premium'])->assertStatus(400);
        $this->postJson('/api/v1/shopowner/subscription/create-order', ['plan_slug' => 'premium'])->assertStatus(400);
    }

    public function test_lifetime_owner_cannot_cancel(): void
    {
        $this->postJson('/api/v1/shopowner/subscription/cancel')->assertStatus(400);
        $this->assertSame($this->business->id, $this->user->fresh()->active_plan_id);
    }

    public function test_premium_owner_can_still_downgrade_to_free(): void
    {
        $premium = SubscriptionPlan::where('slug', 'premium')->first();
        $this->user->update(['active_plan_id' => $premium->id]);

        $this->postJson('/api/v1/shopowner/subscription/upgrade', ['plan_slug' => 'free'])->assertStatus(200);
    }
}
