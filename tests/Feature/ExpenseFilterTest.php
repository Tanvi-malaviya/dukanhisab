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

    public function test_expenses_list_only_includes_operational_expenses_and_excludes_purchases_and_returns(): void
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

        // 1. System Purchase cash_out entry
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

        // 2. Legacy Purchase cash_out entry (reference_type null)
        CashBook::create([
            'shop_id' => $shop->id,
            'type' => 'cash_out',
            'amount' => 333.34,
            'payment_method' => 'cash',
            'description' => 'Purchase: PUR-20260930-0001',
            'reference_id' => null,
            'reference_type' => null,
            'transaction_date' => Carbon::now(),
        ]);

        // 3. System Sales Return cash_out entry
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

        // 4. Cancellation Reversal cash_out entry
        CashBook::create([
            'shop_id' => $shop->id,
            'type' => 'cash_out',
            'amount' => 200.00,
            'payment_method' => 'cash',
            'description' => 'Reversal (Cancelled): INV-20260911-0001 - personal reason',
            'reference_id' => 1,
            'reference_type' => 'sale_cancel',
            'transaction_date' => Carbon::now(),
        ]);

        // 5. Operational expense (explicit reference_type = expense)
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

        // 6. Operational expense (legacy reference_type = null)
        CashBook::create([
            'shop_id' => $shop->id,
            'type' => 'cash_out',
            'amount' => 100.00,
            'payment_method' => 'cash',
            'description' => 'rent of this month',
            'reference_id' => null,
            'reference_type' => null,
            'transaction_date' => Carbon::now(),
        ]);

        // 7. Fetch expenses via API
        $resp = $this->actingAs($user, 'sanctum')->withHeaders($headers)
            ->getJson('/api/v1/expenses');

        $resp->assertStatus(200);
        $data = $resp->json();

        // Expenses screen must only list operational expenses, NEVER inventory purchases, returns, or reversals.
        $this->assertCount(2, $data);
        $descriptions = collect($data)->pluck('description')->all();
        $this->assertContains('Shop Electricity Bill', $descriptions);
        $this->assertContains('rent of this month', $descriptions);
        $this->assertNotContains('Purchase: PUR-20260910-0001', $descriptions);
        $this->assertNotContains('Purchase: PUR-20260930-0001', $descriptions);
        $this->assertNotContains('Return: INV-20260910-0007', $descriptions);
        $this->assertNotContains('Reversal (Cancelled): INV-20260911-0001 - personal reason', $descriptions);
    }
}
