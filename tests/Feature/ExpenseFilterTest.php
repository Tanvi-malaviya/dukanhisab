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

    public function test_expenses_list_includes_expenses_and_purchases_but_not_sales_returns(): void
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

        // Expenses screen lists operational expenses and purchase payments (see ExpenseApiController),
        // but never sales-return refunds.
        $this->assertCount(2, $data);
        $descriptions = collect($data)->pluck('description')->all();
        $this->assertContains('Shop Electricity Bill', $descriptions);
        $this->assertContains('Purchase: PUR-20260910-0001', $descriptions);
        $this->assertNotContains('Return: INV-20260910-0007', $descriptions);
    }
}
