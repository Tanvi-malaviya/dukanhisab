<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * PUT /sales/{id} used to reject any item edit unless the sale was already "Completed" — so a
 * Credit sale sitting at "Unpaid" or "Partially Paid" (the two statuses this screen actually
 * exists to manage) could never have its items edited at all, with a misleading "returned sale"
 * error message. These lock in that Unpaid/Partially Paid sales are now editable, that a
 * cancelled/returned sale still correctly isn't, and that editing recomputes the due amount
 * against store credit rather than the full grand total (which would have double-reverted for
 * a partially store-credit-covered sale).
 */
class EditUnpaidSaleTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->shop = Shop::create(['owner_id' => $user->id, 'name' => 'Edit Test Shop', 'status' => 'active']);
        Sanctum::actingAs($user);
    }

    private function h(): array
    {
        return ['X-Shop-ID' => $this->shop->id];
    }

    private function product(): Product
    {
        return Product::create([
            'shop_id' => $this->shop->id, 'name' => 'P' . uniqid(), 'stock' => 50,
            'selling_price' => 100, 'purchase_price' => 60,
        ]);
    }

    public function test_editing_items_of_an_unpaid_credit_sale_is_now_allowed(): void
    {
        $p = $this->product();
        $c = Customer::create(['shop_id' => $this->shop->id, 'name' => 'Cust', 'mobile' => '9' . rand(1e8, 9.9e8), 'due_amount' => 0]);

        $saleId = $this->withHeaders($this->h())->postJson('/api/v1/sales', [
            'customer_id' => $c->id, 'subtotal' => 200, 'grand_total' => 200, 'payment_type' => 'Credit',
            'items' => [['product_id' => $p->id, 'quantity' => 2, 'selling_price' => 100]],
        ])->json();
        $this->assertEquals('Unpaid', $saleId['status']);
        $this->assertEquals(200, (float) $c->fresh()->due_amount);

        $r = $this->withHeaders($this->h())->putJson("/api/v1/sales/{$saleId['id']}", [
            'subtotal' => 300, 'grand_total' => 300,
            'items' => [['product_id' => $p->id, 'quantity' => 3, 'selling_price' => 100]],
        ]);

        $r->assertStatus(200);
        $this->assertEquals('Unpaid', $r->json('status'));
        // Due should now reflect the new grand total, not the old one stacked on top.
        $this->assertEquals(300, (float) $c->fresh()->due_amount);
        $this->assertEquals(47, $p->fresh()->stock);
    }

    public function test_editing_items_of_a_partially_paid_sale_reverts_and_reapplies_due_net_of_store_credit(): void
    {
        $p = $this->product();
        $c = Customer::create(['shop_id' => $this->shop->id, 'name' => 'Cust2', 'mobile' => '9' . rand(1e8, 9.9e8), 'due_amount' => 0]);

        $saleId = $this->withHeaders($this->h())->postJson('/api/v1/sales', [
            'customer_id' => $c->id, 'subtotal' => 200, 'grand_total' => 200, 'payment_type' => 'Credit',
            'items' => [['product_id' => $p->id, 'quantity' => 2, 'selling_price' => 100]],
        ])->json();
        // New sales can't use store credit any more; recreate a legacy sale that used ₹50 of it:
        // 200 grand total - 50 store credit = 150 due.
        \App\Models\Sale::where('id', $saleId['id'])->update(['store_credit' => 50, 'status' => 'Partially Paid']);
        $c->update(['due_amount' => 150]);

        $r = $this->withHeaders($this->h())->putJson("/api/v1/sales/{$saleId['id']}", [
            'subtotal' => 200, 'grand_total' => 200,
            'items' => [['product_id' => $p->id, 'quantity' => 2, 'selling_price' => 100]],
        ]);

        $r->assertStatus(200);
        $this->assertEquals('Partially Paid', $r->json('status'));
        // Editing shouldn't touch the store credit already applied — due stays 150, not
        // erroneously reverted to 200 (which the old "subtract full grand_total" bug did).
        $this->assertEquals(150, (float) $c->fresh()->due_amount);
    }

    public function test_editing_items_of_a_returned_sale_is_still_rejected(): void
    {
        $p = $this->product();
        $saleId = $this->withHeaders($this->h())->postJson('/api/v1/sales', [
            'subtotal' => 100, 'grand_total' => 100, 'payment_type' => 'Cash',
            'items' => [['product_id' => $p->id, 'quantity' => 1, 'selling_price' => 100]],
        ])->json('id');

        $this->withHeaders($this->h())->putJson("/api/v1/sales/{$saleId}", ['status' => 'Returned'])->assertStatus(200);

        $this->withHeaders($this->h())->putJson("/api/v1/sales/{$saleId}", [
            'items' => [['product_id' => $p->id, 'quantity' => 1, 'selling_price' => 100]],
        ])->assertStatus(400);
    }
}
