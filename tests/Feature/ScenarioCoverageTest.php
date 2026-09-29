<?php

namespace Tests\Feature;

use App\Models\CashBook;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * End-to-end scenarios across sales, purchases, dues, cashbook, reports,
 * dashboard and multi-shop isolation.
 */
class ScenarioCoverageTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->shop = Shop::create(['owner_id' => $this->user->id, 'name' => 'Scenario Shop', 'status' => 'active']);
        Sanctum::actingAs($this->user);
    }

    private function h(?Shop $shop = null): array
    {
        return ['X-Shop-ID' => ($shop ?? $this->shop)->id];
    }

    private function product(array $o = []): Product
    {
        return Product::create($o + [
            'shop_id' => $this->shop->id, 'name' => 'P' . uniqid(), 'stock' => 20,
            'selling_price' => 100, 'purchase_price' => 60,
        ]);
    }

    private function customer(array $o = []): Customer
    {
        return Customer::create($o + ['shop_id' => $this->shop->id, 'name' => 'Cust', 'mobile' => '9' . rand(100000000, 999999999), 'due_amount' => 0]);
    }

    private function supplier(array $o = []): Supplier
    {
        return Supplier::create($o + ['shop_id' => $this->shop->id, 'name' => 'Sup', 'mobile' => '8' . rand(100000000, 999999999), 'due_amount' => 0]);
    }

    private function sale(Product $p, int $qty, string $type = 'Cash', ?Customer $c = null, array $extra = [])
    {
        $total = $qty * (float) $p->selling_price;
        return $this->withHeaders($this->h())->postJson('/api/v1/sales', $extra + [
            'customer_id' => $c?->id, 'subtotal' => $total, 'grand_total' => $total, 'payment_type' => $type,
            'items' => [['product_id' => $p->id, 'quantity' => $qty, 'selling_price' => $p->selling_price]],
        ]);
    }

    // ---------------------------------------------------------------- Sales

    public function test_cash_sale_reduces_stock_posts_cashbook_and_numbers_invoice(): void
    {
        $p = $this->product();
        $r = $this->sale($p, 3)->assertStatus(201);
        $this->assertEquals(17, $p->fresh()->stock);
        $this->assertMatchesRegularExpression('/^INV-\d{8}-0001$/', $r->json('sale_number'));
        $cb = CashBook::where('reference_type', 'sale')->first();
        $this->assertEquals('cash_in', $cb->type);
        $this->assertEquals(300, (float) $cb->amount);
        $this->assertEquals('cash', $cb->payment_method);
        $this->assertStringEndsWith('0002', $this->sale($p, 1)->json('sale_number'));
    }

    public function test_upi_and_bank_sales_book_correct_payment_method(): void
    {
        $p = $this->product();
        $this->sale($p, 1, 'UPI')->assertStatus(201);
        $this->sale($p, 1, 'Bank')->assertStatus(201);
        $this->assertEquals(1, CashBook::where('payment_method', 'upi')->count());
        $this->assertEquals(1, CashBook::where('payment_method', 'bank')->count());
    }

    public function test_sale_validation_rejects_bad_payloads(): void
    {
        $p = $this->product();
        $this->withHeaders($this->h())->postJson('/api/v1/sales', [])->assertStatus(422);
        $this->withHeaders($this->h())->postJson('/api/v1/sales', [
            'subtotal' => 10, 'grand_total' => 10, 'payment_type' => 'Cheque',
            'items' => [['product_id' => $p->id, 'quantity' => 1, 'selling_price' => 10]],
        ])->assertStatus(422);
        $this->withHeaders($this->h())->postJson('/api/v1/sales', [
            'subtotal' => 10, 'grand_total' => 10, 'payment_type' => 'Cash',
            'items' => [['product_id' => $p->id, 'quantity' => 0, 'selling_price' => 10]],
        ])->assertStatus(422);
    }

    /** The web/mobile Rate field lets a cashier override the sale price per line — the server must accept it as-is. */
    public function test_sale_accepts_a_price_different_from_the_products_catalog_price(): void
    {
        $p = $this->product(['selling_price' => 100]);
        $r = $this->withHeaders($this->h())->postJson('/api/v1/sales', [
            'subtotal' => 80, 'grand_total' => 80, 'payment_type' => 'Cash',
            'items' => [['product_id' => $p->id, 'quantity' => 1, 'selling_price' => 80]],
        ])->assertStatus(201);
        $this->assertEquals(80, (float) $r->json('items.0.selling_price'));
        $this->assertEquals(100, (float) $p->fresh()->selling_price, 'overriding a sale price must not change the product\'s own catalog price');
    }

    public function test_sale_larger_than_stock_is_allowed_and_stock_goes_negative(): void
    {
        // Deliberate: shops (and offline mobile sync) may sell before stock is entered.
        $p = $this->product(['stock' => 2]);
        $this->sale($p, 5)->assertStatus(201);
        $this->assertEquals(-3, $p->fresh()->stock);
    }

    public function test_credit_sale_increases_due_without_cashbook(): void
    {
        $p = $this->product();
        $c = $this->customer();
        $r = $this->sale($p, 2, 'Credit', $c)->assertStatus(201);
        $this->assertEquals('Unpaid', $r->json('status'));
        $this->assertEquals(200, (float) $c->fresh()->due_amount);
        $this->assertEquals(0, CashBook::count());
    }

    public function test_sale_with_store_credit_uses_credit_balance_and_notes(): void
    {
        $p = $this->product();
        $c = $this->customer(['credit_balance' => 150]);
        CreditNote::create([
            'shop_id' => $this->shop->id, 'customer_id' => $c->id, 'credit_note_number' => 'CN-T-1',
            'total_amount' => 150, 'used_amount' => 0, 'remaining_balance' => 150, 'status' => 'Active', 'reason' => 't',
        ]);
        $this->sale($p, 1, 'Store Credit', $c)->assertStatus(201);
        $this->assertEquals(50, (float) $c->fresh()->credit_balance);
        $cn = CreditNote::first();
        $this->assertEquals(100, (float) $cn->used_amount);
        $this->assertEquals(50, (float) $cn->remaining_balance);
    }

    public function test_sale_idempotency_key_prevents_double_posting(): void
    {
        $p = $this->product();
        $payload = [
            'subtotal' => 100, 'grand_total' => 100, 'payment_type' => 'Cash',
            'items' => [['product_id' => $p->id, 'quantity' => 1, 'selling_price' => 100]],
        ];
        $hdr = $this->h() + ['Idempotency-Key' => 'abc-123'];
        $first = $this->withHeaders($hdr)->postJson('/api/v1/sales', $payload)->assertStatus(201);
        $second = $this->withHeaders($hdr)->postJson('/api/v1/sales', $payload);
        $this->assertEquals($first->json('id'), $second->json('id'));
        $this->assertEquals(1, Sale::count());
        $this->assertEquals(19, $p->fresh()->stock);
        $this->withHeaders($hdr)->postJson('/api/v1/sales', ['subtotal' => 5] + $payload)->assertStatus(409);
    }

    public function test_sale_filters_and_search(): void
    {
        $p = $this->product(['name' => 'Searchable Widget']);
        $c = $this->customer(['name' => 'Ramesh']);
        $this->sale($p, 1, 'Cash', $c);
        $this->sale($p, 1, 'Credit', $c);
        $this->assertCount(1, $this->withHeaders($this->h())->getJson('/api/v1/sales?status=Unpaid')->json());
        $this->assertCount(2, $this->withHeaders($this->h())->getJson('/api/v1/sales?search=Ramesh')->json());
        $this->assertCount(2, $this->withHeaders($this->h())->getJson('/api/v1/sales?search=Widget')->json());
        $this->assertCount(0, $this->withHeaders($this->h())->getJson('/api/v1/sales?search=zzznone')->json());
    }

    public function test_edit_sale_items_reverts_and_reapplies_stock_and_cashbook(): void
    {
        $p = $this->product();
        $id = $this->sale($p, 2)->json('id');
        $this->withHeaders($this->h())->putJson("/api/v1/sales/{$id}", [
            'subtotal' => 500, 'grand_total' => 500,
            'items' => [['product_id' => $p->id, 'quantity' => 5, 'selling_price' => 100]],
        ])->assertStatus(200);
        $this->assertEquals(15, $p->fresh()->stock);
        $entries = CashBook::where('reference_type', 'sale')->where('reference_id', $id)->get();
        $this->assertCount(1, $entries);
        $this->assertEquals(500, (float) $entries[0]->amount);
    }

    public function test_edit_payment_type_cash_to_credit_moves_money_to_due(): void
    {
        $p = $this->product();
        $c = $this->customer();
        $id = $this->sale($p, 2, 'Cash', $c)->json('id');
        $this->withHeaders($this->h())->putJson("/api/v1/sales/{$id}", ['payment_type' => 'Credit'])->assertStatus(200);
        $this->assertEquals(200, (float) $c->fresh()->due_amount);
        $this->assertEquals(0, CashBook::where('reference_type', 'sale')->count());
    }

    public function test_cancel_requires_reason_and_cannot_repeat(): void
    {
        $p = $this->product();
        $id = $this->sale($p, 1)->json('id');
        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$id}/cancel", [])->assertStatus(422);
        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$id}/cancel", ['cancellation_reason' => 'oops'])->assertStatus(200);
        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$id}/cancel", ['cancellation_reason' => 'oops'])->assertStatus(400);
        $this->assertEquals(20, $p->fresh()->stock);
    }

    public function test_cancel_sale_with_store_credit_restores_credit(): void
    {
        $p = $this->product();
        $c = $this->customer(['credit_balance' => 100]);
        CreditNote::create([
            'shop_id' => $this->shop->id, 'customer_id' => $c->id, 'credit_note_number' => 'CN-T-2',
            'total_amount' => 100, 'used_amount' => 0, 'remaining_balance' => 100, 'status' => 'Active', 'reason' => 't',
        ]);
        $id = $this->sale($p, 1, 'Store Credit', $c)->json('id');
        $this->assertEquals(0, (float) $c->fresh()->credit_balance);
        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$id}/cancel", ['cancellation_reason' => 'undo'])->assertStatus(200);
        $this->assertEquals(100, (float) $c->fresh()->credit_balance);
        $this->assertEquals('Active', CreditNote::first()->status);
    }

    public function test_cancel_after_partial_return_restores_only_unreturned_stock(): void
    {
        $p = $this->product();
        $id = $this->sale($p, 4)->json('id');
        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$id}/return", [
            'refund_method' => 'cash', 'items' => [['product_id' => $p->id, 'quantity' => 1]],
        ])->assertStatus(200);
        $this->assertEquals(17, $p->fresh()->stock);
        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$id}/cancel", ['cancellation_reason' => 'rest'])->assertStatus(200);
        $this->assertEquals(20, $p->fresh()->stock, 'stock must not be double-restored');
    }

    // -------------------------------------------------------------- Returns

    public function test_full_return_cash_refund(): void
    {
        $p = $this->product();
        $id = $this->sale($p, 2)->json('id');
        $r = $this->withHeaders($this->h())->postJson("/api/v1/sales/{$id}/return", ['refund_method' => 'cash'])->assertStatus(200);
        $this->assertEquals('Returned', $r->json('status'));
        $this->assertEquals(20, $p->fresh()->stock);
        $out = CashBook::where('type', 'cash_out')->where('reference_id', $id)->first();
        $this->assertEquals(200, (float) $out->amount);
        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$id}/return", [])->assertStatus(400);
    }

    public function test_partial_return_updates_totals_status_and_refund(): void
    {
        $p = $this->product();
        $id = $this->sale($p, 3)->json('id');
        $r = $this->withHeaders($this->h())->postJson("/api/v1/sales/{$id}/return", [
            'refund_method' => 'upi', 'items' => [['product_id' => $p->id, 'quantity' => 1]],
        ])->assertStatus(200);
        $this->assertEquals('Partially Returned', $r->json('status'));
        $this->assertEquals(200, (float) $r->json('grand_total'));
        $this->assertEquals(18, $p->fresh()->stock);
        $out = CashBook::where('type', 'cash_out')->first();
        $this->assertEquals(100, (float) $out->amount);
        $this->assertEquals('upi', $out->payment_method);
    }

    public function test_return_more_than_sold_is_rejected(): void
    {
        $p = $this->product();
        $id = $this->sale($p, 2)->json('id');
        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$id}/return", [
            'items' => [['product_id' => $p->id, 'quantity' => 3]],
        ])->assertStatus(400);
        $this->assertEquals(18, $p->fresh()->stock);
    }

    public function test_return_on_credit_sale_reduces_due(): void
    {
        $p = $this->product();
        $c = $this->customer();
        $id = $this->sale($p, 2, 'Credit', $c)->json('id');
        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$id}/return", ['refund_method' => 'due_adjustment'])->assertStatus(200);
        $this->assertEquals(0, (float) $c->fresh()->due_amount);
        $this->assertEquals(0, CashBook::count());
    }

    public function test_return_as_credit_note_adds_store_credit(): void
    {
        $p = $this->product();
        $c = $this->customer();
        $id = $this->sale($p, 1, 'Cash', $c)->json('id');
        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$id}/return", ['refund_method' => 'credit_note'])->assertStatus(200);
        $this->assertEquals(100, (float) $c->fresh()->credit_balance);
        $this->assertEquals(1, CreditNote::count());
        $this->withHeaders($this->h())->getJson('/api/v1/credit-notes')->assertStatus(200)->assertJsonPath('total', 1);
    }

    // ------------------------------------------------------------ Customers

    public function test_customer_crud_and_search(): void
    {
        $r = $this->withHeaders($this->h())->postJson('/api/v1/customers', ['name' => 'Anil', 'mobile' => '9000000001'])->assertStatus(201);
        $id = $r->json('id');
        $this->withHeaders($this->h())->postJson('/api/v1/customers', [])->assertStatus(422);
        $this->withHeaders($this->h())->putJson("/api/v1/customers/{$id}", ['name' => 'Anil K'])->assertStatus(200);
        $this->assertCount(1, $this->withHeaders($this->h())->getJson('/api/v1/customers?search=9000000001')->json());
        $this->withHeaders($this->h())->deleteJson("/api/v1/customers/{$id}")->assertStatus(204);
        $this->assertCount(0, $this->withHeaders($this->h())->getJson('/api/v1/customers')->json());
    }

    public function test_collect_payment_partial_full_and_overpayment(): void
    {
        $p = $this->product();
        $c = $this->customer();
        $saleId = $this->sale($p, 3, 'Credit', $c)->json('id');
        $this->withHeaders($this->h())->postJson("/api/v1/customers/{$c->id}/collect-payment", ['amount' => 100, 'payment_method' => 'Cash'])->assertStatus(200);
        $this->assertEquals(200, (float) $c->fresh()->due_amount);
        $this->assertEquals('cash_in', CashBook::where('reference_type', 'customer_payment')->first()->type);
        $this->withHeaders($this->h())->postJson("/api/v1/customers/{$c->id}/collect-payment", ['amount' => 500, 'payment_method' => 'Cash'])->assertStatus(422);
        $this->withHeaders($this->h())->postJson("/api/v1/customers/{$c->id}/collect-payment", ['amount' => 0, 'payment_method' => 'Cash'])->assertStatus(422);
        $this->withHeaders($this->h())->postJson("/api/v1/customers/{$c->id}/collect-payment", ['amount' => 200, 'payment_method' => 'UPI'])->assertStatus(200);
        $this->assertEquals(0, (float) $c->fresh()->due_amount);
        $this->assertEquals('Completed', Sale::find($saleId)->status, 'sale should flip to Completed once due is cleared');
    }

    // ------------------------------------------------------------ Purchases

    public function test_cash_purchase_increases_stock_updates_cost_and_posts_cashbook(): void
    {
        $p = $this->product(['stock' => 5]);
        $r = $this->withHeaders($this->h())->postJson('/api/v1/purchases', [
            'total_amount' => 500, 'payment_type' => 'Cash',
            'items' => [['product_id' => $p->id, 'quantity' => 10, 'purchase_price' => 50]],
        ])->assertStatus(201);
        $this->assertStringStartsWith('PUR-', $r->json('purchase_number'));
        $this->assertEquals(15, $p->fresh()->stock);
        $this->assertEquals(50, (float) $p->fresh()->purchase_price);
        $cb = CashBook::where('reference_type', 'purchase')->first();
        $this->assertEquals('cash_out', $cb->type);
        $this->assertEquals(500, (float) $cb->amount);
    }

    public function test_credit_and_partial_purchase_update_supplier_due(): void
    {
        $p = $this->product();
        $s = $this->supplier();
        $this->withHeaders($this->h())->postJson('/api/v1/purchases', [
            'supplier_id' => $s->id, 'total_amount' => 1000, 'paid_amount' => 400, 'payment_type' => 'Cash',
            'items' => [['product_id' => $p->id, 'quantity' => 10, 'purchase_price' => 100]],
        ])->assertStatus(201)->assertJsonPath('status', 'Partially Paid');
        $this->assertEquals(600, (float) $s->fresh()->due_amount);
        $this->assertEquals(400, (float) CashBook::where('reference_type', 'purchase')->first()->amount);
    }

    public function test_purchase_with_due_requires_supplier(): void
    {
        $p = $this->product();
        $this->withHeaders($this->h())->postJson('/api/v1/purchases', [
            'total_amount' => 100, 'payment_type' => 'Credit',
            'items' => [['product_id' => $p->id, 'quantity' => 1, 'purchase_price' => 100]],
        ])->assertStatus(422);
        $this->assertEquals(20, $p->fresh()->stock);
    }

    public function test_purchase_return_full_and_partial(): void
    {
        $p = $this->product(['stock' => 0]);
        $s = $this->supplier();
        $mk = fn () => $this->withHeaders($this->h())->postJson('/api/v1/purchases', [
            'supplier_id' => $s->id, 'total_amount' => 500, 'payment_type' => 'Credit',
            'items' => [['product_id' => $p->id, 'quantity' => 5, 'purchase_price' => 100]],
        ])->assertStatus(201)->json('id');
        $a = $mk();
        $this->withHeaders($this->h())->postJson("/api/v1/purchases/{$a}/return", ['items' => [['product_id' => $p->id, 'quantity' => 2]]])->assertStatus(200);
        $this->assertEquals(3, $p->fresh()->stock);
        $this->withHeaders($this->h())->postJson("/api/v1/purchases/{$a}/return", ['items' => [['product_id' => $p->id, 'quantity' => 9]]])->assertStatus(400);
        $this->withHeaders($this->h())->postJson("/api/v1/purchases/{$a}/return", [])->assertStatus(200);
        $this->assertEquals(0, $p->fresh()->stock);
        $this->assertEquals(0, (float) $s->fresh()->due_amount);
    }

    public function test_pay_supplier_due(): void
    {
        $s = $this->supplier(['due_amount' => 300]);
        $this->withHeaders($this->h())->postJson("/api/v1/suppliers/{$s->id}/pay-due", ['amount' => 100, 'payment_method' => 'Bank'])->assertStatus(200);
        $this->assertEquals(200, (float) $s->fresh()->due_amount);
        $cb = CashBook::where('reference_type', 'supplier_payment')->first();
        $this->assertEquals('cash_out', $cb->type);
        $this->assertEquals('bank', $cb->payment_method);
        $this->withHeaders($this->h())->postJson("/api/v1/suppliers/{$s->id}/pay-due", ['amount' => 999, 'payment_method' => 'Cash'])->assertStatus(422);
    }

    public function test_supplier_crud(): void
    {
        $id = $this->withHeaders($this->h())->postJson('/api/v1/suppliers', ['name' => 'Vendor'])->assertStatus(201)->json('id');
        $this->withHeaders($this->h())->putJson("/api/v1/suppliers/{$id}", ['name' => 'Vendor 2'])->assertStatus(200);
        $this->withHeaders($this->h())->deleteJson("/api/v1/suppliers/{$id}")->assertStatus(204);
        $this->withHeaders($this->h())->postJson('/api/v1/suppliers', ['name' => ''])->assertStatus(422);
    }

    // ------------------------------------------------------ Products / cats

    public function test_product_and_category_crud(): void
    {
        $cat = $this->withHeaders($this->h())->postJson('/api/v1/categories', ['name' => 'Grocery'])->assertStatus(201)->json('id');
        $pid = $this->withHeaders($this->h())->postJson('/api/v1/products', [
            'name' => 'Rice', 'selling_price' => 50, 'purchase_price' => 40, 'stock' => 10, 'category_id' => $cat,
        ])->assertStatus(201)->json('id');
        $this->withHeaders($this->h())->postJson('/api/v1/products', ['name' => 'Bad'])->assertStatus(422);
        $this->withHeaders($this->h())->putJson("/api/v1/products/{$pid}", ['selling_price' => 55])->assertStatus(200);
        $this->assertEquals(55, (float) Product::find($pid)->selling_price);
        $this->withHeaders($this->h())->deleteJson("/api/v1/products/{$pid}")->assertStatus(204);
        $this->withHeaders($this->h())->deleteJson("/api/v1/categories/{$cat}")->assertStatus(204);
    }

    public function test_sale_and_purchase_availability_flags_can_be_saved(): void
    {
        $pid = $this->withHeaders($this->h())->postJson('/api/v1/products', [
            'name' => 'Raw', 'selling_price' => 10, 'available_for_sale' => false, 'available_for_purchase' => true,
        ])->assertStatus(201)->json('id');
        $p = Product::find($pid);
        $this->assertFalse((bool) $p->available_for_sale);
        $this->assertTrue((bool) $p->available_for_purchase);
    }

    public function test_low_stock_appears_on_dashboard(): void
    {
        $this->product(['stock' => 2, 'low_stock_threshold' => 5]);
        $this->product(['stock' => 50, 'low_stock_threshold' => 5]);
        $this->withHeaders($this->h())->getJson('/api/v1/dashboard')->assertJsonPath('low_stock_count', 1);
    }

    // ------------------------------------------------- Expenses / cashbook

    public function test_expense_crud_posts_cash_out(): void
    {
        $id = $this->withHeaders($this->h())->postJson('/api/v1/expenses', [
            'amount' => 250, 'payment_method' => 'cash', 'description' => 'Rent',
        ])->assertStatus(201)->json('id');
        $this->withHeaders($this->h())->postJson('/api/v1/expenses', ['amount' => 0, 'payment_method' => 'cash', 'description' => 'x'])->assertStatus(422);
        $this->withHeaders($this->h())->putJson("/api/v1/expenses/{$id}", ['amount' => 300])->assertStatus(200);
        $this->assertEquals(300, (float) CashBook::find($id)->amount);
        $this->withHeaders($this->h())->deleteJson("/api/v1/expenses/{$id}")->assertStatus(204);
    }

    /**
     * The manual "Add Entry" (Cash In/Out) screens on both web and mobile only ever offer/send
     * Cash — Bank/UPI money must go through Bank Accounts > Deposit/Withdraw instead. Locking this
     * in here: if the server rules ever change, a client sending "bank"/"upi" here would otherwise
     * silently vanish (mobile drops it as a permanent sync conflict with no visible error).
     */
    public function test_manual_cashbook_entry_rejects_bank_and_upi(): void
    {
        $this->withHeaders($this->h())->postJson('/api/v1/cashbooks', [
            'type' => 'cash_in', 'amount' => 100, 'payment_method' => 'bank', 'description' => 'x',
        ])->assertStatus(422);
        $this->withHeaders($this->h())->postJson('/api/v1/cashbooks', [
            'type' => 'cash_in', 'amount' => 100, 'payment_method' => 'upi', 'description' => 'x',
        ])->assertStatus(422);
        $this->assertSame(0, CashBook::count());
    }

    public function test_manual_cashbook_entries_and_system_entries_protected(): void
    {
        $id = $this->withHeaders($this->h())->postJson('/api/v1/cashbooks', ['type' => 'cash_in', 'amount' => 500, 'description' => 'Owner capital'])
            ->assertStatus(201)->json('id');
        $this->withHeaders($this->h())->postJson('/api/v1/cashbooks', ['type' => 'oops', 'amount' => 5, 'description' => 'x'])->assertStatus(422);
        $this->withHeaders($this->h())->deleteJson("/api/v1/cashbooks/{$id}")->assertStatus(204);

        $this->sale($this->product(), 1);
        $sysId = CashBook::where('reference_type', 'sale')->first()->id;
        $this->withHeaders($this->h())->deleteJson("/api/v1/cashbooks/{$sysId}")->assertStatus(400);
    }

    public function test_bank_accounts_endpoint_is_read_only(): void
    {
        $this->withHeaders($this->h())->getJson('/api/v1/bank-accounts')->assertStatus(200);
        $this->withHeaders($this->h())->postJson('/api/v1/bank-accounts', ['name' => 'x'])->assertStatus(400);
    }

    // ----------------------------------------------- Reports and dashboard

    public function test_dashboard_and_reports_reconcile_with_transactions(): void
    {
        $p = $this->product();
        $c = $this->customer();
        $s = $this->supplier();

        $this->sale($p, 2, 'Cash');                 // +200 cash
        $this->sale($p, 3, 'Credit', $c);           // +300 due
        $this->withHeaders($this->h())->postJson("/api/v1/customers/{$c->id}/collect-payment", ['amount' => 100, 'payment_method' => 'Cash']); // +100 cash, due 200
        $this->withHeaders($this->h())->postJson('/api/v1/purchases', [
            'supplier_id' => $s->id, 'total_amount' => 400, 'paid_amount' => 150, 'payment_type' => 'Cash',
            'items' => [['product_id' => $p->id, 'quantity' => 4, 'purchase_price' => 100]],
        ]);                                         // -150 cash, supplier due 250
        $this->withHeaders($this->h())->postJson('/api/v1/expenses', ['amount' => 50, 'payment_method' => 'cash', 'description' => 'Tea']); // -50 cash

        $d = $this->withHeaders($this->h())->getJson('/api/v1/dashboard')->assertStatus(200);
        $this->assertEquals(500, $d->json('today_sales'));
        $this->assertEquals(400, $d->json('today_purchases'));
        $this->assertEquals(100, $d->json('cash_balance'));   // 200 + 100 - 150 - 50
        $this->assertEquals(200, $d->json('customer_due'));
        $this->assertEquals(250, $d->json('supplier_due'));

        $r = $this->withHeaders($this->h())->getJson('/api/v1/reports')->assertStatus(200);
        $this->assertEquals(500, $r->json('total_sales'));
        $this->assertEquals(400, $r->json('total_purchases'));
        $this->assertEquals(50, $r->json('total_expenses'));
        $this->assertEquals(50, $r->json('net_profit'));
    }

    public function test_returned_and_cancelled_sales_leave_reports_and_dashboard(): void
    {
        $p = $this->product();
        $keep = $this->sale($p, 1)->json('id');
        $ret = $this->sale($p, 1)->json('id');
        $can = $this->sale($p, 1)->json('id');
        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$ret}/return", ['refund_method' => 'cash']);
        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$can}/cancel", ['cancellation_reason' => 'not needed']);
        $this->assertEquals(100, $this->withHeaders($this->h())->getJson('/api/v1/dashboard')->json('today_sales'));
        $this->assertEquals(1, $this->withHeaders($this->h())->getJson('/api/v1/reports')->json('sales_count'));
    }

    public function test_reports_date_filter(): void
    {
        $p = $this->product();
        $this->sale($p, 1, 'Cash', null, ['sale_date' => now()->subDays(40)->toDateTimeString()]);
        $this->sale($p, 1);
        $all = $this->withHeaders($this->h())->getJson('/api/v1/reports?start_date=' . now()->subDays(60)->toDateString())->json();
        $this->assertEquals(2, $all['sales_count']);
        $cur = $this->withHeaders($this->h())->getJson('/api/v1/reports')->json();
        $this->assertEquals(1, $cur['sales_count']);
    }

    // -------------------------------------------- Invoices, sync, security

    public function test_invoice_pdf_is_generated_for_sale_and_purchase(): void
    {
        $p = $this->product();
        $saleId = $this->sale($p, 1)->json('id');
        $r = $this->withHeaders($this->h())->get("/api/v1/sales/{$saleId}/invoice");
        $this->assertLessThan(500, $r->status(), 'sale invoice generation failed: ' . substr($r->getContent(), 0, 300));

        $purId = $this->withHeaders($this->h())->postJson('/api/v1/purchases', [
            'total_amount' => 100, 'payment_type' => 'Cash',
            'items' => [['product_id' => $p->id, 'quantity' => 1, 'purchase_price' => 100]],
        ])->json('id');
        $r = $this->withHeaders($this->h())->get("/api/v1/purchases/{$purId}/invoice");
        $this->assertLessThan(500, $r->status(), 'purchase invoice generation failed: ' . substr($r->getContent(), 0, 300));
    }

    public function test_sync_batch_creates_records_and_is_replay_safe(): void
    {
        $r = $this->withHeaders($this->h())->postJson('/api/v1/sync/batch', ['operations' => [
            ['resource' => 'customers', 'action' => 'create', 'op_id' => 'op-1', 'data' => ['name' => 'Synced']],
        ]])->assertStatus(200);
        $this->assertNotEmpty($r->json('results'));
        $this->assertEquals(1, Customer::where('name', 'Synced')->count());
    }

    public function test_updated_since_returns_soft_deleted_rows_for_sync(): void
    {
        $c = $this->customer();
        $c->delete();
        $rows = $this->withHeaders($this->h())->getJson('/api/v1/customers?updated_since=' . now()->subDay()->toIso8601String())->json();
        $this->assertCount(1, $rows);
    }

    public function test_other_shops_data_is_not_accessible(): void
    {
        $other = User::factory()->create();
        $otherShop = Shop::create(['owner_id' => $other->id, 'name' => 'Other', 'status' => 'active']);
        $theirs = Product::create(['shop_id' => $otherShop->id, 'name' => 'Secret', 'stock' => 1, 'selling_price' => 1, 'purchase_price' => 1]);

        // Cannot scope into someone else's shop
        $this->withHeaders(['X-Shop-ID' => $otherShop->id])->getJson('/api/v1/products')->assertStatus(403);
        // Cannot fetch their record through own shop scope
        $this->withHeaders($this->h())->getJson("/api/v1/products/{$theirs->id}")->assertStatus(404);
        $this->withHeaders($this->h())->deleteJson("/api/v1/products/{$theirs->id}")->assertStatus(404);
        // Cannot sell their product
        $this->sale($theirs, 1)->assertStatus(422);
        $this->assertEquals(1, $theirs->fresh()->stock);
        $this->withHeaders($this->h())->postJson('/api/v1/purchases', [
            'total_amount' => 10, 'payment_type' => 'Cash',
            'items' => [['product_id' => $theirs->id, 'quantity' => 1, 'purchase_price' => 10]],
        ])->assertStatus(422);
        $theirCustomer = Customer::create(['shop_id' => $otherShop->id, 'name' => 'X', 'due_amount' => 0]);
        $this->sale($this->product(), 1, 'Credit', $theirCustomer)->assertStatus(422);
    }

    public function test_missing_shop_header_and_missing_token(): void
    {
        $this->getJson('/api/v1/products')->assertStatus(400);
        $this->app['auth']->forgetGuards();
        $this->refreshApplication();
        $this->getJson('/api/v1/products', ['X-Shop-ID' => 1])->assertStatus(401);
    }

    public function test_admin_panel_requires_admin_login(): void
    {
        $this->withoutVite();
        $this->get('/admin/users')->assertRedirect();
        $this->get('/admin/dashboard')->assertRedirect();
        $this->get('/admin/login')->assertStatus(200);
    }

    public function test_public_config_endpoint_and_shop_pages_render(): void
    {
        $this->withoutVite();
        $this->getJson('/api/v1/app-config')->assertStatus(200);
        $this->get('/shop/login')->assertStatus(200);
    }
}
