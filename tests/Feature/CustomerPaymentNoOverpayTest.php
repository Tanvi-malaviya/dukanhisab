<?php

namespace Tests\Feature;

use App\Models\CashBook;
use App\Models\Customer;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Collect-payment never accepts more than the customer's net due (due minus any old store credit),
 * so no cash-in is ever posted against nothing.
 */
class CustomerPaymentNoOverpayTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->shop = Shop::create(['owner_id' => $user->id, 'name' => 'S', 'status' => 'active']);
        Sanctum::actingAs($user);
    }

    private function pay(float $due, float $credit, float $amount)
    {
        $c = Customer::create(['shop_id' => $this->shop->id, 'name' => 'C', 'mobile' => '9' . rand(100000000, 999999999), 'due_amount' => $due, 'credit_balance' => $credit]);
        $res = $this->withHeaders(['X-Shop-ID' => $this->shop->id])
            ->postJson("/api/v1/customers/{$c->id}/collect-payment", ['amount' => $amount, 'payment_method' => 'cash']);

        return [$res, $c->fresh()];
    }

    public function test_payment_is_rejected_when_nothing_is_due(): void
    {
        [$res] = $this->pay(0, 0, 500);
        $res->assertStatus(422);
        $this->assertSame(0, CashBook::count());
    }

    public function test_payment_is_rejected_when_old_credit_already_covers_the_due(): void
    {
        [$res, $c] = $this->pay(300, 500, 500);
        $res->assertStatus(422);
        $this->assertSame(0, CashBook::count());
        $this->assertEquals(300, (float) $c->due_amount);
        $this->assertEquals(500, (float) $c->credit_balance);
    }

    public function test_paying_exactly_the_net_due_settles_the_customer(): void
    {
        [$res, $c] = $this->pay(1000, 400, 600);
        $res->assertStatus(200);
        $this->assertEquals(0, (float) $c->net_balance);
        $this->assertEquals(600, (float) CashBook::sum('amount'));
    }
}
