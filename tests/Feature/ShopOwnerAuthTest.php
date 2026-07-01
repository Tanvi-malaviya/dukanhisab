<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use App\Models\User;
use Carbon\Carbon;

class ShopOwnerAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    /**
     * Test shopowner registration.
     */
    public function test_shopowner_registration()
    {
        $response = $this->postJson('/api/v1/shopowner/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'mobile' => '9876543210',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
                 ->assertJsonStructure(['message', 'email', 'dev_otp']);

        $this->assertDatabaseHas('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'email_verified_at' => null,
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user->otp_code);
    }

    /**
     * Test shopowner OTP verification.
     */
    public function test_shopowner_otp_verification()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
            'otp_code' => '123456',
            'otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/v1/shopowner/verify-otp', [
            'email' => $user->email,
            'otp_code' => '123456',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'token', 'user']);

        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertNull($user->otp_code);
    }

    /**
     * Test shopowner login flow.
     */
    public function test_shopowner_login_flow()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => Carbon::now(),
        ]);

        $response = $this->postJson('/api/v1/shopowner/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'token', 'user']);
    }

    /**
     * Test shopowner unverified login redirection/error.
     */
    public function test_shopowner_unverified_login_error()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => null,
        ]);

        $response = $this->postJson('/api/v1/shopowner/login', [
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403)
                 ->assertJson([
                     'email_unverified' => true
                 ]);
    }

    /**
     * Test resending verification OTP.
     */
    public function test_shopowner_resend_otp()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'email_verified_at' => null,
            'otp_code' => '111111',
        ]);

        $response = $this->postJson('/api/v1/shopowner/resend-otp', [
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'dev_otp']);

        $user->refresh();
        $this->assertNotEquals('111111', $user->otp_code);
    }

    /**
     * Test forgot password.
     */
    public function test_shopowner_forgot_password()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
        ]);

        $response = $this->postJson('/api/v1/shopowner/forgot-password', [
            'email' => 'john@example.com',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'dev_otp']);

        $user->refresh();
        $this->assertNotNull($user->otp_code);
    }

    /**
     * Test resetting password using OTP.
     */
    public function test_shopowner_reset_password()
    {
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'otp_code' => '654321',
            'otp_expires_at' => Carbon::now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/v1/shopowner/reset-password', [
            'email' => 'john@example.com',
            'otp_code' => '654321',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['message']);

        $user->refresh();
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpassword123', $user->password));
    }

    /**
     * Test shop setup flow.
     */
    public function test_shop_setup_flow()
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $user = User::factory()->create([
            'name' => 'Original Name',
            'mobile' => '1234567890',
        ]);

        $logo = \Illuminate\Http\UploadedFile::fake()->create('logo.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/v1/shopowner/shop-setup', [
            'name' => 'My Great Shop',
            'owner_name' => 'Updated Name',
            'mobile' => '9876543210',
            'gst_number' => 'GST123456',
            'logo' => $logo,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'user', 'has_shop', 'shop']);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'mobile' => '9876543210',
        ]);

        $this->assertDatabaseHas('shops', [
            'owner_id' => $user->id,
            'name' => 'My Great Shop',
            'mobile' => '9876543210',
            'gst_number' => 'GST123456',
        ]);

        $shop = \App\Models\Shop::where('owner_id', $user->id)->first();
        $this->assertNotNull($shop->logo);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($shop->logo);
    }

    /**
     * Test return sale flow.
     */
    public function test_return_sale_flow()
    {
        $user = User::factory()->create();
        $shop = \App\Models\Shop::create([
            'owner_id' => $user->id,
            'name' => 'Test Shop',
            'mobile' => '1234567890',
        ]);

        $product = \App\Models\Product::create([
            'shop_id' => $shop->id,
            'name' => 'Pencil',
            'purchase_price' => 5.00,
            'selling_price' => 10.00,
            'stock' => 10,
            'low_stock_threshold' => 3,
        ]);

        $sale = \App\Models\Sale::create([
            'shop_id' => $shop->id,
            'sale_number' => 'SAL-123456',
            'subtotal' => 10.00,
            'grand_total' => 10.00,
            'payment_type' => 'Cash',
            'status' => 'Completed',
            'sale_date' => Carbon::now(),
        ]);

        \App\Models\SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'selling_price' => 10.00,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Shop-ID', $shop->id)
            ->postJson("/api/v1/sales/{$sale->id}/return");

        $response->assertStatus(200);
        $this->assertEquals('Returned', $response->json('status'));

        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'status' => 'Returned',
        ]);

        $product->refresh();
        $this->assertEquals(11, $product->stock); // restored stock
    }

    /**
     * Test partial return sale flow.
     */
    public function test_partial_return_sale_flow()
    {
        $user = User::factory()->create();
        $shop = \App\Models\Shop::create([
            'owner_id' => $user->id,
            'name' => 'Test Shop',
            'mobile' => '1234567890',
        ]);

        $product = \App\Models\Product::create([
            'shop_id' => $shop->id,
            'name' => 'Pencil',
            'purchase_price' => 5.00,
            'selling_price' => 10.00,
            'stock' => 10,
            'low_stock_threshold' => 3,
        ]);

        $sale = \App\Models\Sale::create([
            'shop_id' => $shop->id,
            'sale_number' => 'SAL-123457',
            'subtotal' => 30.00,
            'grand_total' => 30.00,
            'payment_type' => 'Cash',
            'status' => 'Completed',
            'sale_date' => Carbon::now(),
        ]);

        \App\Models\SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'selling_price' => 10.00,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Shop-ID', $shop->id)
            ->postJson("/api/v1/sales/{$sale->id}/return", [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                    ]
                ]
            ]);

        $response->assertStatus(200);
        $this->assertEquals('Partially Returned', $response->json('status'));
        $this->assertEquals(10.00, $response->json('grand_total'));

        $this->assertDatabaseHas('sales', [
            'id' => $sale->id,
            'status' => 'Partially Returned',
            'grand_total' => 10.00,
        ]);

        $this->assertDatabaseHas('sale_items', [
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $product->refresh();
        $this->assertEquals(12, $product->stock); // restored 2 pencils to stock (10 + 2)
    }

    /**
     * Test return purchase flow.
     */
    public function test_return_purchase_flow()
    {
        $user = User::factory()->create();
        $shop = \App\Models\Shop::create([
            'owner_id' => $user->id,
            'name' => 'Test Shop',
            'mobile' => '1234567890',
        ]);

        $product = \App\Models\Product::create([
            'shop_id' => $shop->id,
            'name' => 'Notebook',
            'purchase_price' => 20.00,
            'selling_price' => 30.00,
            'stock' => 10,
            'low_stock_threshold' => 3,
        ]);

        $purchase = \App\Models\Purchase::create([
            'shop_id' => $shop->id,
            'purchase_number' => 'PUR-123456',
            'total_amount' => 40.00,
            'payment_type' => 'Cash',
            'status' => 'Completed',
            'purchase_date' => Carbon::now(),
        ]);

        \App\Models\PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'purchase_price' => 20.00,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Shop-ID', $shop->id)
            ->postJson("/api/v1/purchases/{$purchase->id}/return");

        $response->assertStatus(200);
        $this->assertEquals('Returned', $response->json('status'));

        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'status' => 'Returned',
        ]);

        $product->refresh();
        $this->assertEquals(8, $product->stock); // decremented stock (10 - 2)
    }

    /**
     * Test partial return purchase flow.
     */
    public function test_partial_return_purchase_flow()
    {
        $user = User::factory()->create();
        $shop = \App\Models\Shop::create([
            'owner_id' => $user->id,
            'name' => 'Test Shop',
            'mobile' => '1234567890',
        ]);

        $product = \App\Models\Product::create([
            'shop_id' => $shop->id,
            'name' => 'Notebook',
            'purchase_price' => 20.00,
            'selling_price' => 30.00,
            'stock' => 10,
            'low_stock_threshold' => 3,
        ]);

        $purchase = \App\Models\Purchase::create([
            'shop_id' => $shop->id,
            'purchase_number' => 'PUR-123457',
            'total_amount' => 60.00,
            'payment_type' => 'Cash',
            'status' => 'Completed',
            'purchase_date' => Carbon::now(),
        ]);

        \App\Models\PurchaseItem::create([
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'purchase_price' => 20.00,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Shop-ID', $shop->id)
            ->postJson("/api/v1/purchases/{$purchase->id}/return", [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                    ]
                ]
            ]);

        $response->assertStatus(200);
        $this->assertEquals('Partially Returned', $response->json('status'));
        $this->assertEquals(20.00, $response->json('total_amount'));

        $this->assertDatabaseHas('purchases', [
            'id' => $purchase->id,
            'status' => 'Partially Returned',
            'total_amount' => 20.00,
        ]);

        $this->assertDatabaseHas('purchase_items', [
            'purchase_id' => $purchase->id,
            'product_id' => $product->id,
            'quantity' => 3,
            'returned_quantity' => 2,
        ]);

        $product->refresh();
        $this->assertEquals(8, $product->stock); // decremented stock by returned quantity (10 - 2)
    }
}
