<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\User;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\SupplierProductPrice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class SupplierProductPriceTest extends TestCase
{
    use RefreshDatabase;

    public function test_supplier_product_pricing_flow(): void
    {
        // 1. Setup Shop, User, Supplier, Products
        $user = User::factory()->create();

        $shop = Shop::create([
            'owner_id' => $user->id,
            'name' => 'Supplier Price Test Shop ' . Str::random(5),
            'currency' => 'INR',
            'is_active' => true,
        ]);

        $user->update(['shop_id' => $shop->id]);

        $supplier = Supplier::create([
            'shop_id' => $shop->id,
            'name' => 'Test Wholesaler',
            'mobile' => '9876543211',
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
            ->getJson("/api/v1/suppliers/{$supplier->id}/product-prices");

        $respInit->assertStatus(200);
        $respInit->assertJsonPath('products.0.name', 'Product Alpha');
        $respInit->assertJsonPath('products.0.default_price', 60);
        $respInit->assertJsonPath('products.0.custom_price', null);

        // 3. Set custom purchase price for Product Alpha (₹52.00)
        $respSave = $this->actingAs($user, 'sanctum')->withHeaders($headers)
            ->postJson("/api/v1/suppliers/{$supplier->id}/product-prices", [
                'prices' => [
                    [
                        'product_id' => $product1->id,
                        'custom_price' => 52.00,
                    ],
                    [
                        'product_id' => $product2->id,
                        'custom_price' => null, // no custom price
                    ],
                ],
            ]);

        $respSave->assertStatus(200);

        // 4. Verify in Database
        $this->assertDatabaseHas('supplier_product_prices', [
            'shop_id' => $shop->id,
            'supplier_id' => $supplier->id,
            'product_id' => $product1->id,
            'custom_price' => 52.00,
        ]);

        $this->assertDatabaseMissing('supplier_product_prices', [
            'supplier_id' => $supplier->id,
            'product_id' => $product2->id,
        ]);

        // 5. Fetch updated prices
        $respAfter = $this->actingAs($user, 'sanctum')->withHeaders($headers)
            ->getJson("/api/v1/suppliers/{$supplier->id}/product-prices");

        $respAfter->assertStatus(200);
        $productsData = collect($respAfter->json('products'))->keyBy('product_id');

        $this->assertEquals(52.00, $productsData[$product1->id]['custom_price']);
        $this->assertTrue($productsData[$product1->id]['has_custom']);
        $this->assertNull($productsData[$product2->id]['custom_price']);
        $this->assertFalse($productsData[$product2->id]['has_custom']);

        // 6. Reset / Clear custom purchase price
        $respReset = $this->actingAs($user, 'sanctum')->withHeaders($headers)
            ->postJson("/api/v1/suppliers/{$supplier->id}/product-prices", [
                'prices' => [
                    [
                        'product_id' => $product1->id,
                        'custom_price' => null, // cleared
                    ],
                ],
            ]);

        $respReset->assertStatus(200);

        $this->assertDatabaseMissing('supplier_product_prices', [
            'supplier_id' => $supplier->id,
            'product_id' => $product1->id,
        ]);
    }
}
