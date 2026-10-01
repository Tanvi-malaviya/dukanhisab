<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Customer;
use App\Models\Shop;
use App\Models\User;
use App\Models\CashBook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class CustomerNetBalanceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->shop = Shop::create([
            'owner_id' => $this->user->id,
            'name' => 'Net Balance Test Shop ' . Str::random(5),
            'currency' => 'INR',
            'is_active' => true,
        ]);
        $this->user->update(['shop_id' => $this->shop->id]);
    }

    public function test_customer_model_computes_single_net_balance(): void
    {
        // 1. Customer owes 10000 with 2000 credit -> Net Due 8000
        $c1 = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'Ramesh Due',
            'due_amount' => 10000.00,
            'credit_balance' => 2000.00,
        ]);
        $this->assertEquals(8000.00, $c1->net_balance);

        // 2. Customer with advance credit: Due 0, Credit 500 -> Net Balance -500
        $c2 = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'Suresh Advance',
            'due_amount' => 0.00,
            'credit_balance' => 500.00,
        ]);
        $this->assertEquals(-500.00, $c2->net_balance);

        // 3. Settled customer -> Net Balance 0
        $c3 = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'Mahesh Clear',
            'due_amount' => 0.00,
            'credit_balance' => 0.00,
        ]);
        $this->assertEquals(0.00, $c3->net_balance);
    }

    public function test_api_returns_net_balance_in_customer_payload(): void
    {
        Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'Anil Payload',
            'due_amount' => 5000.00,
            'credit_balance' => 1500.00,
        ]);

        $res = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Shop-ID' => $this->shop->id])
            ->getJson('/api/v1/customers');

        $res->assertStatus(200);
        $data = $res->json();
        $this->assertNotEmpty($data);
        $cust = $data[0];
        $this->assertArrayHasKey('net_balance', $cust);
        $this->assertEquals(3500.00, (float) $cust['net_balance']);
    }

    public function test_collect_payment_reconciles_credit_and_caps_at_net_due(): void
    {
        // Customer has 10000 due and 2000 credit -> net due 8000
        $customer = Customer::create([
            'shop_id' => $this->shop->id,
            'name' => 'Dinesh Reconcile',
            'due_amount' => 10000.00,
            'credit_balance' => 2000.00,
        ]);

        // Attempting to collect 8500 exceeds net due 8000 -> 422 rejected
        $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Shop-ID' => $this->shop->id])
            ->postJson("/api/v1/customers/{$customer->id}/collect-payment", [
                'amount' => 8500.00,
                'payment_method' => 'Cash',
            ])->assertStatus(422);

        // Collecting exact net due of 8000 succeeds
        $res = $this->actingAs($this->user, 'sanctum')
            ->withHeaders(['X-Shop-ID' => $this->shop->id])
            ->postJson("/api/v1/customers/{$customer->id}/collect-payment", [
                'amount' => 8000.00,
                'payment_method' => 'Cash',
            ]);

        $res->assertStatus(200);
        $this->assertEquals(0.00, (float) $customer->fresh()->due_amount);
        $this->assertEquals(0.00, (float) $customer->fresh()->credit_balance);
        $this->assertEquals(0.00, (float) $customer->fresh()->net_balance);

        // CashBook should record exactly 8000 cash_in
        $cb = CashBook::where('shop_id', $this->shop->id)->where('type', 'cash_in')->first();
        $this->assertNotNull($cb);
        $this->assertEquals(8000.00, (float) $cb->amount);
    }
}
