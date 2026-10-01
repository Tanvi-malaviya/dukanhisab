<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\CashBook;
use App\Models\CreditNote;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

/**
 * Credit notes / store credit were removed: a sales return refunds through cash, bank, UPI or the
 * customer's khata due — never as store credit.
 */
class SalesReturnRefundMethodTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Shop $shop;
    private Customer $customer;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->shop = Shop::create([
            'owner_id' => $this->user->id,
            'name' => 'Test Shop ' . Str::random(5),
            'currency' => 'INR',
            'is_active' => true,
        ]);
        $this->customer = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'Rahul Test Customer',
            'mobile' => '999888' . rand(1000, 9999),
        ]);
        $this->product = Product::create([
            'shop_id' => $this->shop->id,
            'name' => 'Paracetamol 500mg',
            'purchase_price' => 30.00,
            'selling_price' => 50.00,
            'stock' => 100,
        ]);
    }

    private function sell(int $qty, string $paymentType = 'Cash'): int
    {
        return $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Shop-ID' => $this->shop->id])
            ->postJson('/api/v1/sales', [
                'customer_id' => $this->customer->id,
                'subtotal' => 50.00 * $qty,
                'grand_total' => 50.00 * $qty,
                'payment_type' => $paymentType,
                'items' => [['product_id' => $this->product->id, 'quantity' => $qty, 'selling_price' => 50.00]],
            ])->assertStatus(201)->json('id');
    }

    private function returnItems(int $saleId, string $refundMethod, int $qty)
    {
        return $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Shop-ID' => $this->shop->id])
            ->postJson("/api/v1/sales/{$saleId}/return", [
                'refund_method' => $refundMethod,
                'items' => [['product_id' => $this->product->id, 'quantity' => $qty]],
            ]);
    }

    public function test_partial_cash_refund_restores_stock_and_pays_out_cash(): void
    {
        $saleId = $this->sell(5);
        $this->assertEquals(95, $this->product->fresh()->stock);

        $this->returnItems($saleId, 'cash', 2)->assertStatus(200)->assertJsonPath('status', 'Partially Returned');

        $this->assertEquals(97, $this->product->fresh()->stock);
        $refund = CashBook::where('type', 'cash_out')->where('reference_type', 'sale')->first();
        $this->assertEquals(100.00, (float) $refund->amount);
        $this->assertSame('cash', $refund->payment_method);
        $this->assertEquals(0, (float) $this->customer->fresh()->credit_balance);
    }

    public function test_due_adjustment_reduces_khata_due(): void
    {
        $saleId = $this->sell(4, 'Credit');
        $this->assertEquals(200.00, (float) $this->customer->fresh()->due_amount);

        $this->returnItems($saleId, 'due_adjustment', 1)->assertStatus(200);

        $this->assertEquals(150.00, (float) $this->customer->fresh()->due_amount);
        $this->assertEquals(97, $this->product->fresh()->stock);
    }

    public function test_credit_note_refund_is_rejected_and_changes_nothing(): void
    {
        $saleId = $this->sell(5);

        $this->returnItems($saleId, 'credit_note', 2)->assertStatus(422);

        $this->assertEquals(95, $this->product->fresh()->stock);
        $this->assertEquals(0, (float) $this->customer->fresh()->credit_balance);
        $this->assertEquals(0, CreditNote::count());
    }
}
