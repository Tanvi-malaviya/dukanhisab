<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\CreditNote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class SalesReturnCreditNoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_return_with_credit_note_and_stock_restoration(): void
    {
        // 1. Setup Shop, User, Customer, Product
        $user = User::factory()->create();

        $shop = Shop::create([
            'owner_id' => $user->id,
            'name' => 'Test Shop ' . Str::random(5),
            'currency' => 'INR',
            'is_active' => true,
        ]);

        $user->update(['shop_id' => $shop->id]);

        $customer = Customer::create([
            'shop_id' => $shop->id,
            'name' => 'Rahul Test Customer',
            'phone' => '999888' . rand(1000, 9999),
            'credit_balance' => 0.00,
        ]);

        $product = Product::create([
            'shop_id' => $shop->id,
            'name' => 'Paracetamol 500mg',
            'purchase_price' => 30.00,
            'selling_price' => 50.00,
            'stock' => 100,
        ]);

        $headers = ['X-Shop-ID' => $shop->id];

        // 2. Perform Sale of 5 units
        $responseSale = $this->actingAs($user, 'sanctum')->withHeaders($headers)->postJson('/api/v1/sales', [
            'customer_id' => $customer->id,
            'subtotal' => 250.00,
            'discount' => 0,
            'grand_total' => 250.00,
            'payment_type' => 'Cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 5,
                    'selling_price' => 50.00,
                    'discount' => 0,
                ]
            ],
        ]);

        $responseSale->assertStatus(201);
        $saleId = $responseSale->json('id');

        // Check stock after sale
        $this->assertEquals(95, $product->fresh()->stock, 'Product stock should be 95 after selling 5 units');

        // 3. Process Return of 2 units using refund_method = 'credit_note'
        $responseReturn = $this->actingAs($user, 'sanctum')->withHeaders($headers)->postJson("/api/v1/sales/{$saleId}/return", [
            'refund_method' => 'credit_note',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                ]
            ],
        ]);

        $responseReturn->assertStatus(200);

        // Check stock restoration
        $this->assertEquals(97, $product->fresh()->stock, 'Product stock should be restored to 97');

        // Check Credit Note generation and Customer credit_balance
        $customerFresh = $customer->fresh();
        $this->assertEquals(100.00, (float)$customerFresh->credit_balance, 'Customer credit balance should be 100.00');

        $creditNote = CreditNote::where('customer_id', $customer->id)->latest()->first();
        $this->assertNotNull($creditNote, 'Credit Note must be created in DB');
        $this->assertEquals(100.00, (float)$creditNote->total_amount);
        $this->assertEquals(100.00, (float)$creditNote->remaining_balance);
        $this->assertEquals('Active', $creditNote->status);

        // 4. Test Paying a new sale using Store Credit
        $responseStoreCreditSale = $this->actingAs($user, 'sanctum')->withHeaders($headers)->postJson('/api/v1/sales', [
            'customer_id' => $customer->id,
            'subtotal' => 100.00,
            'discount' => 0,
            'grand_total' => 100.00,
            'payment_type' => 'Store Credit',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'selling_price' => 50.00,
                    'discount' => 0,
                ]
            ],
        ]);

        $responseStoreCreditSale->assertStatus(201);

        // Verify credit balance deducted and credit note marked redeemed
        $this->assertEquals(0.00, (float)$customer->fresh()->credit_balance, 'Customer credit balance should now be 0');
        $this->assertEquals('Redeemed', $creditNote->fresh()->status, 'Credit note status should now be Redeemed');

        // 5. Test Split Payment: Add ₹100 to credit balance and pay a ₹200 bill with ₹100 Store Credit + ₹100 Cash
        $customer->update(['credit_balance' => 100.00]);
        $splitCreditNote = CreditNote::create([
            'shop_id' => $shop->id,
            'customer_id' => $customer->id,
            'credit_note_number' => 'CN-SPLIT-TEST',
            'total_amount' => 100.00,
            'used_amount' => 0.00,
            'remaining_balance' => 100.00,
            'status' => 'Active',
        ]);

        $responseSplitSale = $this->actingAs($user, 'sanctum')->withHeaders($headers)->postJson('/api/v1/sales', [
            'customer_id' => $customer->id,
            'subtotal' => 200.00,
            'discount' => 0,
            'grand_total' => 200.00,
            'payment_type' => 'Cash',
            'used_credit_balance' => 100.00,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 4,
                    'selling_price' => 50.00,
                    'discount' => 0,
                ]
            ],
        ]);

        $responseSplitSale->assertStatus(201);
        $this->assertEquals(0.00, (float)$customer->fresh()->credit_balance, 'Customer credit balance should be 0 after using 100 on 200 bill');
        $this->assertEquals('Redeemed', $splitCreditNote->fresh()->status, 'Credit note should be fully redeemed');

        // Check CashBook entry only logged the remaining ₹100 cash!
        $cashBookEntry = \App\Models\CashBook::where('shop_id', $shop->id)->latest('id')->first();
        $this->assertEquals(100.00, (float)$cashBookEntry->amount, 'CashBook should only log net cash received (100.00)');
    }
}
