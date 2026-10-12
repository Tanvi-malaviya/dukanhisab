<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_delete_account_via_api_with_valid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'delete_me@example.com',
            'password' => Hash::make('secret1234'),
            'status' => 'active',
        ]);

        $shop = Shop::create([
            'owner_id' => $user->id,
            'name' => 'My Test Shop',
            'mobile' => '9876543210',
            'currency' => 'INR',
            'status' => 'active',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/shopowner/delete-account', [
                'password' => 'secret1234',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
            ]);

        // Account is marked as deleted so admin can view and recover
        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => 'deleted']);
        $this->assertDatabaseHas('shops', ['id' => $shop->id, 'status' => 'inactive']);
    }

    public function test_user_cannot_delete_account_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'email' => 'keep_me@example.com',
            'password' => Hash::make('secret1234'),
            'status' => 'active',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/shopowner/delete-account', [
                'password' => 'wrong_password',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => 'active']);
    }

    public function test_delete_account_via_http_delete_method(): void
    {
        $user = User::factory()->create([
            'email' => 'delete_http@example.com',
            'password' => Hash::make('secret1234'),
            'status' => 'active',
        ]);

        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->deleteJson('/api/v1/account', [
                'password' => 'secret1234',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => 'deleted']);
    }

    public function test_public_delete_account_page_renders(): void
    {
        $response = $this->get('/delete-account');

        $response->assertOk()
            ->assertSee('Request Account & Data Deletion', false)
            ->assertSee('What Happens When You Delete Your Account?', false)
            ->assertSee('Direct Web Account Deletion', false);
    }

    public function test_user_can_delete_account_via_public_web_form(): void
    {
        $user = User::factory()->create([
            'email' => 'web_delete@example.com',
            'password' => Hash::make('mypassword123'),
            'status' => 'active',
        ]);

        $response = $this->post('/delete-account', [
            'identity' => 'web_delete@example.com',
            'password' => 'mypassword123',
            'confirm_understanding' => '1',
            'reason' => 'closing_business',
        ]);

        $response->assertSessionHas('success_deleted');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => 'deleted']);
    }

    public function test_deleted_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'deleted_user@example.com',
            'password' => Hash::make('secret1234'),
            'status' => 'deleted',
            'email_verified_at' => now(),
        ]);

        $response = $this->postJson('/api/v1/shopowner/login', [
            'email' => 'deleted_user@example.com',
            'password' => 'secret1234',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'Your account has been deleted. Please contact the administrator to restore your account.',
            ]);
    }

    public function test_admin_can_view_and_recover_deleted_account(): void
    {
        $admin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin_test@example.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'email' => 'recoverable@example.com',
            'password' => Hash::make('secret1234'),
            'status' => 'deleted',
        ]);

        $shop = Shop::create([
            'owner_id' => $user->id,
            'name' => 'Recoverable Shop',
            'mobile' => '9876543210',
            'currency' => 'INR',
            'status' => 'inactive',
        ]);

        // Admin checks users index filtered by status=deleted
        $response = $this->actingAs($admin, 'admin')->get('/admin/users?status=deleted');
        $response->assertOk()
            ->assertSee('recoverable@example.com')
            ->assertSee('Recover');

        // Admin recovers the user
        $recoverResponse = $this->actingAs($admin, 'admin')->post("/admin/users/{$user->id}/recover");
        $recoverResponse->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'status' => 'active']);
        $this->assertDatabaseHas('shops', ['id' => $shop->id, 'status' => 'active']);
    }

    public function test_admin_can_force_delete_purging_from_database(): void
    {
        $admin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin_purge@example.com',
            'password' => Hash::make('password123'),
            'role' => 'super_admin',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'email' => 'purge_me@example.com',
            'password' => Hash::make('secret1234'),
            'status' => 'deleted',
        ]);

        $shop = Shop::create([
            'owner_id' => $user->id,
            'name' => 'Purge Shop',
            'mobile' => '9876543210',
            'currency' => 'INR',
            'status' => 'inactive',
        ]);

        $purgeResponse = $this->actingAs($admin, 'admin')->delete("/admin/users/{$user->id}/force-delete");
        $purgeResponse->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseMissing('shops', ['id' => $shop->id]);
    }
}
