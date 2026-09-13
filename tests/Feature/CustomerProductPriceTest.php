<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Models\CustomerProductPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class CustomerProductPriceTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_product_pricing_flow(): void
    {
        // 1. Setup Shop, User, Customer, Products
        $user = User::factory()->create();

        $shop = Shop::create([
            'owner_id' => $user->id,
            'name' => 'Price Test Shop ' . Str::random(5),
            'currency' => 'INR',
            'is_active' => true,
        ]);

        $user->update(['shop_id' => $shop->id]);

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => 'VIP Customer',
            'mobile' => '9876543210',
        ]);

        $product1 = Product::create([
            'shop_id' => $shop->id,
            'name' => 'Product Alpha',
            'selling_price' => 100.00,
            'purchase_price' => 60.00,
            'stock' => 50,
        ]);

        $product2 = Product::create([
            'shop_id' => $shop->id,
            'name' => 'Product Beta',
            'selling_price' => 200.00,
            'purchase_price' => 120.00,
            'stock' => 30,
        ]);

        $headers = ['X-Shop-ID' => $shop->id];

        // 2. Fetch prices when none configured
        $respInit = $this->actingAs($user, 'sanctum')->withHeaders($headers)
            ->getJson("/api/v1/customers/{$customer->id}/product-prices");

        $respInit->assertStatus(200);
        $respInit->assertJsonPath('products.0.name', 'Product Alpha');
        $respInit->assertJsonPath('products.0.default_price', 100);
        $respInit->assertJsonPath('products.0.custom_price', null);

        // 3. Set custom price for Product Alpha (₹85.50)
        $respSave = $this->actingAs($user, 'sanctum')->withHeaders($headers)
            ->postJson("/api/v1/customers/{$customer->id}/product-prices", [
                'prices' => [
                    [
                        'product_id' => $product1->id,
                        'custom_price' => 85.50,
                    ],
                    [
                        'product_id' => $product2->id,
                        'custom_price' => null, // no custom price
                    ],
                ],
            ]);

        $respSave->assertStatus(200);

        // 4. Verify in Database
        $this->assertDatabaseHas('customer_product_prices', [
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'product_id' => $product1->id,
            'custom_price' => 85.50,
        ]);

        $this->assertDatabaseMissing('customer_product_prices', [
            'customer_id' => $customer->id,
            'product_id' => $product2->id,
        ]);

        // 5. Fetch updated prices
        $respAfter = $this->actingAs($user, 'sanctum')->withHeaders($headers)
            ->getJson("/api/v1/customers/{$customer->id}/product-prices");

        $respAfter->assertStatus(200);
        $productsData = collect($respAfter->json('products'))->keyBy('product_id');
        $this->assertEquals(85.50, $productsData[$product1->id]['custom_price']);
        $this->assertTrue($productsData[$product1->id]['has_custom']);
        $this->assertNull($productsData[$product2->id]['custom_price']);
        $this->assertFalse($productsData[$product2->id]['has_custom']);

        // 6. Reset / Clear custom price
        $respReset = $this->actingAs($user, 'sanctum')->withHeaders($headers)
            ->postJson("/api/v1/customers/{$customer->id}/product-prices", [
                'prices' => [
                    [
                        'product_id' => $product1->id,
                        'custom_price' => null, // cleared
                    ],
                ],
            ]);

        $respReset->assertStatus(200);

        $this->assertDatabaseMissing('customer_product_prices', [
            'customer_id' => $customer->id,
            'product_id' => $product1->id,
        ]);
    }
}
