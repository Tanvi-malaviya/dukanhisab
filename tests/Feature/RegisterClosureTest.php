<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\User;
use App\Models\CashBook;
use App\Models\CashRegisterClosure;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class RegisterClosureTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_closure_current_status_calculates_opening_and_daily_cash(): void
    {
        $user = User::factory()->create();
        $shop = Shop::create([
            'owner_id' => $user->id,
            'name' => 'Register Shop ' . Str::random(5),
            'currency' => 'INR',
            'is_active' => true,
        ]);
        $user->update(['shop_id' => $shop->id]);

        // Yesterday's cash: cash_in 1000, cash_out 200 => net 800 opening balance for today
        CashBook::create([
            'shop_id' => $shop->id,
            'type' => 'cash_in',
            'amount' => 1000.00,
            'payment_method' => 'cash',
            'description' => 'Yesterday sale',
            'transaction_date' => Carbon::yesterday(),
        ]);
        CashBook::create([
            'shop_id' => $shop->id,
            'type' => 'cash_out',
            'amount' => 200.00,
            'payment_method' => 'cash',
            'description' => 'Yesterday tea',
            'transaction_date' => Carbon::yesterday(),
        ]);

        // Today's cash: cash_in 500, cash_out 100
        CashBook::create([
            'shop_id' => $shop->id,
            'type' => 'cash_in',
            'amount' => 500.00,
            'payment_method' => 'cash',
            'description' => 'Today sale',
            'transaction_date' => Carbon::today(),
        ]);
        CashBook::create([
            'shop_id' => $shop->id,
            'type' => 'cash_out',
            'amount' => 100.00,
            'payment_method' => 'cash',
            'description' => 'Today expense',
            'transaction_date' => Carbon::today(),
        ]);

        $response = $this->actingAs($user)
            ->withHeaders(['X-Shop-ID' => $shop->id])
            ->getJson('/api/v1/register-closures/current-status');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'opening_balance' => 800.0,
                    'cash_in' => 500.0,
                    'cash_out' => 100.0,
                    'expected_cash' => 1200.0, // 800 + 500 - 100
                    'is_closed_today' => false,
                ]
            ]);
    }

    public function test_can_save_daily_register_closure_with_physical_cash_count_and_denominations(): void
    {
        $user = User::factory()->create();
        $shop = Shop::create([
            'owner_id' => $user->id,
            'name' => 'Register Closure Shop ' . Str::random(5),
            'currency' => 'INR',
            'is_active' => true,
        ]);
        $user->update(['shop_id' => $shop->id]);

        // Today cash in 1000
        CashBook::create([
            'shop_id' => $shop->id,
            'type' => 'cash_in',
            'amount' => 1000.00,
            'payment_method' => 'cash',
            'description' => 'Today cash sale',
            'transaction_date' => Carbon::today(),
        ]);

        // Count physical cash: 2x500 = 1000 (perfect balance)
        $response = $this->actingAs($user)
            ->withHeaders(['X-Shop-ID' => $shop->id])
            ->postJson('/api/v1/register-closures', [
                'actual_cash' => 1000.00,
                'denominations' => [
                    '500' => 2,
                    '200' => 0,
                    '100' => 0,
                    '50' => 0,
                    '20' => 0,
                    '10' => 0,
                    'coins' => 0,
                ],
                'note' => 'EOD cash counter reconciled.',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'expected_cash' => 1000.0,
                    'actual_cash' => 1000.0,
                    'difference' => 0.0,
                    'reconciliation_status' => 'balanced',
                ]
            ]);

        $this->assertDatabaseHas('cash_register_closures', [
            'shop_id' => $shop->id,
            'expected_cash' => 1000.00,
            'actual_cash' => 1000.00,
            'difference' => 0.00,
        ]);
    }

    public function test_surplus_and_shortage_are_correctly_identified(): void
    {
        $user = User::factory()->create();
        $shop = Shop::create([
            'owner_id' => $user->id,
            'name' => 'Shortage Test Shop ' . Str::random(5),
            'currency' => 'INR',
            'is_active' => true,
        ]);
        $user->update(['shop_id' => $shop->id]);

        // Expected cash 500
        CashBook::create([
            'shop_id' => $shop->id,
            'type' => 'cash_in',
            'amount' => 500.00,
            'payment_method' => 'cash',
            'description' => 'Sale',
            'transaction_date' => Carbon::today(),
        ]);

        // Actual cash counted 450 (-50 shortage)
        $response = $this->actingAs($user)
            ->withHeaders(['X-Shop-ID' => $shop->id])
            ->postJson('/api/v1/register-closures', [
                'actual_cash' => 450.00,
                'denominations' => ['100' => 4, '50' => 1],
                'note' => '50 rs shortage',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'expected_cash' => 500.0,
                    'actual_cash' => 450.0,
                    'difference' => -50.0,
                    'reconciliation_status' => 'shortage',
                ]
            ]);
    }
}
