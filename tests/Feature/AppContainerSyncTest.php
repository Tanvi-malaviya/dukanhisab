<?php

namespace Tests\Feature;

use App\Models\ContainerType;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** The exact payloads the mobile app sends through /sync/batch for containers. */
class AppContainerSyncTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;
    private ContainerType $tub;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->shop = Shop::create(['owner_id' => $user->id, 'name' => 'Ice', 'status' => 'active']);
        $this->shop->forceFill(['features' => ['containers']])->save();
        $this->tub = ContainerType::create(['shop_id' => $this->shop->id, 'name' => 'Tub', 'deposit_amount' => 200]);
        $this->customer = Customer::create(['shop_id' => $this->shop->id, 'name' => 'Ramesh', 'due_amount' => 0]);
        Sanctum::actingAs($user);
    }

    private function batch(array $ops)
    {
        return $this->withHeaders(['X-Shop-ID' => $this->shop->id])->postJson('/api/v1/sync/batch', ['operations' => $ops]);
    }

    public function test_product_create_and_update_carry_the_container_link(): void
    {
        $r = $this->batch([[
            'resource' => 'products', 'action' => 'create', 'op_id' => 'p1', 'client_uuid' => 'uuid-p1',
            'data' => ['name' => 'Vanilla 5L', 'selling_price' => '500.00', 'purchase_price' => '300.00', 'barcode' => 'BC-1', 'stock' => 10, 'low_stock_threshold' => 5,
                'available_for_sale' => true, 'available_for_purchase' => true, 'container_type_id' => $this->tub->id, 'containers_per_unit' => 1],
        ]])->assertOk();
        $this->assertTrue($r->json('results.0.success'), json_encode($r->json('results.0.error')));
        $product = Product::first();
        $this->assertSame($this->tub->id, (int) $product->container_type_id);

        // App update always sends both fields; null clears the link.
        $r = $this->batch([[
            'resource' => 'products', 'action' => 'update', 'op_id' => 'p2', 'id' => $product->id,
            'data' => ['name' => 'Vanilla 5L', 'selling_price' => '500.00', 'purchase_price' => '300.00', 'barcode' => 'BC-1', 'low_stock_threshold' => 5,
                'available_for_sale' => true, 'available_for_purchase' => true, 'container_type_id' => null, 'containers_per_unit' => 1],
        ]])->assertOk();
        $this->assertTrue($r->json('results.0.success'), json_encode($r->json('results.0.error')));
        $this->assertNull($product->fresh()->container_type_id);
    }

    public function test_sale_with_containers_posts_deposit_and_lots(): void
    {
        $product = Product::create(['shop_id' => $this->shop->id, 'name' => 'Vanilla 5L', 'stock' => 10, 'selling_price' => 500, 'purchase_price' => 300,
            'container_type_id' => $this->tub->id, 'containers_per_unit' => 1]);

        $r = $this->batch([[
            'resource' => 'sales', 'action' => 'create', 'op_id' => 's1', 'client_uuid' => 'uuid-s1',
            'data' => [
                'customer_id' => $this->customer->id, 'subtotal' => '1000.00', 'discount' => '0.00', 'grand_total' => '1000.00', 'payment_type' => 'Cash',
                'sale_date' => now()->toIso8601String(),
                'items' => [['product_id' => $product->id, 'quantity' => 2, 'selling_price' => '500.00', 'discount' => '0.00']],
                'containers' => ['given' => [['container_type_id' => $this->tub->id, 'quantity' => 2]], 'returned' => [], 'collect_deposit' => true, 'settlement_method' => 'cash'],
            ],
        ]])->assertOk();
        $this->assertTrue($r->json('results.0.success'), json_encode($r->json('results.0.error')));

        $detail = $this->withHeaders(['X-Shop-ID' => $this->shop->id])->getJson('/api/v1/containers/customers/' . $this->customer->id)->assertOk();
        $this->assertSame(2, $detail->json('holdings.0.pending'));
        $this->assertEquals(400, $detail->json('deposit_held'));
        // The deposit is not sales income.
        $this->assertEquals(1000, \App\Models\Sale::first()->grand_total);
    }

    public function test_credit_sale_deposit_is_still_collected_in_cash(): void
    {
        $r = $this->batch([[
            'resource' => 'sales', 'action' => 'create', 'op_id' => 's2',
            'data' => [
                'customer_id' => $this->customer->id, 'subtotal' => '500.00', 'discount' => '0.00', 'grand_total' => '500.00', 'payment_type' => 'Credit',
                'items' => [['product_id' => Product::create(['shop_id' => $this->shop->id, 'name' => 'X', 'stock' => 5, 'selling_price' => 500, 'purchase_price' => 1])->id, 'quantity' => 1, 'selling_price' => '500.00', 'discount' => '0.00']],
                'containers' => ['given' => [['container_type_id' => $this->tub->id, 'quantity' => 1]], 'returned' => [], 'collect_deposit' => true, 'settlement_method' => 'cash'],
            ],
        ]])->assertOk();
        $this->assertTrue($r->json('results.0.success'), json_encode($r->json('results.0.error')));
        $this->assertEquals(500, $this->customer->fresh()->due_amount, 'deposit must not be added to the udhar');
    }
}
