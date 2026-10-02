<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\CashBook;
use App\Models\ContainerEntry;
use App\Models\ContainerMovement;
use App\Models\ContainerType;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Returnable containers module: deposits are a liability kept out of sales, returns settle the
 * oldest invoices first at the deposit actually paid, and every money effect is reversible.
 */
class ReturnableContainersTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Shop $shop;
    private Customer $customer;
    private ContainerType $tub;
    private Product $iceCream;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->shop = Shop::create(['owner_id' => $this->user->id, 'name' => 'Ice Cream Depot', 'status' => 'active']);
        $this->shop->forceFill(['features' => ['containers']])->save();
        $this->customer = Customer::create(['shop_id' => $this->shop->id, 'name' => 'Ramesh', 'due_amount' => 0]);
        $this->tub = ContainerType::create(['shop_id' => $this->shop->id, 'name' => 'Tub', 'deposit_amount' => 200]);
        $this->iceCream = Product::create([
            'shop_id' => $this->shop->id, 'name' => 'Vanilla 5L', 'stock' => 100,
            'selling_price' => 500, 'purchase_price' => 300,
            'container_type_id' => $this->tub->id, 'containers_per_unit' => 1,
        ]);

        Sanctum::actingAs($this->user);
    }

    private function api(string $method, string $uri, array $data = [])
    {
        return $this->withHeaders(['X-Shop-ID' => $this->shop->id])->json($method, $uri, $data);
    }

    private function sellWithTubs(int $qty, string $paymentType = 'Cash', array $containers = [])
    {
        return $this->api('POST', '/api/v1/sales', [
            'customer_id' => $this->customer->id,
            'subtotal' => 500 * $qty,
            'grand_total' => 500 * $qty,
            'payment_type' => $paymentType,
            'items' => [['product_id' => $this->iceCream->id, 'quantity' => $qty, 'selling_price' => 500]],
            'containers' => $containers + [
                'given' => [['container_type_id' => $this->tub->id, 'quantity' => $qty]],
                'settlement_method' => 'cash',
            ],
        ]);
    }

    private function returnTubs(array $line, string $method = 'cash')
    {
        return $this->api('POST', '/api/v1/containers/entries', [
            'customer_id' => $this->customer->id,
            'returns' => [$line + ['container_type_id' => $this->tub->id]],
            'settlement_method' => $method,
        ]);
    }

    private function pendingOnSale(int $saleId): int
    {
        return (int) ContainerMovement::where('sale_id', $saleId)->openLots()->get()->sum('pending_quantity');
    }

    // ------------------------------------------------------------ feature gate

    public function test_shops_without_the_module_cannot_use_container_endpoints(): void
    {
        $this->shop->forceFill(['features' => null])->save();

        $this->api('GET', '/api/v1/containers/summary')->assertStatus(403)->assertJsonPath('error', 'feature_disabled');
        $this->sellWithTubs(1)->assertStatus(422)->assertJsonValidationErrors('containers');
        $this->assertSame(0, Sale::count());
    }

    public function test_admin_toggles_the_module_per_shop_and_it_reaches_the_profile(): void
    {
        $admin = Admin::create(['name' => 'Root', 'email' => 'root@test.com', 'password' => 'secret123', 'role' => 'superadmin', 'status' => 'active']);
        $this->shop->forceFill(['features' => null])->save();

        $this->actingAs($admin, 'admin')
            ->post(route('admin.shops.features', $this->shop->id), ['feature' => 'containers', 'enabled' => 1])
            ->assertRedirect();
        $this->assertTrue($this->shop->fresh()->hasFeature('containers'));

        Sanctum::actingAs($this->user);
        $this->withHeaders(['X-Shop-ID' => $this->shop->id])->getJson('/api/v1/shopowner/profile')
            ->assertJsonPath('shop.features', ['containers']);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.shops.features', $this->shop->id), ['feature' => 'containers', 'enabled' => 0])
            ->assertRedirect();
        $this->assertFalse($this->shop->fresh()->hasFeature('containers'));
    }

    // ------------------------------------------------------------ deposit stays out of sales

    public function test_sale_deposit_is_a_separate_cashbook_entry_not_part_of_the_sale(): void
    {
        $sale = $this->sellWithTubs(2)->assertCreated()->json();

        $this->assertEquals(1000, $sale['grand_total']);
        $this->assertCount(1, $sale['container_lots']);
        $this->assertSame(2, $sale['container_lots'][0]['pending_quantity']);

        $this->assertEquals(1000, CashBook::where('reference_type', 'sale')->sum('amount'));
        $deposit = CashBook::where('reference_type', 'container_deposit')->firstOrFail();
        $this->assertEquals(400, $deposit->amount);
        $this->assertSame('cash_in', $deposit->type);

        // Expenses never include container entries.
        $this->assertSame(0, CashBook::expenses()->count());

        $this->api('GET', '/api/v1/containers/summary')
            ->assertOk()
            ->assertJsonPath('containers_out', 2)
            ->assertJsonPath('deposit_held', 400);
    }

    public function test_credit_sale_still_collects_deposit_separately_from_the_due(): void
    {
        $this->sellWithTubs(1, 'Credit')->assertCreated();

        $this->assertEquals(500, $this->customer->fresh()->due_amount);
        $this->assertEquals(200, CashBook::where('reference_type', 'container_deposit')->sum('amount'));
    }

    // ------------------------------------------------------------ bulk / partial returns

    public function test_one_return_settles_containers_from_three_invoices(): void
    {
        $inv1 = $this->sellWithTubs(1)->json('id');
        $inv2 = $this->sellWithTubs(5)->json('id');
        $inv3 = $this->sellWithTubs(14)->json('id');

        $entry = $this->returnTubs(['returned' => 20])->assertCreated()->json();

        $this->assertSame('return', $entry['type']);
        $this->assertStringStartsWith('CR-', $entry['entry_number']);
        $this->assertEquals(4000, $entry['refund_amount']);
        $this->assertEquals(-4000, $entry['net_amount']);
        $this->assertSame(0, $this->pendingOnSale($inv1) + $this->pendingOnSale($inv2) + $this->pendingOnSale($inv3));

        // One cash refund for the whole return.
        $refunds = CashBook::where('reference_type', 'container_refund')->get();
        $this->assertCount(1, $refunds);
        $this->assertEquals(4000, $refunds->first()->amount);

        $this->api('GET', '/api/v1/sales/' . $inv3)->assertJsonCount(1, 'container_lots')
            ->assertJsonPath('container_lots.0.pending_quantity', 0);
    }

    public function test_partial_return_closes_oldest_invoices_first(): void
    {
        $inv1 = $this->sellWithTubs(1)->json('id');
        $inv2 = $this->sellWithTubs(5)->json('id');
        $inv3 = $this->sellWithTubs(14)->json('id');

        $this->returnTubs(['returned' => 12])->assertCreated()->assertJsonPath('refund_amount', 2400);

        $this->assertSame(0, $this->pendingOnSale($inv1));
        $this->assertSame(0, $this->pendingOnSale($inv2));
        $this->assertSame(8, $this->pendingOnSale($inv3));
    }

    public function test_return_can_target_a_chosen_invoice(): void
    {
        $inv1 = $this->sellWithTubs(1)->json('id');
        $inv2 = $this->sellWithTubs(5)->json('id');

        $this->returnTubs(['returned' => 3, 'sale_id' => $inv2])->assertCreated();

        $this->assertSame(1, $this->pendingOnSale($inv1));
        $this->assertSame(2, $this->pendingOnSale($inv2));
    }

    public function test_refund_uses_the_deposit_actually_paid_on_each_invoice(): void
    {
        $this->tub->update(['deposit_amount' => 180]);
        $this->sellWithTubs(1);
        $this->tub->update(['deposit_amount' => 200]);
        $this->sellWithTubs(5);

        $this->returnTubs(['returned' => 6])->assertCreated()->assertJsonPath('refund_amount', 1180);
    }

    public function test_cannot_return_more_than_the_customer_holds(): void
    {
        $this->sellWithTubs(2);

        $this->returnTubs(['returned' => 3])->assertStatus(422)->assertJsonValidationErrors('containers');
        $this->assertSame(0, CashBook::where('reference_type', 'container_refund')->count());
    }

    // ------------------------------------------------------------ damaged / lost

    public function test_damage_deduction_and_lost_container_become_forfeit_income(): void
    {
        $this->sellWithTubs(4);

        $entry = $this->returnTubs(['returned' => 2, 'damaged' => 1, 'damage_deduction' => 50, 'lost' => 1])
            ->assertCreated()->json();

        // 2×200 + (200 − 50) refunded; 50 deduction + 200 lost kept.
        $this->assertEquals(550, $entry['refund_amount']);
        $this->assertEquals(250, $entry['forfeit_amount']);
        $this->assertEquals(550, CashBook::where('reference_type', 'container_refund')->sum('amount'));

        $this->api('GET', '/api/v1/containers/summary')
            ->assertJsonPath('containers_out', 0)
            ->assertJsonPath('deposit_held', 0)
            ->assertJsonPath('forfeit_income_total', 250)
            ->assertJsonPath('types.0.lost', 1);
    }

    public function test_damage_deduction_cannot_exceed_the_deposit(): void
    {
        $this->sellWithTubs(1);

        $this->returnTubs(['damaged' => 1, 'damage_deduction' => 250])->assertStatus(422);
    }

    // ------------------------------------------------------------ settlement options

    public function test_refund_adjusted_against_dues_moves_no_cash(): void
    {
        $this->sellWithTubs(2, 'Credit'); // due 1000, deposit 400 paid in cash

        $this->returnTubs(['returned' => 2], 'due_adjustment')->assertCreated()
            ->assertJsonPath('settlement_method', 'due_adjustment');

        $this->assertEquals(600, $this->customer->fresh()->due_amount);
        $this->assertSame(0, CashBook::where('reference_type', 'container_refund')->count());
        $this->assertSame('Partially Paid', Sale::first()->status);
    }

    public function test_due_adjustment_larger_than_the_due_is_rejected(): void
    {
        $this->sellWithTubs(2); // paid in cash, no due

        $this->returnTubs(['returned' => 2], 'due_adjustment')->assertStatus(422)->assertJsonValidationErrors('settlement_method');
        $this->assertSame(2, (int) ContainerMovement::openLots()->get()->sum('pending_quantity'));
    }

    public function test_exchange_at_billing_moves_no_deposit(): void
    {
        $this->sellWithTubs(5);

        $sale = $this->sellWithTubs(5, 'Cash', ['returned' => [['container_type_id' => $this->tub->id, 'quantity' => 5]]])
            ->assertCreated()->json();

        $entry = ContainerEntry::where('sale_id', $sale['id'])->firstOrFail();
        $this->assertSame('exchange', $entry->type);
        $this->assertEquals(0, $entry->net_amount);
        $this->assertSame(1, CashBook::where('reference_type', 'container_deposit')->count()); // only the first sale

        // The new tubs are what is pending now, on the new invoice.
        $this->assertSame(5, $this->pendingOnSale($sale['id']));
        $this->api('GET', '/api/v1/containers/summary')->assertJsonPath('containers_out', 5);
    }

    public function test_deposit_not_collected_tracks_count_only(): void
    {
        $this->sellWithTubs(3, 'Cash', ['collect_deposit' => false]);
        $this->assertSame(0, CashBook::where('reference_type', 'container_deposit')->count());

        $this->returnTubs(['returned' => 3], 'cash')->assertCreated()
            ->assertJsonPath('refund_amount', 0)
            ->assertJsonPath('settlement_method', 'none');
        $this->assertSame(0, CashBook::where('reference_type', 'container_refund')->count());
    }

    public function test_opening_balance_records_deposit_held_without_cash(): void
    {
        $this->api('POST', '/api/v1/containers/entries', [
            'customer_id' => $this->customer->id,
            'opening' => true,
            'issues' => [['container_type_id' => $this->tub->id, 'quantity' => 10, 'deposit_per_unit' => 150]],
        ])->assertCreated()->assertJsonPath('type', 'opening')->assertJsonPath('net_amount', 0);

        $this->assertSame(0, CashBook::count());
        $this->api('GET', '/api/v1/containers/customers')
            ->assertJsonPath('0.total_pending', 10)
            ->assertJsonPath('0.deposit_held', 1500);
    }

    // ------------------------------------------------------------ reversal

    public function test_reversing_a_return_reopens_containers_and_reverses_cash(): void
    {
        $inv = $this->sellWithTubs(2)->json('id');
        $return = $this->returnTubs(['returned' => 2])->json();

        $this->api('POST', "/api/v1/containers/entries/{$return['id']}/reverse")->assertCreated()
            ->assertJsonPath('type', 'reversal');

        $this->assertSame(2, $this->pendingOnSale($inv));
        $this->assertEquals(400, CashBook::where('reference_type', 'container_reversal')->where('type', 'cash_in')->sum('amount'));
        $this->api('POST', "/api/v1/containers/entries/{$return['id']}/reverse")->assertStatus(422);
    }

    public function test_reversing_a_due_adjustment_puts_the_due_back(): void
    {
        $this->sellWithTubs(2, 'Credit');
        $return = $this->returnTubs(['returned' => 2], 'due_adjustment')->json();

        $this->api('POST', "/api/v1/containers/entries/{$return['id']}/reverse")->assertCreated();

        $this->assertEquals(1000, $this->customer->fresh()->due_amount);
        $this->assertSame('Unpaid', Sale::first()->status);
    }

    public function test_a_give_with_returned_containers_cannot_be_reversed_first(): void
    {
        $this->sellWithTubs(2);
        $give = ContainerEntry::where('type', 'give')->firstOrFail();
        $this->returnTubs(['returned' => 1]);

        $this->api('POST', "/api/v1/containers/entries/{$give->id}/reverse")->assertStatus(422);
    }

    // ------------------------------------------------------------ guards & stock

    public function test_customer_holding_containers_cannot_be_deleted(): void
    {
        $this->sellWithTubs(1);

        $this->api('DELETE', '/api/v1/customers/' . $this->customer->id)->assertStatus(422);
        $this->assertNotSoftDeleted($this->customer);
    }

    public function test_container_type_with_pending_containers_cannot_be_deleted(): void
    {
        $this->sellWithTubs(1);

        $this->api('DELETE', '/api/v1/container-types/' . $this->tub->id)->assertStatus(422);
    }

    public function test_stock_adjustment_tracks_containers_in_the_shop(): void
    {
        $this->api('POST', "/api/v1/container-types/{$this->tub->id}/adjust-stock", ['quantity' => 50])
            ->assertCreated()->assertJsonPath('container_type.total_owned', 50);
        $this->sellWithTubs(4);
        $this->returnTubs(['lost' => 1]);

        $this->api('GET', '/api/v1/container-types')
            ->assertJsonPath('0.with_customers', 3)
            ->assertJsonPath('0.lost', 1)
            ->assertJsonPath('0.in_shop', 46);

        $this->api('POST', "/api/v1/container-types/{$this->tub->id}/adjust-stock", ['quantity' => -60])->assertStatus(422);
    }

    public function test_product_can_only_link_a_container_type_of_its_own_shop(): void
    {
        $other = Shop::create(['owner_id' => $this->user->id, 'name' => 'Other', 'status' => 'active']);
        $foreign = ContainerType::create(['shop_id' => $other->id, 'name' => 'Can', 'deposit_amount' => 500]);

        $this->api('PUT', '/api/v1/products/' . $this->iceCream->id, ['container_type_id' => $foreign->id])
            ->assertStatus(422)->assertJsonValidationErrors('container_type_id');
        $this->api('PUT', '/api/v1/products/' . $this->iceCream->id, ['container_type_id' => null, 'containers_per_unit' => null])
            ->assertOk()->assertJsonPath('containers_per_unit', 1);
    }

    public function test_invoice_pdf_lists_pending_containers_until_all_are_back(): void
    {
        $inv = $this->sellWithTubs(2)->json('id');
        $controller = app(\App\Http\Controllers\Api\InvoiceApiController::class);
        $build = fn () => (new \ReflectionMethod($controller, 'buildContainerDepositHtml'))
            ->invoke($controller, Sale::find($inv), $this->shop->fresh());

        $this->assertStringContainsString('400.00', $build());
        $this->returnTubs(['returned' => 1]);
        $this->assertStringContainsString('200.00', $build());
        $this->returnTubs(['returned' => 1]);
        $this->assertSame('', $build());
    }
}
