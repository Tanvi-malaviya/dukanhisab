<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Shop;
use App\Models\User;
use App\Models\CashBook;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;

class BankTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_deposit_to_bank_creates_contra_entries_and_adjusts_bank_balance(): void
    {
        $user = User::factory()->create();
        $shop = Shop::create([
            'owner_id' => $user->id,
            'name' => 'Bank Transfer Shop ' . Str::random(5),
            'currency' => 'INR',
            'is_active' => true,
        ]);
        $user->update(['shop_id' => $shop->id]);

        $response = $this->actingAs($user)
            ->withHeaders(['X-Shop-ID' => $shop->id])
            ->postJson('/api/v1/bank-transfers', [
                'type' => 'deposit',
                'amount' => 500.00,
                'description' => 'EOD Cash Deposit',
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'type' => 'deposit',
                    'amount' => 500.00,
                ]
            ]);

        // Verify CashBook contra entries: cash_out from cash, cash_in into bank
        $cashOut = CashBook::where('shop_id', $shop->id)
            ->where('reference_type', 'contra')
            ->where('type', 'cash_out')
            ->where('payment_method', 'cash')
            ->first();
        $this->assertNotNull($cashOut);
        $this->assertEquals(500.00, (float)$cashOut->amount);

        $cashIn = CashBook::where('shop_id', $shop->id)
            ->where('reference_type', 'contra')
            ->where('type', 'cash_in')
            ->where('payment_method', 'bank')
            ->first();
        $this->assertNotNull($cashIn);
        $this->assertEquals(500.00, (float)$cashIn->amount);

        // Verify bank-accounts index reports the new balance
        $bankIndex = $this->actingAs($user)
            ->withHeaders(['X-Shop-ID' => $shop->id])
            ->getJson('/api/v1/bank-accounts');
        $bankIndex->assertStatus(200);
        $this->assertEquals(500.00, (float)$bankIndex->json()[0]['balance']);
    }

    public function test_cash_withdrawal_from_bank_creates_contra_entries_and_reduces_bank_balance(): void
    {
        $user = User::factory()->create();
        $shop = Shop::create([
            'owner_id' => $user->id,
            'name' => 'Bank Transfer Shop ' . Str::random(5),
            'currency' => 'INR',
            'is_active' => true,
        ]);
        $user->update(['shop_id' => $shop->id]);

        // Initial bank balance: deposit 2000
        CashBook::create([
            'shop_id' => $shop->id,
            'type' => 'cash_in',
            'amount' => 2000.00,
            'payment_method' => 'bank',
            'description' => 'Opening Bank Balance',
        ]);

        $response = $this->actingAs($user)
            ->withHeaders(['X-Shop-ID' => $shop->id])
            ->postJson('/api/v1/bank-transfers', [
                'type' => 'withdraw',
                'amount' => 400.00,
                'description' => 'Cash withdrawal for petty cash',
            ]);

        $response->assertStatus(201);

        // Verify CashBook contra entries: cash_out from bank, cash_in to cash
        $bankOut = CashBook::where('shop_id', $shop->id)
            ->where('reference_type', 'contra')
            ->where('type', 'cash_out')
            ->where('payment_method', 'bank')
            ->first();
        $this->assertNotNull($bankOut);
        $this->assertEquals(400.00, (float)$bankOut->amount);

        $cashIn = CashBook::where('shop_id', $shop->id)
            ->where('reference_type', 'contra')
            ->where('type', 'cash_in')
            ->where('payment_method', 'cash')
            ->first();
        $this->assertNotNull($cashIn);
        $this->assertEquals(400.00, (float)$cashIn->amount);

        // Verify bank-accounts index reports remaining balance: 2000 - 400 = 1600
        $bankIndex = $this->actingAs($user)
            ->withHeaders(['X-Shop-ID' => $shop->id])
            ->getJson('/api/v1/bank-accounts');
        $bankIndex->assertStatus(200);
        $this->assertEquals(1600.00, (float)$bankIndex->json()[0]['balance']);
    }
}
