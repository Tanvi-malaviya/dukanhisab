<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\CashBook;

class PurchasePartialAndDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_with_scheme_discount_reduces_cogs_and_partial_purchase_splits_payable()
    {
        $user = User::factory()->create();
        $shop = Shop::create([
            'owner_id' => $user->id,
            'name' => 'Test Shop ABC',
            'currency' => 'INR',
            'is_active' => true,
        ]);
        $user->update(['shop_id' => $shop->id]);

        $supplier = Supplier::create([
            'shop_id' => $shop->id,
            'name' => 'ABC Wholesalers',
            'mobile' => '9876543210',
            'due_amount' => 0.00,
        ]);

        $product = Product::create([
            'shop_id' => $shop->id,
            'name' => 'Notebook 200 Pages',
            'barcode' => '8901234567890',
            'purchase_price' => 50.00,
            'selling_price' => 80.00,
            'stock' => 10,
        ]);

        $headers = ['X-Shop-Id' => $shop->id];

        // 1. Test Purchase with Scheme Discount reducing COGS
        // Buy 10 units at ₹50 list = ₹500, but supplier gives ₹100 scheme discount.
        // Effective unit cost = (500 - 100) / 10 = ₹40.00.
        $responseDiscountPurchase = $this->actingAs($user, 'sanctum')->withHeaders($headers)->postJson('/api/v1/purchases', [
            'supplier_id' => $supplier->id,
            'total_amount' => 400.00,
            'discount' => 100.00,
            'paid_amount' => 400.00,
            'payment_type' => 'Cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'purchase_price' => 50.00,
                    'discount' => 100.00,
                ]
            ],
        ]);

        $responseDiscountPurchase->assertStatus(201);
        $this->assertEquals(20, $product->fresh()->stock, 'Stock should increase from 10 to 20');
        $this->assertEquals(40.00, (float)$product->fresh()->purchase_price, 'Product purchase_price (COGS) should be reduced to 40.00 after scheme discount');
        $this->assertEquals(0.00, (float)$supplier->fresh()->due_amount, 'Supplier due should be 0 for fully paid cash purchase');

        $cashOutEntry = CashBook::where('shop_id', $shop->id)->where('type', 'cash_out')->latest('id')->first();
        $this->assertNotNull($cashOutEntry);
        $this->assertEquals(400.00, (float)$cashOutEntry->amount, 'CashBook should record exactly the paid amount (400.00)');

        // 2. Test Partial Purchase: Total ₹5,000, Paid ₹2,000 via Bank, Remaining ₹3,000 due
        $responsePartialPurchase = $this->actingAs($user, 'sanctum')->withHeaders($headers)->postJson('/api/v1/purchases', [
            'supplier_id' => $supplier->id,
            'total_amount' => 5000.00,
            'discount' => 0.00,
            'paid_amount' => 2000.00,
            'payment_type' => 'Bank',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 100,
                    'purchase_price' => 50.00,
                    'discount' => 0.00,
                ]
            ],
        ]);

        $responsePartialPurchase->assertStatus(201);
        $partialPurchaseData = $responsePartialPurchase->json();
        $this->assertEquals('Partially Paid', $partialPurchaseData['status'], 'Status should be Partially Paid');
        $this->assertEquals(2000.00, (float)$partialPurchaseData['paid_amount']);
        $this->assertEquals(3000.00, (float)$supplier->fresh()->due_amount, 'Supplier due should be incremented by unpaid portion (3000.00)');

        $bankOutEntry = CashBook::where('shop_id', $shop->id)->where('type', 'cash_out')->latest('id')->first();
        $this->assertEquals(2000.00, (float)$bankOutEntry->amount, 'CashBook should only log the paid 2000.00');
        $this->assertEquals('bank', $bankOutEntry->payment_method);

        // 3. Test Partial Return on this purchase: return 20 units (worth ₹1,000)
        // Since supplier due is ₹3,000, returning ₹1,000 should reduce supplier due to ₹2,000 (no cash out)
        $purchaseId = $partialPurchaseData['id'];
        $responseReturn = $this->actingAs($user, 'sanctum')->withHeaders($headers)->postJson("/api/v1/purchases/{$purchaseId}/return", [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 20,
                ]
            ],
        ]);

        $responseReturn->assertStatus(200);
        $this->assertEquals(2000.00, (float)$supplier->fresh()->due_amount, 'Supplier due should decrease from 3000 to 2000 after 1000 return');
    }
}
