<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Shop;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Login/session and plan-feature rules shared by the web panel and the app.
 */
class LoginSessionParityTest extends TestCase
{
    use RefreshDatabase;

    private function plan(array $features): SubscriptionPlan
    {
        return SubscriptionPlan::create([
            'name' => 'P', 'slug' => 'p' . uniqid(), 'billing_period' => 'yearly', 'price' => 100,
            'status' => 'active', 'features' => $features,
        ]);
    }

    private function owner(array $features = ['max_devices' => 5, 'backup' => false]): User
    {
        $user = User::factory()->create([
            'password' => Hash::make('secret123'),
            'email_verified_at' => now(),
            'status' => 'active',
            'active_plan_id' => $this->plan($features)->id,
        ]);
        Shop::create(['owner_id' => $user->id, 'name' => 'S', 'status' => 'active']);

        return $user;
    }

    private function login(User $user): string
    {
        return $this->postJson('/api/v1/shopowner/login', ['email' => $user->email, 'password' => 'secret123'])
            ->assertStatus(200)->json('token');
    }

    private function asToken(string $token, ?int $shopId = null): self
    {
        $this->app['auth']->forgetGuards();

        return $this->withHeaders(array_filter(['Authorization' => "Bearer {$token}", 'X-Shop-ID' => $shopId]));
    }

    public function test_the_old_unchecked_auth_routes_are_gone(): void
    {
        $user = $this->owner();
        $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'secret123'])->assertStatus(404);
        $this->postJson('/api/v1/auth/register', ['name' => 'X', 'email' => 'x@example.com', 'password' => 'secret123'])->assertStatus(404);
    }

    public function test_suspending_from_admin_ends_every_session(): void
    {
        $user = $this->owner();
        $token = $this->login($user);
        $this->asToken($token)->getJson('/api/v1/shopowner/profile')->assertStatus(200);

        $admin = Admin::create(['name' => 'A', 'email' => 'a@test.com', 'password' => 'secret123', 'role' => 'superadmin', 'status' => 'active']);
        $this->actingAs($admin, 'admin')->post(route('admin.users.update', $user->id), ['status' => 'suspended'] + $user->only('name', 'email'));

        $this->assertSame('suspended', $user->fresh()->status);
        $this->assertSame(0, $user->tokens()->count());
        $this->asToken($token)->getJson('/api/v1/shopowner/profile')->assertStatus(401);
    }

    public function test_a_suspended_account_is_refused_with_a_clear_reason_and_its_token_revoked(): void
    {
        $user = $this->owner();
        $token = $this->login($user);
        // Status changed without going through the model (e.g. directly in the database).
        User::where('id', $user->id)->update(['status' => 'suspended']);

        $this->asToken($token, $user->shops()->first()->id)->getJson('/api/v1/dashboard')
            ->assertStatus(403)->assertJsonPath('error', 'account_suspended');
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_device_limit_from_the_plan_signs_out_the_oldest_session(): void
    {
        $user = $this->owner(['max_devices' => 1, 'backup' => false]);
        $web = $this->login($user);
        $app = $this->login($user);

        $this->asToken($web)->getJson('/api/v1/shopowner/profile')->assertStatus(401);
        $this->asToken($app)->getJson('/api/v1/shopowner/profile')->assertStatus(200);
    }

    public function test_backup_follows_the_plans_backup_feature_not_its_name(): void
    {
        $without = $this->owner(['max_devices' => 5, 'backup' => false]);
        $this->asToken($this->login($without), $without->shops()->first()->id)
            ->getJson('/api/v1/backup/export')->assertStatus(403)->assertJsonPath('error', 'plan_feature_required');

        $with = $this->owner(['max_devices' => 5, 'backup' => true]);
        $this->asToken($this->login($with), $with->shops()->first()->id)
            ->getJson('/api/v1/backup/export')->assertStatus(200);
    }
}
