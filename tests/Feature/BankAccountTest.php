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
 * One bank account per shop — its default. Every bank/UPI sale, purchase, due payment, expense and
 * deposit/withdrawal settles into it. Owners can edit its details but can't add or delete accounts.
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

    // ------------------------------------------------------------ single account

    public function test_a_shop_with_no_accounts_gets_a_default_one_lazily(): void
    {
        $r = $this->withHeaders($this->h())->getJson('/api/v1/bank-accounts')->assertStatus(200)->json();
        $this->assertCount(1, $r);
        $this->assertTrue($r[0]['is_default']);
        $this->assertEquals(0, (float) $r[0]['balance']);
    }

    public function test_creating_deleting_and_bulk_deleting_accounts_is_not_allowed(): void
    {
        $default = BankAccount::defaultForShop($this->shop->id);

        $this->withHeaders($this->h())->postJson('/api/v1/bank-accounts', ['name' => 'Savings'])->assertStatus(405);
        $this->withHeaders($this->h())->deleteJson("/api/v1/bank-accounts/{$default->id}")->assertStatus(405);
        $this->withHeaders($this->h())->postJson('/api/v1/bank-accounts/bulk-delete', ['ids' => [$default->id]])->assertStatus(405);

        $this->assertSame(1, BankAccount::where('shop_id', $this->shop->id)->count());
    }

    public function test_the_default_accounts_details_can_be_edited(): void
    {
        $default = BankAccount::defaultForShop($this->shop->id);

        $r = $this->withHeaders($this->h())->putJson("/api/v1/bank-accounts/{$default->id}", [
            'name' => 'Current A/c', 'bank_name' => 'SBI', 'account_number' => '1234', 'ifsc_code' => 'SBIN0001', 'opening_balance' => 500,
        ])->assertStatus(200)->json();

        $this->assertSame('SBI', $r['bank_name']);
        $this->assertEquals(500, (float) $r['balance'], 'opening balance must count even with zero transactions');
        $this->assertTrue($default->fresh()->is_default);
    }

    public function test_editing_cannot_switch_off_the_default(): void
    {
        $default = BankAccount::defaultForShop($this->shop->id);
        $this->withHeaders($this->h())->putJson("/api/v1/bank-accounts/{$default->id}", ['is_default' => false, 'status' => 'inactive'])->assertStatus(200);
        $this->assertTrue($default->fresh()->is_default);
        $this->assertSame('active', $default->fresh()->status);
    }

    public function test_accounts_are_scoped_to_the_shop(): void
    {
        $otherUser = User::factory()->create();
        $otherShop = Shop::create(['owner_id' => $otherUser->id, 'name' => 'Other', 'status' => 'active']);
        BankAccount::defaultForShop($otherShop->id);

        $this->assertCount(1, $this->withHeaders($this->h())->getJson('/api/v1/bank-accounts')->json());
    }

    // ------------------------------------------------------ balances

    public function test_a_transfer_naming_another_account_still_goes_to_the_default(): void
    {
        $default = BankAccount::defaultForShop($this->shop->id);
        $this->withHeaders($this->h())->postJson('/api/v1/bank-transfers', ['type' => 'deposit', 'amount' => 1000, 'bank_account_id' => 999999])->assertStatus(201);
        $this->assertEquals(1000, $default->fresh()->computeBalance());
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

    public function test_dashboard_bank_balance_matches_the_default_account(): void
    {
        $default = BankAccount::defaultForShop($this->shop->id);
        $this->withHeaders($this->h())->postJson('/api/v1/bank-transfers', ['type' => 'deposit', 'amount' => 100])->assertStatus(201);
        $this->withHeaders($this->h())->postJson('/api/v1/bank-transfers', ['type' => 'deposit', 'amount' => 250])->assertStatus(201);

        $dash = $this->withHeaders($this->h())->getJson('/api/v1/dashboard')->json();
        $this->assertEquals(350, $dash['bank_balance']);
        $this->assertEquals(350, $default->fresh()->computeBalance());
    }

    // ------------------------------------------------------ migration

    public function test_merge_migration_folds_extra_accounts_into_the_default(): void
    {
        $default = BankAccount::defaultForShop($this->shop->id);
        $default->update(['opening_balance' => 100]);
        $extra = BankAccount::create(['shop_id' => $this->shop->id, 'name' => 'Savings', 'opening_balance' => 50, 'is_default' => false, 'status' => 'active']);
        CashBook::create(['shop_id' => $this->shop->id, 'type' => 'cash_in', 'amount' => 300, 'payment_method' => 'bank', 'bank_account_id' => $extra->id, 'description' => 'x', 'transaction_date' => now()]);
        CashBook::create(['shop_id' => $this->shop->id, 'type' => 'cash_in', 'amount' => 20, 'payment_method' => 'upi', 'description' => 'y', 'transaction_date' => now()]);

        (require database_path('migrations/2026_10_01_000001_merge_bank_accounts_into_default.php'))->up();

        $this->assertSame(1, BankAccount::where('shop_id', $this->shop->id)->count());
        $this->assertNull(BankAccount::find($extra->id));
        $this->assertEquals(150, (float) $default->fresh()->opening_balance);
        $this->assertEquals(470, $default->fresh()->computeBalance(), '100 + 50 opening, + 300 + 20 entries');
        $this->assertSame(0, CashBook::where('bank_account_id', $extra->id)->count());
    }
}
