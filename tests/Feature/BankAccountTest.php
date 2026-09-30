<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\CashBook;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Real, multiple, shop-owned bank accounts — replacing the single hardcoded "Primary Bank
 * Account" the API used to fabricate. Day-to-day sales/purchases/due-payments settle into the
 * shop's default account automatically; deposits and withdrawals can target any account.
 */
class BankAccountTest extends TestCase
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

    // ------------------------------------------------------------ CRUD

    public function test_a_shop_with_no_accounts_gets_a_default_one_lazily(): void
    {
        $r = $this->withHeaders($this->h())->getJson('/api/v1/bank-accounts')->assertStatus(200)->json();
        $this->assertCount(1, $r);
        $this->assertTrue($r[0]['is_default']);
        $this->assertEquals(0, (float) $r[0]['balance']);
    }

    public function test_creating_a_second_account_does_not_make_it_default(): void
    {
        $this->withHeaders($this->h())->getJson('/api/v1/bank-accounts'); // creates the lazy default
        $r = $this->withHeaders($this->h())->postJson('/api/v1/bank-accounts', [
            'name' => 'Savings', 'account_number' => '1234', 'opening_balance' => 500,
        ])->assertStatus(201)->json();
        $this->assertFalse($r['is_default']);
        $this->assertEquals(500, (float) $r['balance'], 'opening balance must count even with zero transactions');
    }

    public function test_making_an_account_default_unsets_the_previous_default(): void
    {
        $first = BankAccount::defaultForShop($this->shop->id);
        $second = $this->withHeaders($this->h())->postJson('/api/v1/bank-accounts', ['name' => 'Savings'])->json();

        $this->withHeaders($this->h())->putJson("/api/v1/bank-accounts/{$second['id']}", ['make_default' => true])->assertStatus(200);
        $this->assertFalse($first->fresh()->is_default);
        $this->assertTrue(BankAccount::find($second['id'])->is_default);
    }

    public function test_the_default_account_cannot_be_deleted(): void
    {
        $default = BankAccount::defaultForShop($this->shop->id);
        $this->withHeaders($this->h())->deleteJson("/api/v1/bank-accounts/{$default->id}")->assertStatus(400);
    }

    public function test_an_account_with_transactions_cannot_be_deleted(): void
    {
        $acc = $this->withHeaders($this->h())->postJson('/api/v1/bank-accounts', ['name' => 'Savings'])->json();
        $this->withHeaders($this->h())->postJson('/api/v1/bank-transfers', ['type' => 'deposit', 'amount' => 100, 'bank_account_id' => $acc['id']]);
        $this->withHeaders($this->h())->deleteJson("/api/v1/bank-accounts/{$acc['id']}")->assertStatus(400);
    }

    public function test_an_unused_non_default_account_can_be_deleted(): void
    {
        BankAccount::defaultForShop($this->shop->id);
        $acc = $this->withHeaders($this->h())->postJson('/api/v1/bank-accounts', ['name' => 'Unused'])->json();
        $this->withHeaders($this->h())->deleteJson("/api/v1/bank-accounts/{$acc['id']}")->assertStatus(204);
    }

    public function test_accounts_are_scoped_to_the_shop(): void
    {
        $otherUser = User::factory()->create();
        $otherShop = Shop::create(['owner_id' => $otherUser->id, 'name' => 'Other', 'status' => 'active']);
        BankAccount::defaultForShop($otherShop->id);

        $this->assertCount(1, $this->withHeaders($this->h())->getJson('/api/v1/bank-accounts')->json());
    }

    // ------------------------------------------------------ balances

    public function test_deposit_and_withdraw_change_only_the_targeted_accounts_balance(): void
    {
        $main = BankAccount::defaultForShop($this->shop->id);
        $savings = $this->withHeaders($this->h())->postJson('/api/v1/bank-accounts', ['name' => 'Savings'])->json();

        $this->withHeaders($this->h())->postJson('/api/v1/bank-transfers', ['type' => 'deposit', 'amount' => 1000, 'bank_account_id' => $savings['id']]);

        $accounts = collect($this->withHeaders($this->h())->getJson('/api/v1/bank-accounts')->json())->keyBy('id');
        $this->assertEquals(1000, (float) $accounts[$savings['id']]['balance']);
        $this->assertEquals(0, (float) $accounts[$main->id]['balance']);
    }

    public function test_deposit_without_an_account_id_uses_the_default(): void
    {
        $default = BankAccount::defaultForShop($this->shop->id);
        $this->withHeaders($this->h())->postJson('/api/v1/bank-transfers', ['type' => 'deposit', 'amount' => 300])->assertStatus(201);
        $this->assertEquals(300, $default->fresh()->computeBalance());
    }

    public function test_a_bank_sale_settles_into_the_default_account(): void
    {
        $p = $this->product();
        $default = BankAccount::defaultForShop($this->shop->id);
        $this->withHeaders($this->h())->postJson('/api/v1/sales', [
            'subtotal' => 200, 'grand_total' => 200, 'payment_type' => 'Bank',
            'items' => [['product_id' => $p->id, 'quantity' => 1, 'selling_price' => 200]],
        ])->assertStatus(201);

        $this->assertEquals(200, $default->fresh()->computeBalance());
        $entry = CashBook::where('reference_type', 'sale')->first();
        $this->assertEquals($default->id, $entry->bank_account_id);
    }

    public function test_a_upi_purchase_and_a_customer_upi_payment_also_settle_into_the_default_account(): void
    {
        $p = $this->product();
        $c = Customer::create(['shop_id' => $this->shop->id, 'name' => 'C', 'mobile' => '9000000001', 'due_amount' => 500]);
        $s = Supplier::create(['shop_id' => $this->shop->id, 'name' => 'S', 'mobile' => '8000000001', 'due_amount' => 0]);
        $default = BankAccount::defaultForShop($this->shop->id);

        $this->withHeaders($this->h())->postJson("/api/v1/customers/{$c->id}/collect-payment", ['amount' => 500, 'payment_method' => 'UPI'])->assertStatus(200);
        $this->withHeaders($this->h())->postJson('/api/v1/purchases', [
            'supplier_id' => $s->id, 'total_amount' => 300, 'payment_type' => 'UPI',
            'items' => [['product_id' => $p->id, 'quantity' => 1, 'purchase_price' => 300]],
        ])->assertStatus(201);

        $this->assertEquals(200, $default->fresh()->computeBalance(), '+500 customer payment - 300 purchase');
    }

    public function test_a_cash_sale_does_not_touch_any_bank_account(): void
    {
        $p = $this->product();
        $default = BankAccount::defaultForShop($this->shop->id);
        $this->withHeaders($this->h())->postJson('/api/v1/sales', [
            'subtotal' => 200, 'grand_total' => 200, 'payment_type' => 'Cash',
            'items' => [['product_id' => $p->id, 'quantity' => 1, 'selling_price' => 200]],
        ])->assertStatus(201);
        $this->assertEquals(0, $default->fresh()->computeBalance());
    }

    public function test_dashboard_bank_balance_is_the_total_across_all_accounts(): void
    {
        $main = BankAccount::defaultForShop($this->shop->id);
        $savings = $this->withHeaders($this->h())->postJson('/api/v1/bank-accounts', ['name' => 'Savings'])->json();
        $this->withHeaders($this->h())->postJson('/api/v1/bank-transfers', ['type' => 'deposit', 'amount' => 100])->assertStatus(201);
        $this->withHeaders($this->h())->postJson('/api/v1/bank-transfers', ['type' => 'deposit', 'amount' => 250, 'bank_account_id' => $savings['id']])->assertStatus(201);

        $dash = $this->withHeaders($this->h())->getJson('/api/v1/dashboard')->json();
        $this->assertEquals(350, $dash['bank_balance']);
    }

    public function test_deleting_an_accounts_default_flag_requires_another_account_to_take_it_first(): void
    {
        // Sanity: with only one account, it must always stay default — confirmed via the delete guard.
        $only = BankAccount::defaultForShop($this->shop->id);
        $this->withHeaders($this->h())->deleteJson("/api/v1/bank-accounts/{$only->id}")->assertStatus(400);
        $this->assertSame(1, BankAccount::where('shop_id', $this->shop->id)->count());
    }
}
