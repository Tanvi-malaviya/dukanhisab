<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Shop;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\CashBook;

class SalePartialPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_partial_payment_and_customer_ledger_flow()
    {
        $user = User::factory()->create();
        $shop = Shop::create([
            'owner_id' => $user->id,
            'name' => 'Test Sales Shop',
            'currency' => 'INR',
            'is_active' => true,
        ]);
        $user->update(['shop_id' => $shop->id]);

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => 'Rahul Sharma',
            'mobile' => '9876500000',
            'due_amount' => 0.00,
        ]);

        $product = Product::create([
            'shop_id' => $shop->id,
            'name' => 'Rice 5kg Bag',
            'barcode' => '8901234000001',
            'purchase_price' => 200.00,
            'selling_price' => 300.00,
            'stock' => 50,
        ]);

        $headers = ['X-Shop-Id' => $shop->id];

        // 1. Fully paid cash sale
        $resFull = $this->actingAs($user, 'sanctum')->withHeaders($headers)->postJson('/api/v1/sales', [
            'customer_id' => $customer->id,
            'subtotal' => 300.00,
            'discount' => 0.00,
            'grand_total' => 300.00,
            'paid_amount' => 300.00,
            'payment_type' => 'Cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'selling_price' => 300.00,
                    'discount' => 0.00,
                ]
            ],
        ]);

        $resFull->assertStatus(201);
        $fullSaleData = $resFull->json();
        $this->assertEquals('Completed', $fullSaleData['status']);
        $this->assertEquals(300.00, (float)$fullSaleData['paid_amount']);
        $this->assertEquals(0.00, (float)$customer->fresh()->due_amount);

        $cashEntry = CashBook::where('shop_id', $shop->id)->where('type', 'cash_in')->latest('id')->first();
        $this->assertNotNull($cashEntry);
        $this->assertEquals(300.00, (float)$cashEntry->amount);

        // 2. Partial sale: total 3,000 (10 units), paid 1,000 via UPI, remaining due 2,000
        $resPartial = $this->actingAs($user, 'sanctum')->withHeaders($headers)->postJson('/api/v1/sales', [
            'customer_id' => $customer->id,
            'subtotal' => 3000.00,
            'discount' => 0.00,
            'grand_total' => 3000.00,
            'paid_amount' => 1000.00,
            'payment_type' => 'UPI',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 10,
                    'selling_price' => 300.00,
                    'discount' => 0.00,
                ]
            ],
        ]);

        $resPartial->assertStatus(201);
        $partialSaleData = $resPartial->json();
        $this->assertEquals('Partially Paid', $partialSaleData['status']);
        $this->assertEquals(1000.00, (float)$partialSaleData['paid_amount']);
        $this->assertEquals(2000.00, (float)$customer->fresh()->due_amount, 'Customer due should increase by unpaid 2000.00');

        $upiEntry = CashBook::where('shop_id', $shop->id)->where('type', 'cash_in')->latest('id')->first();
        $this->assertEquals(1000.00, (float)$upiEntry->amount, 'CashBook should record only actual received 1000.00');
        $this->assertEquals('upi', $upiEntry->payment_method);

        // 3. Validation: Due > 0 requires customer
        $resNoCustomer = $this->actingAs($user, 'sanctum')->withHeaders($headers)->postJson('/api/v1/sales', [
            'customer_id' => null,
            'subtotal' => 300.00,
            'discount' => 0.00,
            'grand_total' => 300.00,
            'paid_amount' => 100.00,
            'payment_type' => 'Cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'selling_price' => 300.00,
                    'discount' => 0.00,
                ]
            ],
        ]);

        $resNoCustomer->assertStatus(422);
        $resNoCustomer->assertJsonValidationErrors(['customer_id']);

        // 4. Customer settling payment updates sale status to Completed
        $resPay = $this->actingAs($user, 'sanctum')->withHeaders($headers)->postJson("/api/v1/customers/{$customer->id}/collect-payment", [
            'amount' => 2000.00,
            'payment_method' => 'cash',
        ]);

        $resPay->assertStatus(200);
        $this->assertEquals(0.00, (float)$customer->fresh()->due_amount);
        $this->assertEquals('Completed', Sale::find($partialSaleData['id'])->status, 'Sale status should be synced to Completed when dues are settled');
    }
}
