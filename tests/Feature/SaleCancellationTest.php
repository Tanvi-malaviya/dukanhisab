<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\CashBook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class SaleCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $shop;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->shop = Shop::create([
            'owner_id' => $this->user->id,
            'name' => 'Cancel Test Shop',
            'status' => 'active',
        ]);
        Sanctum::actingAs($this->user);
    }

    public function test_cancel_cash_sale_rolls_back_stock_and_posts_cashbook_reversal()
    {
        $product = Product::create([
            'shop_id' => $this->shop->id,
            'name' => 'Item Alpha',
            'stock' => 10,
            'selling_price' => 100,
            'purchase_price' => 70,
        ]);

        // Create Sale
        $saleResponse = $this->withHeaders(['X-Shop-ID' => $this->shop->id])
            ->postJson('/api/v1/sales', [
                'subtotal' => 200,
                'grand_total' => 200,
                'payment_type' => 'Cash',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                        'selling_price' => 100,
                    ]
                ],
            ]);
        $saleResponse->assertStatus(201);
        $saleId = $saleResponse->json('id');

        // Stock decreased to 8
        $this->assertEquals(8, $product->fresh()->stock);

        // Cancel Sale
        $cancelResponse = $this->withHeaders(['X-Shop-ID' => $this->shop->id])
            ->postJson("/api/v1/sales/{$saleId}/cancel", [
                'cancellation_reason' => 'Customer requested cancellation',
            ]);

        $cancelResponse->assertStatus(200);
        $this->assertEquals('Cancelled', $cancelResponse->json('status'));
        $this->assertEquals('Customer requested cancellation', $cancelResponse->json('cancellation_reason'));

        // Stock rolled back to 10
        $this->assertEquals(10, $product->fresh()->stock);

        // CashBook has reversal entry
        $reversalEntry = CashBook::where('shop_id', $this->shop->id)
            ->where('reference_type', 'sale_cancel')
            ->where('reference_id', $saleId)
            ->first();

        $this->assertNotNull($reversalEntry);
        $this->assertEquals('cash_out', $reversalEntry->type);
        $this->assertEquals(200, (float)$reversalEntry->amount);

        // Cancelled sale stays visible in index list
        $indexResponse = $this->withHeaders(['X-Shop-ID' => $this->shop->id])
            ->getJson('/api/v1/sales');
        $indexResponse->assertStatus(200);
        $this->assertEquals('Cancelled', $indexResponse->json('0.status'));
    }

    public function test_cancel_credit_sale_rolls_back_customer_due()
    {
        $customer = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'Udhar Customer',
            'mobile' => '9876543210',
            'due_amount' => 0,
        ]);

        $product = Product::create([
            'shop_id' => $this->shop->id,
            'name' => 'Item Beta',
            'stock' => 10,
            'selling_price' => 300,
            'purchase_price' => 200,
        ]);

        $saleResponse = $this->withHeaders(['X-Shop-ID' => $this->shop->id])
            ->postJson('/api/v1/sales', [
                'customer_id' => $customer->id,
                'subtotal' => 300,
                'grand_total' => 300,
                'payment_type' => 'Credit',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'selling_price' => 300,
                    ]
                ],
            ]);
        $saleResponse->assertStatus(201);
        $saleId = $saleResponse->json('id');

        // Due amount is 300
        $this->assertEquals(300, (float)$customer->fresh()->due_amount);

        // Cancel sale
        $cancelResponse = $this->withHeaders(['X-Shop-ID' => $this->shop->id])
            ->postJson("/api/v1/sales/{$saleId}/cancel", [
                'cancellation_reason' => 'Billing error on credit',
            ]);
        $cancelResponse->assertStatus(200);

        // Due amount rolls back to 0
        $this->assertEquals(0, (float)$customer->fresh()->due_amount);
    }

    public function test_hard_delete_is_blocked_for_sale_invoice()
    {
        $sale = Sale::create([
            'shop_id' => $this->shop->id,
            'sale_number' => 'INV-20260911-0001',
            'subtotal' => 100,
            'grand_total' => 100,
            'paid_amount' => 100,
            'payment_type' => 'Cash',
            'status' => 'Completed',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Hard delete is blocked for posted sales invoices. Please cancel the sale instead.");

        $sale->forceDelete();
    }

    public function test_cancel_purchase_rolls_back_stock_and_supplier_due()
    {
        $supplier = Supplier::create([
            'shop_id' => $this->shop->id,
            'name' => 'Test Vendor',
            'mobile' => '9988776655',
            'due_amount' => 0,
        ]);

        $product = Product::create([
            'shop_id' => $this->shop->id,
            'name' => 'Raw Material',
            'stock' => 5,
            'selling_price' => 100,
            'purchase_price' => 50,
        ]);

        // Create credit purchase
        $purchaseResponse = $this->withHeaders(['X-Shop-ID' => $this->shop->id])
            ->postJson('/api/v1/purchases', [
                'supplier_id' => $supplier->id,
                'total_amount' => 500,
                'paid_amount' => 0,
                'payment_type' => 'Credit',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 10,
                        'purchase_price' => 50,
                    ]
                ],
            ]);
        $purchaseResponse->assertStatus(201);
        $purchaseId = $purchaseResponse->json('id');

        // Stock is now 15, supplier due is 500
        $this->assertEquals(15, $product->fresh()->stock);
        $this->assertEquals(500, (float)$supplier->fresh()->due_amount);

        // Cancel purchase
        $cancelResponse = $this->withHeaders(['X-Shop-ID' => $this->shop->id])
            ->postJson("/api/v1/purchases/{$purchaseId}/cancel", [
                'cancellation_reason' => 'Vendor cancelled supply order',
            ]);
        $cancelResponse->assertStatus(200);
        $this->assertEquals('Cancelled', $cancelResponse->json('status'));

        // Stock rolled back to 5
        $this->assertEquals(5, $product->fresh()->stock);

        // Supplier due rolled back to 0
        $this->assertEquals(0, (float)$supplier->fresh()->due_amount);

        // Hard delete blocked on purchase
        $purchase = Purchase::find($purchaseId);
        try {
            $purchase->forceDelete();
            $this->fail("Expected Exception on forceDelete was not thrown");
        } catch (\Exception $e) {
            $this->assertStringContainsString("Hard delete is blocked for posted purchase bills", $e->getMessage());
        }
    }

    public function test_cancelled_sales_and_purchases_excluded_from_reports_pnl()
    {
        $product = Product::create([
            'shop_id' => $this->shop->id,
            'name' => 'Report Item',
            'stock' => 50,
            'selling_price' => 200,
            'purchase_price' => 100,
        ]);

        // Create Completed Sale of 200
        $this->withHeaders(['X-Shop-ID' => $this->shop->id])
            ->postJson('/api/v1/sales', [
                'subtotal' => 200,
                'grand_total' => 200,
                'payment_type' => 'Cash',
                'items' => [['product_id' => $product->id, 'quantity' => 1, 'selling_price' => 200]],
            ]);

        // Create Sale of 500 and cancel it
        $sale2 = $this->withHeaders(['X-Shop-ID' => $this->shop->id])
            ->postJson('/api/v1/sales', [
                'subtotal' => 500,
                'grand_total' => 500,
                'payment_type' => 'Cash',
                'items' => [['product_id' => $product->id, 'quantity' => 2, 'selling_price' => 250]],
            ]);
        $this->withHeaders(['X-Shop-ID' => $this->shop->id])
            ->postJson("/api/v1/sales/{$sale2->json('id')}/cancel", [
                'cancellation_reason' => 'Test cancellation for PnL',
            ]);

        // Fetch reports
        $reportResponse = $this->withHeaders(['X-Shop-ID' => $this->shop->id])
            ->getJson('/api/v1/reports');
        $reportResponse->assertStatus(200);

        // total_sales should only be 200 (not 700)
        $this->assertEquals(200, (float)$reportResponse->json('total_sales'));
        $this->assertEquals(1, $reportResponse->json('sales_count'));
    }
}
