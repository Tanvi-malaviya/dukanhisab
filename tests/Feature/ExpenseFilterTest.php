<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\User;
use App\Models\CashBook;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class ExpenseFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_expenses_only_include_operational_expenses_not_purchases_or_returns(): void
    {
        $user = User::factory()->create();

        $shop = Shop::create([
            'owner_id' => $user->id,
            'name' => 'Expense Filter Test Shop ' . Str::random(5),
            'currency' => 'INR',
            'is_active' => true,
        ]);

        $user->update(['shop_id' => $shop->id]);
        $headers = ['X-Shop-ID' => $shop->id];

        // 1. Create a system Purchase cash_out entry
        CashBook::create([
            'shop_id' => $shop->id,
            'type' => 'cash_out',
            'amount' => 800.00,
            'payment_method' => 'cash',
            'description' => 'Purchase: PUR-20260910-0001',
            'reference_id' => 1,
            'reference_type' => 'purchase',
            'transaction_date' => Carbon::now(),
        ]);

        // 2. Create a system Sales Return cash_out entry
        CashBook::create([
            'shop_id' => $shop->id,
            'type' => 'cash_out',
            'amount' => 300.00,
            'payment_method' => 'cash',
            'description' => 'Return: INV-20260910-0007',
            'reference_id' => 1,
            'reference_type' => 'sale',
            'transaction_date' => Carbon::now(),
        ]);

        // 3. Create a manual / operational expense
        CashBook::create([
            'shop_id' => $shop->id,
            'type' => 'cash_out',
            'amount' => 150.00,
            'payment_method' => 'cash',
            'description' => 'Shop Electricity Bill',
            'reference_id' => null,
            'reference_type' => 'expense',
            'transaction_date' => Carbon::now(),
        ]);

        // 4. Fetch expenses via API
        $resp = $this->actingAs($user, 'sanctum')->withHeaders($headers)
            ->getJson('/api/v1/expenses');

        $resp->assertStatus(200);
        $data = $resp->json();

        // Must ONLY contain the 1 operational expense, not the purchase or return
        $this->assertCount(1, $data);
        $this->assertEquals('Shop Electricity Bill', $data[0]['description']);
        $this->assertEquals('150.00', $data[0]['amount']);
    }
}
