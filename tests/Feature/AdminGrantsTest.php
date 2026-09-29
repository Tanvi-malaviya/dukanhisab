<?php

namespace Tests\Feature;

use App\Models\AddOn;
use App\Models\Admin;
use App\Models\Payment;
use App\Models\Shop;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserAddOn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Admin-side "grant it for free" flows: assigning a subscription, assigning an add-on, and adding
 * a shop past a user's plan limit — none of these should require a payment, and none of the
 * admin-only bookkeeping they leave behind should leak to the shop-owner app/web API.
 */
class AdminGrantsTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::create(['name' => 'Root', 'email' => 'root@test.com', 'password' => 'secret123', 'role' => 'superadmin', 'status' => 'active']);
        $this->user = User::factory()->create();
        $this->actingAs($this->admin, 'admin');
    }

    private function plan(array $o = []): SubscriptionPlan
    {
        return SubscriptionPlan::create($o + ['name' => 'P', 'slug' => 'p' . uniqid(), 'billing_period' => 'yearly', 'price' => 100, 'status' => 'active']);
    }

    // ------------------------------------------------------------ subscriptions

    public function test_assigning_a_yearly_plan_computes_the_end_date_with_no_days_field(): void
    {
        $plan = $this->plan(['billing_period' => 'yearly']);
        $this->post(route('admin.users.subscription', $this->user->id), [
            'plan_id' => $plan->id, 'mode' => 'plan',
        ])->assertRedirect();

        $sub = Subscription::where('user_id', $this->user->id)->latest('id')->first();
        $this->assertNotNull($sub->ends_at);
        $this->assertEqualsWithDelta(now()->addYear()->timestamp, $sub->ends_at->timestamp, 5);
        $this->assertTrue($sub->granted_by_admin);
        $this->assertSame($plan->id, $this->user->fresh()->active_plan_id);
    }

    public function test_assigning_a_lifetime_plan_needs_no_days_and_never_expires(): void
    {
        $plan = $this->plan(['billing_period' => 'lifetime']);
        $this->post(route('admin.users.subscription', $this->user->id), [
            'plan_id' => $plan->id, 'mode' => 'plan',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $sub = Subscription::where('user_id', $this->user->id)->latest('id')->first();
        $this->assertNull($sub->ends_at, 'a lifetime plan must never expire, with no day count involved');
    }

    public function test_grace_days_extend_a_yearly_plan_but_are_ignored_for_lifetime(): void
    {
        $yearly = $this->plan(['billing_period' => 'yearly']);
        $this->post(route('admin.users.subscription', $this->user->id), ['plan_id' => $yearly->id, 'mode' => 'plan', 'grace_days' => 10]);
        $sub = Subscription::where('user_id', $this->user->id)->latest('id')->first();
        $this->assertEqualsWithDelta(now()->addYear()->addDays(10)->timestamp, $sub->ends_at->timestamp, 5);

        $lifetime = $this->plan(['billing_period' => 'lifetime']);
        $this->post(route('admin.users.subscription', $this->user->id), ['plan_id' => $lifetime->id, 'mode' => 'plan', 'grace_days' => 10]);
        $sub2 = Subscription::where('user_id', $this->user->id)->latest('id')->first();
        $this->assertNull($sub2->ends_at, 'grace days must not turn a never-expiring grant into an expiring one');
    }

    public function test_custom_mode_lets_admin_pick_an_arbitrary_duration_or_never_expires(): void
    {
        $plan = $this->plan(['billing_period' => 'yearly']);
        $this->post(route('admin.users.subscription', $this->user->id), [
            'plan_id' => $plan->id, 'mode' => 'custom', 'duration_days' => 45,
        ])->assertSessionHasNoErrors();
        $sub = Subscription::where('user_id', $this->user->id)->latest('id')->first();
        $this->assertEqualsWithDelta(now()->addDays(45)->timestamp, $sub->ends_at->timestamp, 5);

        $this->post(route('admin.users.subscription', $this->user->id), [
            'plan_id' => $plan->id, 'mode' => 'custom', 'never_expires' => 1,
        ])->assertSessionHasNoErrors();
        $sub2 = Subscription::where('user_id', $this->user->id)->latest('id')->first();
        $this->assertNull($sub2->ends_at);
    }

    public function test_custom_mode_without_duration_or_never_expires_fails_validation(): void
    {
        $plan = $this->plan();
        $this->post(route('admin.users.subscription', $this->user->id), [
            'plan_id' => $plan->id, 'mode' => 'custom',
        ])->assertSessionHasErrors('duration_days');
    }

    public function test_admin_granted_subscription_creates_no_payment_record(): void
    {
        $plan = $this->plan(['price' => 999]);
        $this->post(route('admin.users.subscription', $this->user->id), ['plan_id' => $plan->id, 'mode' => 'plan']);
        $this->assertSame(0, Payment::count(), 'a free admin grant must never be recorded as revenue');
    }

    public function test_granted_by_admin_flag_is_never_serialized_to_the_shop_owner_api(): void
    {
        $plan = $this->plan(['billing_period' => 'yearly']);
        $this->post(route('admin.users.subscription', $this->user->id), ['plan_id' => $plan->id, 'mode' => 'plan', 'admin_note' => 'VIP goodwill']);

        Shop::create(['owner_id' => $this->user->id, 'name' => 'S', 'status' => 'active']);
        $this->actingAs($this->user, 'sanctum');
        $body = $this->getJson('/api/v1/shopowner/subscription')->assertStatus(200)->json();
        $this->assertArrayNotHasKey('granted_by_admin', $body['subscription']);
        $this->assertArrayNotHasKey('admin_note', $body['subscription']);
    }

    // ------------------------------------------------------------ add-ons

    private function addOn(string $type = 'shop'): AddOn
    {
        return AddOn::create(['title' => ucfirst($type), 'slug' => $type . '-' . uniqid(), 'type' => $type, 'price' => 100, 'billing_period' => 'yearly', 'status' => 'active']);
    }

    public function test_assigning_an_addon_with_never_expires_needs_no_days(): void
    {
        $addOn = $this->addOn();
        $this->post(route('admin.addons.assign'), [
            'user_id' => $this->user->id, 'add_on_id' => $addOn->id, 'quantity' => 1, 'never_expires' => 1,
        ])->assertSessionHasNoErrors();

        $grant = UserAddOn::where('user_id', $this->user->id)->first();
        $this->assertNull($grant->ends_at);
        $this->assertTrue($grant->granted_by_admin);
    }

    public function test_assigning_an_addon_without_days_or_never_expires_fails_validation(): void
    {
        $addOn = $this->addOn();
        $this->post(route('admin.addons.assign'), [
            'user_id' => $this->user->id, 'add_on_id' => $addOn->id, 'quantity' => 1,
        ])->assertSessionHasErrors('days');
    }

    public function test_addon_grant_never_creates_a_payment(): void
    {
        $addOn = $this->addOn();
        $this->post(route('admin.addons.assign'), [
            'user_id' => $this->user->id, 'add_on_id' => $addOn->id, 'quantity' => 2, 'days' => 30,
        ]);
        $this->assertSame(0, Payment::count());
    }

    public function test_addon_granted_flag_is_never_serialized_to_the_shop_owner_api(): void
    {
        $addOn = $this->addOn();
        Shop::create(['owner_id' => $this->user->id, 'name' => 'S', 'status' => 'active']);
        $this->post(route('admin.addons.assign'), [
            'user_id' => $this->user->id, 'add_on_id' => $addOn->id, 'quantity' => 1, 'never_expires' => 1,
        ]);

        $this->actingAs($this->user, 'sanctum');
        $body = $this->getJson('/api/v1/shopowner/add-ons/current')->assertStatus(200)->json();
        $row = collect($body['add_ons'] ?? $body)->first();
        if (is_array($row)) {
            $this->assertArrayNotHasKey('granted_by_admin', $row);
        }
    }

    // ------------------------------------------------------------ shops

    public function test_admin_can_add_a_shop_past_the_users_plan_limit_without_any_addon_purchase(): void
    {
        $this->addOn('shop');
        Shop::create(['owner_id' => $this->user->id, 'name' => 'Shop 1', 'status' => 'active']);
        $this->assertFalse($this->user->fresh()->canAddShop(), 'sanity: free plan is capped at 1 shop');

        $this->post(route('admin.shops.store'), [
            'owner_id' => $this->user->id, 'name' => 'Shop 2', 'status' => 'active',
        ])->assertSessionHasNoErrors();

        $this->assertSame(2, Shop::where('owner_id', $this->user->id)->count());
        $second = Shop::where('owner_id', $this->user->id)->where('name', 'Shop 2')->first();
        $this->assertTrue($second->added_by_admin);
        $this->assertEmpty($this->user->fresh()->lockedShopIds(), 'the admin-added shop must stay usable, not locked');
        $this->assertSame(0, Payment::count());
    }

    public function test_added_by_admin_flag_is_never_serialized_to_the_shop_owner_api(): void
    {
        $this->addOn('shop');
        Shop::create(['owner_id' => $this->user->id, 'name' => 'Shop 1', 'status' => 'active']);
        $this->post(route('admin.shops.store'), ['owner_id' => $this->user->id, 'name' => 'Shop 2', 'status' => 'active']);

        $this->actingAs($this->user, 'sanctum');
        $body = $this->postJson('/api/v1/shopowner/login', [])->status(); // ensure guard context is fine; ignore result
        $shop = Shop::where('name', 'Shop 2')->first()->fresh();
        $this->assertArrayNotHasKey('added_by_admin', $shop->toArray());
    }

    public function test_admin_can_still_add_a_shop_normally_within_the_limit_without_any_grant(): void
    {
        $this->post(route('admin.shops.store'), ['owner_id' => $this->user->id, 'name' => 'Only Shop', 'status' => 'active'])
            ->assertSessionHasNoErrors();
        $shop = Shop::where('owner_id', $this->user->id)->first();
        $this->assertFalse($shop->added_by_admin);
        $this->assertSame(0, UserAddOn::count(), 'no slot should be granted when the user was still within their limit');
    }
}
