<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class DuplicateBarcodeTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_barcode_in_same_shop_is_blocked_on_create(): void
    {
        $user = User::factory()->create();
        $shop = Shop::create([
            'owner_id' => $user->id,
            'name' => 'Barcode Test Shop ' . Str::random(5),
            'currency' => 'INR',
            'is_active' => true,
        ]);
        $user->update(['shop_id' => $shop->id]);

        // 1. Create first product with barcode '8901234567890'
        Product::create([
            'shop_id' => $shop->id,
            'name' => 'First Product',
            'selling_price' => 100.00,
            'purchase_price' => 80.00,
            'barcode' => '8901234567890',
            'stock' => 10,
        ]);

        // 2. Attempt to create another product with the same barcode in the same shop
        $response = $this->actingAs($user)
            ->withHeaders(['X-Shop-ID' => $shop->id])
            ->postJson('/api/v1/products', [
                'name' => 'Second Product with Same Barcode',
                'selling_price' => 150.00,
                'barcode' => '8901234567890',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['barcode'])
            ->assertJsonFragment([
                'barcode' => ['This barcode is already assigned to another product in this shop.']
            ]);
    }

    public function test_same_barcode_in_different_shops_is_allowed(): void
    {
        $user1 = User::factory()->create();
        $shop1 = Shop::create([
            'owner_id' => $user1->id,
            'name' => 'Shop 1 ' . Str::random(5),
            'currency' => 'INR',
            'is_active' => true,
        ]);
        $user1->update(['shop_id' => $shop1->id]);

        $user2 = User::factory()->create();
        $shop2 = Shop::create([
            'owner_id' => $user2->id,
            'name' => 'Shop 2 ' . Str::random(5),
            'currency' => 'INR',
            'is_active' => true,
        ]);
        $user2->update(['shop_id' => $shop2->id]);

        // Product in Shop 1
        Product::create([
            'shop_id' => $shop1->id,
            'name' => 'Shop 1 Product',
            'selling_price' => 50.00,
            'barcode' => 'COMMON-BARCODE-123',
        ]);

        // Attempt to create product in Shop 2 with same barcode
        $response = $this->actingAs($user2)
            ->withHeaders(['X-Shop-ID' => $shop2->id])
            ->postJson('/api/v1/products', [
                'name' => 'Shop 2 Product with Same Barcode',
                'selling_price' => 60.00,
                'barcode' => 'COMMON-BARCODE-123',
            ]);

        $response->assertStatus(201);
    }

    public function test_updating_product_to_duplicate_barcode_is_blocked(): void
    {
        $user = User::factory()->create();
        $shop = Shop::create([
            'owner_id' => $user->id,
            'name' => 'Shop ' . Str::random(5),
            'currency' => 'INR',
            'is_active' => true,
        ]);
        $user->update(['shop_id' => $shop->id]);

        $prod1 = Product::create([
            'shop_id' => $shop->id,
            'name' => 'Prod 1',
            'selling_price' => 50.00,
            'barcode' => 'BARCODE-AAA',
        ]);

        $prod2 = Product::create([
            'shop_id' => $shop->id,
            'name' => 'Prod 2',
            'selling_price' => 70.00,
            'barcode' => 'BARCODE-BBB',
        ]);

        // Update prod2 to have prod1's barcode
        $response = $this->actingAs($user)
            ->withHeaders(['X-Shop-ID' => $shop->id])
            ->putJson('/api/v1/products/' . $prod2->id, [
                'barcode' => 'BARCODE-AAA',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['barcode']);

        // Update prod2 keeping its own barcode should succeed
        $responseSuccess = $this->actingAs($user)
            ->withHeaders(['X-Shop-ID' => $shop->id])
            ->putJson('/api/v1/products/' . $prod2->id, [
                'name' => 'Prod 2 Updated Name',
                'barcode' => 'BARCODE-BBB',
            ]);

        $responseSuccess->assertStatus(200);
    }
}
