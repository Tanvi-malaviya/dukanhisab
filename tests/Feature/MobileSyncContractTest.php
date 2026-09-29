<?php

namespace Tests\Feature;

use App\Models\CashBook;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The mobile app keeps a local copy and pulls `GET /resource?updated_since=<cursor>`.
 * Anything the web panel (or another device) changes must show up in that feed —
 * including deletions and side effects (stock, dues, statuses) — or the two
 * sides drift apart. Time is frozen so "changed after the cursor" is exact.
 */
class MobileSyncContractTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Shop $shop;
    private Carbon $t0;

    protected function setUp(): void
    {
        parent::setUp();
        $this->t0 = Carbon::parse('2026-09-01 10:00:00');
        Carbon::setTestNow($this->t0);
        $this->user = User::factory()->create();
        $this->shop = Shop::create(['owner_id' => $this->user->id, 'name' => 'Sync Shop', 'status' => 'active']);
        Sanctum::actingAs($this->user);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function h(): array
    {
        return ['X-Shop-ID' => $this->shop->id];
    }

    /** Move the clock forward, as if the mobile app synced at t0+5m and the web changed data later. */
    private function later(): string
    {
        Carbon::setTestNow($this->t0->copy()->addMinutes(30));
        return $this->t0->copy()->addMinutes(5)->toIso8601String();
    }

    private function feed(string $resource, string $cursor): array
    {
        $data = $this->withHeaders($this->h())->getJson("/api/v1/{$resource}?updated_since=" . urlencode($cursor))->assertStatus(200)->json();
        return $data['data'] ?? $data['transactions'] ?? $data;
    }

    private function ids(array $rows): array
    {
        return array_map(fn ($r) => $r['id'], $rows);
    }

    private function product(array $o = []): Product
    {
        return Product::create($o + ['shop_id' => $this->shop->id, 'name' => 'P', 'stock' => 20, 'selling_price' => 100, 'purchase_price' => 60]);
    }

    private function saleVia(Product $p, int $qty, string $type = 'Cash', ?Customer $c = null)
    {
        $total = $qty * 100;
        return $this->withHeaders($this->h())->postJson('/api/v1/sales', [
            'customer_id' => $c?->id, 'subtotal' => $total, 'grand_total' => $total, 'payment_type' => $type,
            'items' => [['product_id' => $p->id, 'quantity' => $qty, 'selling_price' => 100]],
        ])->assertStatus(201);
    }

    // ------------------------------------------------ side effects reach the feed

    public function test_web_sale_changes_stock_and_the_mobile_feed_sees_product_and_sale(): void
    {
        $p = $this->product();
        $cursor = $this->later();
        $saleId = $this->saleVia($p, 3)->json('id');

        $products = $this->feed('products', $cursor);
        $this->assertContains($p->id, $this->ids($products));
        $this->assertSame(17, collect($products)->firstWhere('id', $p->id)['stock']);
        $this->assertContains($saleId, $this->ids($this->feed('sales', $cursor)));
        $this->assertNotEmpty($this->feed('cashbooks', $cursor));
    }

    public function test_web_collect_payment_updates_customer_and_sale_status_in_feed(): void
    {
        $p = $this->product();
        $c = Customer::create(['shop_id' => $this->shop->id, 'name' => 'C', 'mobile' => '9000000001', 'due_amount' => 0]);
        $saleId = $this->saleVia($p, 2, 'Credit', $c)->json('id');
        $cursor = $this->later();

        $this->withHeaders($this->h())->postJson("/api/v1/customers/{$c->id}/collect-payment", ['amount' => 200, 'payment_method' => 'Cash'])->assertStatus(200);

        $cust = collect($this->feed('customers', $cursor))->firstWhere('id', $c->id);
        $this->assertNotNull($cust, 'customer must be in the feed after a payment');
        $this->assertEquals(0, (float) $cust['due_amount']);
        $sale = collect($this->feed('sales', $cursor))->firstWhere('id', $saleId);
        $this->assertNotNull($sale, 'sale whose status flipped must be in the feed');
        $this->assertSame('Completed', $sale['status']);
    }

    public function test_web_cancel_reaches_sale_product_and_customer_feeds(): void
    {
        $p = $this->product();
        $c = Customer::create(['shop_id' => $this->shop->id, 'name' => 'C', 'mobile' => '9000000002', 'due_amount' => 0]);
        $saleId = $this->saleVia($p, 2, 'Credit', $c)->json('id');
        $cursor = $this->later();

        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$saleId}/cancel", ['cancellation_reason' => 'wrong bill'])->assertStatus(200);

        $this->assertSame('Cancelled', collect($this->feed('sales', $cursor))->firstWhere('id', $saleId)['status']);
        $this->assertSame(20, collect($this->feed('products', $cursor))->firstWhere('id', $p->id)['stock']);
        $this->assertEquals(0, (float) collect($this->feed('customers', $cursor))->firstWhere('id', $c->id)['due_amount']);
    }

    public function test_web_return_with_credit_note_updates_customer_credit_in_feed(): void
    {
        $p = $this->product();
        $c = Customer::create(['shop_id' => $this->shop->id, 'name' => 'C', 'mobile' => '9000000003', 'due_amount' => 0]);
        $saleId = $this->saleVia($p, 1, 'Cash', $c)->json('id');
        $cursor = $this->later();

        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$saleId}/return", ['refund_method' => 'credit_note'])->assertStatus(200);

        $cust = collect($this->feed('customers', $cursor))->firstWhere('id', $c->id);
        $this->assertEquals(100, (float) $cust['credit_balance']);
        $sale = collect($this->feed('sales', $cursor))->firstWhere('id', $saleId);
        $this->assertSame('Returned', $sale['status']);
        $this->assertSame(1, $sale['items'][0]['returned_quantity']);
    }

    public function test_web_purchase_updates_supplier_due_and_stock_in_feed(): void
    {
        $p = $this->product(['stock' => 0]);
        $s = Supplier::create(['shop_id' => $this->shop->id, 'name' => 'S', 'mobile' => '8000000001', 'due_amount' => 0]);
        $cursor = $this->later();

        $purId = $this->withHeaders($this->h())->postJson('/api/v1/purchases', [
            'supplier_id' => $s->id, 'total_amount' => 500, 'payment_type' => 'Credit',
            'items' => [['product_id' => $p->id, 'quantity' => 5, 'purchase_price' => 100]],
        ])->assertStatus(201)->json('id');

        $this->assertContains($purId, $this->ids($this->feed('purchases', $cursor)));
        $this->assertEquals(500, (float) collect($this->feed('suppliers', $cursor))->firstWhere('id', $s->id)['due_amount']);
        $this->assertSame(5, collect($this->feed('products', $cursor))->firstWhere('id', $p->id)['stock']);
    }

    // ------------------------------------------- edits that leave totals unchanged

    public function test_editing_sale_items_without_changing_totals_still_reaches_the_feed(): void
    {
        $a = $this->product(['name' => 'A']);
        $b = $this->product(['name' => 'B']);
        $saleId = $this->saleVia($a, 2)->json('id');
        $cursor = $this->later();

        // Swap product A for product B at the same price/qty -> identical totals.
        $this->withHeaders($this->h())->putJson("/api/v1/sales/{$saleId}", [
            'subtotal' => 200, 'grand_total' => 200,
            'items' => [['product_id' => $b->id, 'quantity' => 2, 'selling_price' => 100]],
        ])->assertStatus(200);

        $sale = collect($this->feed('sales', $cursor))->firstWhere('id', $saleId);
        $this->assertNotNull($sale, 'edited sale must be in the feed even when its totals did not change');
        $this->assertSame($b->id, $sale['items'][0]['product_id']);
    }

    public function test_editing_purchase_items_without_changing_totals_still_reaches_the_feed(): void
    {
        $a = $this->product(['name' => 'A']);
        $b = $this->product(['name' => 'B']);
        $purId = $this->withHeaders($this->h())->postJson('/api/v1/purchases', [
            'total_amount' => 300, 'payment_type' => 'Cash',
            'items' => [['product_id' => $a->id, 'quantity' => 3, 'purchase_price' => 100]],
        ])->assertStatus(201)->json('id');
        $cursor = $this->later();

        $this->withHeaders($this->h())->putJson("/api/v1/purchases/{$purId}", [
            'total_amount' => 300,
            'items' => [['product_id' => $b->id, 'quantity' => 3, 'purchase_price' => 100]],
        ])->assertStatus(200);

        $pur = collect($this->feed('purchases', $cursor))->firstWhere('id', $purId);
        $this->assertNotNull($pur, 'edited purchase must be in the feed even when its totals did not change');
        $this->assertSame($b->id, $pur['items'][0]['product_id']);
    }

    // ------------------------------------------------------------ deletions

    public function test_deleted_records_appear_in_the_feed_with_deleted_at(): void
    {
        $cat = Category::create(['shop_id' => $this->shop->id, 'name' => 'Cat']);
        $p = $this->product();
        $c = Customer::create(['shop_id' => $this->shop->id, 'name' => 'C', 'mobile' => '9000000004', 'due_amount' => 0]);
        $s = Supplier::create(['shop_id' => $this->shop->id, 'name' => 'S', 'mobile' => '8000000002', 'due_amount' => 0]);
        $cursor = $this->later();

        $this->withHeaders($this->h())->deleteJson("/api/v1/categories/{$cat->id}")->assertStatus(204);
        $this->withHeaders($this->h())->deleteJson("/api/v1/products/{$p->id}")->assertStatus(204);
        $this->withHeaders($this->h())->deleteJson("/api/v1/customers/{$c->id}")->assertStatus(204);
        $this->withHeaders($this->h())->deleteJson("/api/v1/suppliers/{$s->id}")->assertStatus(204);

        foreach ([['categories', $cat->id], ['products', $p->id], ['customers', $c->id], ['suppliers', $s->id]] as [$res, $id]) {
            $row = collect($this->feed($res, $cursor))->firstWhere('id', $id);
            $this->assertNotNull($row, "deleted {$res} #{$id} must be in the feed");
            $this->assertNotNull($row['deleted_at'], "deleted {$res} #{$id} must carry deleted_at");
        }
    }

    public function test_deleted_expense_and_cashbook_entry_appear_in_the_feed(): void
    {
        $exp = $this->withHeaders($this->h())->postJson('/api/v1/expenses', ['amount' => 50, 'payment_method' => 'cash', 'description' => 'Tea'])->assertStatus(201)->json('id');
        $cb = $this->withHeaders($this->h())->postJson('/api/v1/cashbooks', ['type' => 'cash_in', 'amount' => 10, 'description' => 'Capital'])->assertStatus(201)->json('id');
        $cursor = $this->later();

        $this->withHeaders($this->h())->deleteJson("/api/v1/expenses/{$exp}")->assertStatus(204);
        $this->withHeaders($this->h())->deleteJson("/api/v1/cashbooks/{$cb}")->assertStatus(204);

        $e = collect($this->feed('expenses', $cursor))->firstWhere('id', $exp);
        $this->assertNotNull($e, 'deleted expense must be in the expenses feed so mobile can remove it');
        $this->assertNotNull($e['deleted_at']);
        $c = collect($this->feed('cashbooks', $cursor))->firstWhere('id', $cb);
        $this->assertNotNull($c);
        $this->assertNotNull($c['deleted_at']);
    }

    public function test_updated_since_returns_only_changes_after_the_cursor(): void
    {
        $old = $this->product(['name' => 'Old']);
        $cursor = $this->later();
        $new = $this->product(['name' => 'New']);
        $ids = $this->ids($this->feed('products', $cursor));
        $this->assertContains($new->id, $ids);
        $this->assertNotContains($old->id, $ids);
    }

    // ------------------------------------------------- field types mobile parses

    public function test_json_field_types_match_what_the_mobile_parser_casts(): void
    {
        // The Dart parser uses hard casts (`as int`, `as bool?`, `as String`): a wrong type aborts the whole pull.
        $p = $this->product();
        $c = Customer::create(['shop_id' => $this->shop->id, 'name' => 'C', 'mobile' => '9000000005', 'due_amount' => 0]);
        $s = Supplier::create(['shop_id' => $this->shop->id, 'name' => 'S', 'mobile' => '8000000003', 'due_amount' => 0]);
        $this->saleVia($p, 1, 'Credit', $c);
        $this->withHeaders($this->h())->postJson('/api/v1/purchases', [
            'supplier_id' => $s->id, 'total_amount' => 100, 'payment_type' => 'Credit',
            'items' => [['product_id' => $p->id, 'quantity' => 1, 'purchase_price' => 100]],
        ])->assertStatus(201);
        $this->withHeaders($this->h())->postJson('/api/v1/cashbooks', ['type' => 'cash_in', 'amount' => 10, 'description' => 'Capital'])->assertStatus(201);
        $cursor = '2000-01-01T00:00:00Z';

        $prod = $this->feed('products', $cursor)[0];
        $this->assertIsInt($prod['id']);
        $this->assertIsInt($prod['shop_id']);
        $this->assertIsInt($prod['stock']);
        $this->assertIsInt($prod['low_stock_threshold']);
        $this->assertIsBool($prod['available_for_sale']);
        $this->assertIsBool($prod['available_for_purchase']);
        $this->assertIsString($prod['created_at']);
        $this->assertIsString($prod['updated_at']);
        $this->assertArrayHasKey('deleted_at', $prod);

        $sale = $this->feed('sales', $cursor)[0];
        foreach (['id', 'shop_id', 'sale_number', 'subtotal', 'discount', 'grand_total', 'store_credit', 'payment_type', 'status', 'sale_date', 'created_at', 'updated_at', 'items', 'customer'] as $k) {
            $this->assertArrayHasKey($k, $sale, "sale.$k");
        }
        $this->assertIsInt($sale['shop_id']);
        $this->assertIsString($sale['sale_number']);
        $this->assertIsString($sale['sale_date']);
        $this->assertIsInt($sale['items'][0]['quantity']);
        $this->assertIsInt($sale['items'][0]['returned_quantity']);
        $this->assertArrayHasKey('product', $sale['items'][0]);

        $pur = $this->feed('purchases', $cursor)[0];
        foreach (['id', 'shop_id', 'purchase_number', 'total_amount', 'paid_amount', 'discount', 'payment_type', 'status', 'purchase_date', 'created_at', 'updated_at', 'items', 'supplier'] as $k) {
            $this->assertArrayHasKey($k, $pur, "purchase.$k");
        }
        $this->assertIsString($pur['purchase_date']);
        $this->assertIsInt($pur['items'][0]['quantity']);

        $cust = $this->feed('customers', $cursor)[0];
        foreach (['id', 'shop_id', 'name', 'due_amount', 'credit_balance', 'updated_at', 'created_at'] as $k) {
            $this->assertArrayHasKey($k, $cust, "customer.$k");
        }
        $sup = $this->feed('suppliers', $cursor)[0];
        foreach (['id', 'shop_id', 'name', 'due_amount', 'updated_at', 'created_at'] as $k) {
            $this->assertArrayHasKey($k, $sup, "supplier.$k");
        }
        $cb = $this->feed('cashbooks', $cursor)[0];
        foreach (['id', 'shop_id', 'type', 'amount', 'payment_method', 'transaction_date', 'updated_at'] as $k) {
            $this->assertArrayHasKey($k, $cb, "cashbook.$k");
        }
    }

    // ---------------------------------------- offline retry safety (mobile -> server)

    public function test_replaying_a_batch_with_the_same_idempotency_key_does_not_duplicate(): void
    {
        // What the app does after a request whose response was lost: send the same batch again.
        $p = $this->product();
        $batch = ['operations' => [[
            'resource' => 'sales', 'action' => 'create', 'op_id' => '1',
            'data' => [
                'subtotal' => 100, 'grand_total' => 100, 'payment_type' => 'Cash',
                'items' => [['product_id' => $p->id, 'quantity' => 1, 'selling_price' => 100]],
            ],
        ]]];
        $hdr = $this->h() + ['Idempotency-Key' => 'batch-key-1'];

        $first = $this->withHeaders($hdr)->postJson('/api/v1/sync/batch', $batch)->assertStatus(200);
        $again = $this->withHeaders($hdr)->postJson('/api/v1/sync/batch', $batch)->assertStatus(200);

        $this->assertTrue($first->json('results.0.success'));
        $this->assertEquals($first->json('results.0.data.id'), $again->json('results.0.data.id'));
        $this->assertSame(1, \App\Models\Sale::count());
        $this->assertSame(19, $p->fresh()->stock);
        $this->assertSame(1, CashBook::where('reference_type', 'sale')->count());
    }

    public function test_batch_reports_per_operation_results_and_isolates_failures(): void
    {
        $p = $this->product();
        $r = $this->withHeaders($this->h())->postJson('/api/v1/sync/batch', ['operations' => [
            ['resource' => 'customers', 'action' => 'create', 'op_id' => 'a', 'data' => ['name' => 'Offline Cust']],
            ['resource' => 'sales', 'action' => 'create', 'op_id' => 'b', 'data' => ['subtotal' => 1]], // invalid
            ['resource' => 'products', 'action' => 'update', 'op_id' => 'c', 'id' => $p->id, 'data' => ['selling_price' => 150]],
        ]])->assertStatus(200);

        $res = collect($r->json('results'))->keyBy('op_id');
        $this->assertTrue($res['a']['success']);
        $this->assertFalse($res['b']['success']);
        $this->assertSame(422, $res['b']['status']);
        $this->assertTrue($res['c']['success']);
        $this->assertEquals(150, (float) $p->fresh()->selling_price);
    }

    public function test_return_and_cancel_retries_with_the_same_key_are_replayed_not_reapplied(): void
    {
        $p = $this->product();
        $saleId = $this->saleVia($p, 4)->json('id');
        $hdr = $this->h() + ['Idempotency-Key' => 'ret-1'];
        $body = ['refund_method' => 'cash', 'items' => [['product_id' => $p->id, 'quantity' => 1]]];

        $this->withHeaders($hdr)->postJson("/api/v1/sales/{$saleId}/return", $body)->assertStatus(200);
        $this->withHeaders($hdr)->postJson("/api/v1/sales/{$saleId}/return", $body)->assertStatus(200);

        $this->assertSame(17, $p->fresh()->stock, 'a replayed return must not add stock twice');
        $this->assertSame(1, CashBook::where('type', 'cash_out')->count());
    }

    // ------------------------------------------------ retry safety by client id

    private function batchSale(Product $p, string $opId, ?string $uuid): array
    {
        return ['resource' => 'sales', 'action' => 'create', 'op_id' => $opId, 'client_uuid' => $uuid, 'data' => [
            'subtotal' => 100, 'grand_total' => 100, 'payment_type' => 'Cash',
            'items' => [['product_id' => $p->id, 'quantity' => 1, 'selling_price' => 100]],
        ]];
    }

    public function test_a_retried_create_with_a_different_request_shape_is_still_not_duplicated(): void
    {
        // Different op_id, different Idempotency-Key, different batch composition — only the client_uuid ties them together.
        $p = $this->product();
        $first = $this->withHeaders($this->h() + ['Idempotency-Key' => 'k1'])
            ->postJson('/api/v1/sync/batch', ['operations' => [$this->batchSale($p, '1', 'uuid-sale-1')]])->assertStatus(200);
        $again = $this->withHeaders($this->h() + ['Idempotency-Key' => 'k2'])
            ->postJson('/api/v1/sync/batch', ['operations' => [
                ['resource' => 'customers', 'action' => 'create', 'op_id' => '9', 'client_uuid' => 'uuid-cust-1', 'data' => ['name' => 'Extra']],
                $this->batchSale($p, '7', 'uuid-sale-1'),
            ]])->assertStatus(200);

        $this->assertTrue($first->json('results.0.success'));
        $this->assertTrue($again->json('results.1.success'));
        $this->assertEquals($first->json('results.0.data.id'), $again->json('results.1.data.id'));
        $this->assertSame(1, \App\Models\Sale::count());
        $this->assertSame(19, $p->fresh()->stock, 'stock must be reduced once');
        $this->assertSame(1, CashBook::where('reference_type', 'sale')->count());
        $this->assertSame(1, Customer::where('name', 'Extra')->count());
    }

    public function test_client_uuid_dedupes_every_creatable_resource_and_is_per_shop(): void
    {
        $ops = [
            ['resource' => 'customers', 'action' => 'create', 'client_uuid' => 'u-c', 'data' => ['name' => 'C']],
            ['resource' => 'suppliers', 'action' => 'create', 'client_uuid' => 'u-s', 'data' => ['name' => 'S']],
            ['resource' => 'categories', 'action' => 'create', 'client_uuid' => 'u-k', 'data' => ['name' => 'K']],
            ['resource' => 'products', 'action' => 'create', 'client_uuid' => 'u-p', 'data' => ['name' => 'P', 'selling_price' => 5]],
            ['resource' => 'expenses', 'action' => 'create', 'client_uuid' => 'u-e', 'data' => ['amount' => 5, 'payment_method' => 'cash', 'description' => 'x']],
            ['resource' => 'cashbooks', 'action' => 'create', 'client_uuid' => 'u-b', 'data' => ['type' => 'cash_in', 'amount' => 5, 'description' => 'y']],
        ];
        foreach ([1, 2] as $attempt) {
            $r = $this->withHeaders($this->h())->postJson('/api/v1/sync/batch', ['operations' => $ops])->assertStatus(200);
            foreach ($r->json('results') as $res) {
                $this->assertTrue($res['success'], $res['resource'] . ' attempt ' . $attempt . ': ' . json_encode($res['error'] ?? null));
            }
        }
        $this->assertSame(1, Customer::count());
        $this->assertSame(1, Supplier::count());
        $this->assertSame(1, Category::count());
        $this->assertSame(1, Product::count());
        $this->assertSame(2, CashBook::count(), 'one expense + one manual entry');

        // The same uuid in another shop is a different record.
        $otherUser = User::factory()->create();
        $other = Shop::create(['owner_id' => $otherUser->id, 'name' => 'Second', 'status' => 'active']);
        Sanctum::actingAs($otherUser);
        $this->withHeaders(['X-Shop-ID' => $other->id])->postJson('/api/v1/sync/batch', ['operations' => [$ops[0]]])->assertStatus(200);
        $this->assertSame(2, Customer::count());
    }

    public function test_batch_with_an_offline_expense_no_longer_blocks_the_rest_of_the_queue(): void
    {
        $p = $this->product();
        $r = $this->withHeaders($this->h())->postJson('/api/v1/sync/batch', ['operations' => [
            ['resource' => 'expenses', 'action' => 'create', 'op_id' => '1', 'data' => ['amount' => 40, 'payment_method' => 'cash', 'description' => 'Tea', 'transaction_date' => '2026-08-30T09:00:00Z']],
            $this->batchSale($p, '2', null),
        ]])->assertStatus(200);
        $this->assertTrue($r->json('results.0.success'));
        $this->assertTrue($r->json('results.1.success'));
        $this->assertSame('2026-08-30', CashBook::where('reference_type', 'expense')->first()->transaction_date->toDateString(), 'offline entry keeps its real date');
    }

    // ---------------------------------------------- statuses kept at write time

    public function test_listing_sales_does_not_write_anything(): void
    {
        $p = $this->product();
        $c = Customer::create(['shop_id' => $this->shop->id, 'name' => 'C', 'mobile' => '9000000006', 'due_amount' => 0]);
        $this->saleVia($p, 2, 'Credit', $c);
        $before = \App\Models\Sale::first()->updated_at;
        Carbon::setTestNow($this->t0->copy()->addHour());
        \Illuminate\Support\Facades\DB::enableQueryLog();
        $this->withHeaders($this->h())->getJson('/api/v1/sales')->assertStatus(200);
        $writes = collect(\Illuminate\Support\Facades\DB::getQueryLog())->filter(fn ($q) => preg_match('/^(update|insert|delete)/i', $q['query']))->count();
        $this->assertSame(0, $writes, 'GET /sales must be read-only');
        $this->assertTrue(\App\Models\Sale::first()->updated_at->equalTo($before));
    }

    public function test_credit_statuses_stay_correct_through_writes_without_any_list_call(): void
    {
        $p = $this->product();
        $c = Customer::create(['shop_id' => $this->shop->id, 'name' => 'C', 'mobile' => '9000000007', 'due_amount' => 0]);
        $a = $this->saleVia($p, 2, 'Credit', $c)->json('id'); // 200
        $b = $this->saleVia($p, 3, 'Credit', $c)->json('id'); // 300
        $status = fn ($id) => \App\Models\Sale::find($id)->status;
        $this->assertSame('Unpaid', $status($a));

        $this->withHeaders($this->h())->postJson("/api/v1/customers/{$c->id}/collect-payment", ['amount' => 250, 'payment_method' => 'Cash'])->assertStatus(200);
        $this->assertSame('Completed', $status($a));
        $this->assertSame('Partially Paid', $status($b));
        $this->assertEquals(50, (float) \App\Models\Sale::find($b)->paid_amount);

        // Cancelling the fully paid one changes what the customer still owes -> statuses must follow.
        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$a}/cancel", ['cancellation_reason' => 'wrong item'])->assertStatus(200);
        $this->assertSame('Cancelled', $status($a));
        $this->assertContains($status($b), ['Partially Paid', 'Completed', 'Unpaid']);
        $this->assertEquals((float) $c->fresh()->due_amount, (float) \App\Models\Sale::find($b)->grand_total - (float) \App\Models\Sale::find($b)->paid_amount);
    }

    public function test_purchase_statuses_follow_supplier_payments_without_a_list_call(): void
    {
        $p = $this->product();
        $s = Supplier::create(['shop_id' => $this->shop->id, 'name' => 'S', 'mobile' => '8000000004', 'due_amount' => 0]);
        $id = $this->withHeaders($this->h())->postJson('/api/v1/purchases', [
            'supplier_id' => $s->id, 'total_amount' => 500, 'payment_type' => 'Credit',
            'items' => [['product_id' => $p->id, 'quantity' => 5, 'purchase_price' => 100]],
        ])->assertStatus(201)->assertJsonPath('status', 'Unpaid')->json('id');
        $this->withHeaders($this->h())->postJson("/api/v1/suppliers/{$s->id}/pay-due", ['amount' => 500, 'payment_method' => 'Cash'])->assertStatus(200);
        $this->assertSame('Completed', \App\Models\Purchase::find($id)->status);
    }

    // ------------------------------------------------ dashboard correctness

    public function test_dashboard_numbers_match_the_underlying_transactions(): void
    {
        $p = $this->product(['low_stock_threshold' => 0]);
        $c = Customer::create(['shop_id' => $this->shop->id, 'name' => 'C', 'mobile' => '9000000008', 'due_amount' => 0]);
        $s = Supplier::create(['shop_id' => $this->shop->id, 'name' => 'S', 'mobile' => '8000000005', 'due_amount' => 0]);

        $keep = $this->saleVia($p, 2, 'Cash')->json('id');              // +200 cash
        $partial = $this->saleVia($p, 4, 'UPI')->json('id');            // +400 upi
        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$partial}/return", ['refund_method' => 'upi', 'items' => [['product_id' => $p->id, 'quantity' => 1]]])->assertStatus(200); // -100 upi, sale now 300
        $gone = $this->saleVia($p, 1, 'Cash')->json('id');              // +100 cash, then cancelled
        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$gone}/cancel", ['cancellation_reason' => 'cancelled'])->assertStatus(200); // -100 cash
        $this->saleVia($p, 3, 'Credit', $c);                            // 300 due, no cash
        $this->withHeaders($this->h())->postJson('/api/v1/purchases', [
            'supplier_id' => $s->id, 'total_amount' => 500, 'paid_amount' => 200, 'payment_type' => 'Cash',
            'items' => [['product_id' => $p->id, 'quantity' => 5, 'purchase_price' => 100]],
        ])->assertStatus(201);                                          // -200 cash, supplier due 300
        $this->withHeaders($this->h())->postJson('/api/v1/expenses', ['amount' => 30, 'payment_method' => 'cash', 'description' => 'Tea'])->assertStatus(201); // -30 cash

        $d = $this->withHeaders($this->h())->getJson('/api/v1/dashboard')->assertStatus(200)->json();
        $this->assertEquals(200 + 300 + 300, $d['today_sales'], 'kept + partially returned (net) + credit; cancelled excluded');
        $this->assertEquals(500, $d['today_purchases']);
        $this->assertEquals(200 + 100 - 100 - 200 - 30, $d['cash_balance']);
        $this->assertEquals(400 - 100, $d['bank_balance']);
        $this->assertEquals(300, $d['customer_due']);
        $this->assertEquals(300, $d['supplier_due']);

        $r = $this->withHeaders($this->h())->getJson('/api/v1/reports')->assertStatus(200)->json();
        $this->assertEquals($d['today_sales'], $r['total_sales'], 'dashboard and reports agree');
        $this->assertEquals(3, $r['sales_count'], 'kept + partially returned + credit; cancelled excluded');
        $this->assertEquals(500, $r['total_purchases']);
        $this->assertEquals(30, $r['total_expenses']);
    }

    // ------------------------------- secondary records the app edits offline

    public function test_secondary_records_can_be_queued_through_the_batch(): void
    {
        $p = $this->product();
        $c = Customer::create(['shop_id' => $this->shop->id, 'name' => 'C', 'mobile' => '9000000009', 'due_amount' => 0]);
        $s = Supplier::create(['shop_id' => $this->shop->id, 'name' => 'S', 'mobile' => '8000000006', 'due_amount' => 0]);
        $this->withHeaders($this->h())->postJson('/api/v1/cashbooks', ['type' => 'cash_in', 'amount' => 500, 'description' => 'Float'])->assertStatus(201);

        $r = $this->withHeaders($this->h())->postJson('/api/v1/sync/batch', ['operations' => [
            ['resource' => 'customer_prices', 'action' => 'update', 'op_id' => '1', 'id' => $c->id, 'data' => ['prices' => [['product_id' => $p->id, 'custom_price' => 80]]]],
            ['resource' => 'supplier_prices', 'action' => 'update', 'op_id' => '2', 'id' => $s->id, 'data' => ['prices' => [['product_id' => $p->id, 'custom_price' => 40]]]],
            ['resource' => 'register_closures', 'action' => 'create', 'op_id' => '3', 'data' => ['actual_cash' => 500]],
            ['resource' => 'bank_transfers', 'action' => 'create', 'op_id' => '4', 'data' => ['type' => 'deposit', 'amount' => 100]],
        ]])->assertStatus(200);

        foreach ($r->json('results') as $res) {
            $this->assertTrue($res['success'], $res['resource'] . ': ' . json_encode($res['error'] ?? null));
        }
        $this->assertEquals(80, \App\Models\CustomerProductPrice::first()->custom_price);
        $this->assertEquals(40, \App\Models\SupplierProductPrice::first()->custom_price);
        $this->assertSame(1, \App\Models\CashRegisterClosure::count());
        $this->assertSame(2, CashBook::where('reference_type', 'contra')->count());
    }

    public function test_bulk_price_sheets_match_the_per_party_endpoints(): void
    {
        $p = $this->product();
        $c = Customer::create(['shop_id' => $this->shop->id, 'name' => 'C', 'mobile' => '9000000010', 'due_amount' => 0]);
        $s = Supplier::create(['shop_id' => $this->shop->id, 'name' => 'S', 'mobile' => '8000000007', 'due_amount' => 0]);
        $this->withHeaders($this->h())->postJson("/api/v1/customers/{$c->id}/product-prices", ['prices' => [['product_id' => $p->id, 'custom_price' => 70]]])->assertStatus(200);
        $this->withHeaders($this->h())->postJson("/api/v1/suppliers/{$s->id}/product-prices", ['prices' => [['product_id' => $p->id, 'custom_price' => 30]]])->assertStatus(200);

        $bulkC = $this->withHeaders($this->h())->getJson('/api/v1/product-prices/customers')->assertStatus(200)->json();
        $this->assertEquals($this->withHeaders($this->h())->getJson("/api/v1/customers/{$c->id}/product-prices")->json(), $bulkC[(string) $c->id]);
        $bulkS = $this->withHeaders($this->h())->getJson('/api/v1/product-prices/suppliers')->assertStatus(200)->json();
        $this->assertEquals($this->withHeaders($this->h())->getJson("/api/v1/suppliers/{$s->id}/product-prices")->json(), $bulkS[(string) $s->id]);
    }
}
