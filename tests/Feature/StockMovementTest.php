<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Shop;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The server-side stock audit log that replaces each client's own local-only history: every
 * sale, purchase, return, cancel and manual adjustment must leave exactly one row behind.
 */
class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->shop = Shop::create(['owner_id' => $this->user->id, 'name' => 'S', 'status' => 'active']);
        Sanctum::actingAs($this->user);
    }

    private function h(): array
    {
        return ['X-Shop-ID' => $this->shop->id];
    }

    private function product(array $o = []): Product
    {
        return Product::create($o + ['shop_id' => $this->shop->id, 'name' => 'P', 'stock' => 20, 'selling_price' => 100, 'purchase_price' => 60]);
    }

    private function movements(Product $p): \Illuminate\Support\Collection
    {
        return StockMovement::where('product_id', $p->id)->orderBy('id')->get();
    }

    public function test_sale_logs_a_negative_movement(): void
    {
        $p = $this->product();
        $saleId = $this->withHeaders($this->h())->postJson('/api/v1/sales', [
            'subtotal' => 300, 'grand_total' => 300, 'payment_type' => 'Cash',
            'items' => [['product_id' => $p->id, 'quantity' => 3, 'selling_price' => 100]],
        ])->json('id');

        $rows = $this->movements($p);
        $this->assertCount(1, $rows);
        $this->assertSame('sale', $rows[0]->type);
        $this->assertSame(-3, $rows[0]->quantity_change);
        $this->assertSame(17, $rows[0]->resulting_stock);
        $this->assertSame('sale', $rows[0]->reference_type);
        $this->assertEquals($saleId, $rows[0]->reference_id);
        $this->assertEquals($this->user->id, $rows[0]->created_by);
    }

    public function test_purchase_logs_a_positive_movement(): void
    {
        $p = $this->product(['stock' => 0]);
        $this->withHeaders($this->h())->postJson('/api/v1/purchases', [
            'total_amount' => 500, 'payment_type' => 'Cash',
            'items' => [['product_id' => $p->id, 'quantity' => 5, 'purchase_price' => 100]],
        ])->assertStatus(201);

        $rows = $this->movements($p);
        $this->assertCount(1, $rows);
        $this->assertSame('purchase', $rows[0]->type);
        $this->assertSame(5, $rows[0]->quantity_change);
        $this->assertSame(5, $rows[0]->resulting_stock);
    }

    public function test_cancel_and_return_log_their_own_movement_types(): void
    {
        $p = $this->product();
        $saleId = $this->withHeaders($this->h())->postJson('/api/v1/sales', [
            'subtotal' => 100, 'grand_total' => 100, 'payment_type' => 'Cash',
            'items' => [['product_id' => $p->id, 'quantity' => 1, 'selling_price' => 100]],
        ])->json('id');
        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$saleId}/cancel", ['cancellation_reason' => 'test reason'])->assertStatus(200);

        $rows = $this->movements($p);
        $this->assertCount(2, $rows);
        $this->assertSame('sale_cancel', $rows[1]->type);
        $this->assertSame(1, $rows[1]->quantity_change);
        $this->assertSame('test reason', $rows[1]->note);
    }

    public function test_purchase_return_logs_a_negative_movement(): void
    {
        $p = $this->product(['stock' => 0]);
        $s = Supplier::create(['shop_id' => $this->shop->id, 'name' => 'S', 'mobile' => '9', 'due_amount' => 0]);
        $purId = $this->withHeaders($this->h())->postJson('/api/v1/purchases', [
            'supplier_id' => $s->id, 'total_amount' => 500, 'payment_type' => 'Credit',
            'items' => [['product_id' => $p->id, 'quantity' => 5, 'purchase_price' => 100]],
        ])->json('id');
        $this->withHeaders($this->h())->postJson("/api/v1/purchases/{$purId}/return", [
            'items' => [['product_id' => $p->id, 'quantity' => 2]],
        ])->assertStatus(200);

        $rows = $this->movements($p);
        $this->assertSame('purchase_return', $rows->last()->type);
        $this->assertSame(-2, $rows->last()->quantity_change);
    }

    public function test_manual_adjustment_updates_stock_and_logs_it(): void
    {
        $p = $this->product(['stock' => 10]);
        $r = $this->withHeaders($this->h())->postJson("/api/v1/products/{$p->id}/adjust-stock", [
            'quantity_change' => -3, 'note' => 'Damaged goods',
        ])->assertStatus(200);

        $this->assertSame(7, $r->json('stock'));
        $rows = $this->movements($p);
        $this->assertCount(1, $rows);
        $this->assertSame('adjustment', $rows[0]->type);
        $this->assertSame(-3, $rows[0]->quantity_change);
        $this->assertSame('Damaged goods', $rows[0]->note);
    }

    public function test_adjustment_rejects_zero_change(): void
    {
        $p = $this->product();
        $this->withHeaders($this->h())->postJson("/api/v1/products/{$p->id}/adjust-stock", ['quantity_change' => 0])->assertStatus(422);
    }

    public function test_stock_movements_index_supports_updated_since_and_product_filter(): void
    {
        $a = $this->product(['name' => 'A']);
        $b = $this->product(['name' => 'B']);
        $this->withHeaders($this->h())->postJson("/api/v1/products/{$a->id}/adjust-stock", ['quantity_change' => 1]);
        $this->withHeaders($this->h())->postJson("/api/v1/products/{$b->id}/adjust-stock", ['quantity_change' => 1]);

        $onlyA = $this->withHeaders($this->h())->getJson("/api/v1/stock-movements?product_id={$a->id}")->json();
        $this->assertCount(1, $onlyA);
        $this->assertSame($a->id, $onlyA[0]['product_id']);

        $future = urlencode(now()->addMinute()->toIso8601String());
        $this->assertCount(0, $this->withHeaders($this->h())->getJson("/api/v1/stock-movements?updated_since={$future}")->json());
    }

    public function test_creating_a_product_with_initial_stock_logs_it(): void
    {
        $this->withHeaders($this->h())->postJson('/api/v1/products', [
            'name' => 'New Widget', 'selling_price' => 50, 'stock' => 25,
        ])->assertStatus(201);
        $p = Product::where('name', 'New Widget')->first();
        $rows = $this->movements($p);
        $this->assertCount(1, $rows);
        $this->assertSame('adjustment', $rows[0]->type);
        $this->assertSame(25, $rows[0]->quantity_change);
        $this->assertSame('Initial stock (new product)', $rows[0]->note);
    }

    public function test_creating_a_product_with_zero_stock_logs_nothing(): void
    {
        $this->withHeaders($this->h())->postJson('/api/v1/products', [
            'name' => 'Empty Widget', 'selling_price' => 50,
        ])->assertStatus(201);
        $p = Product::where('name', 'Empty Widget')->first();
        $this->assertCount(0, $this->movements($p));
    }

    public function test_editing_product_stock_logs_the_difference(): void
    {
        $p = $this->product(['stock' => 10]);
        $this->withHeaders($this->h())->putJson("/api/v1/products/{$p->id}", ['stock' => 7])->assertStatus(200);
        $m = $this->movements($p)->first();
        $this->assertSame('adjustment', $m->type);
        $this->assertSame(-3, (int) $m->quantity_change);
        $this->assertSame(7, (int) $m->resulting_stock);
        $this->assertSame('Stock edited', $m->note);

        // Editing other fields leaves the stock history alone.
        $this->withHeaders($this->h())->putJson("/api/v1/products/{$p->id}", ['name' => 'Renamed', 'stock' => 7])->assertStatus(200);
        $this->assertCount(1, $this->movements($p));
    }

    public function test_batch_stock_adjustment_without_product_id_is_a_422_not_a_500(): void
    {
        $r = $this->withHeaders($this->h())->postJson('/api/v1/sync/batch', ['operations' => [
            ['resource' => 'stock_adjustments', 'action' => 'create', 'op_id' => '1', 'data' => ['quantity_change' => 4]],
        ]])->assertStatus(200);
        $this->assertSame(422, $r->json('results.0.status'));
    }

    public function test_offline_adjustment_can_be_queued_through_the_batch_endpoint(): void
    {
        $p = $this->product(['stock' => 10]);
        $r = $this->withHeaders($this->h())->postJson('/api/v1/sync/batch', ['operations' => [
            ['resource' => 'stock_adjustments', 'action' => 'create', 'op_id' => '1', 'data' => ['product_id' => $p->id, 'quantity_change' => 4, 'note' => 'Recount']],
        ]])->assertStatus(200);
        $this->assertTrue($r->json('results.0.success'));
        $this->assertSame(14, $p->fresh()->stock);
        $rows = $this->movements($p);
        $this->assertCount(1, $rows);
        $this->assertSame('Recount', $rows[0]->note);
    }

    public function test_stock_movements_are_scoped_to_the_shop(): void
    {
        $otherUser = User::factory()->create();
        $otherShop = Shop::create(['owner_id' => $otherUser->id, 'name' => 'Other', 'status' => 'active']);
        $p = $this->product();
        $this->withHeaders($this->h())->postJson("/api/v1/products/{$p->id}/adjust-stock", ['quantity_change' => 5]);

        Sanctum::actingAs($otherUser);
        $this->withHeaders(['X-Shop-ID' => $otherShop->id])->getJson('/api/v1/stock-movements')->assertStatus(200)->assertJsonCount(0);
    }
}
