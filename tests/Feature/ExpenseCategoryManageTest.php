<?php

namespace Tests\Feature;

use App\Models\CashBook;
use App\Models\ExpenseCategory;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Renaming or deleting an expense category from web or app must reach the app's expense list,
 * which syncs expenses by updated_at.
 */
class ExpenseCategoryManageTest extends TestCase
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

    private function h(): array
    {
        return ['X-Shop-ID' => $this->shop->id];
    }

    private function expenseIn(string $category): CashBook
    {
        $id = $this->withHeaders($this->h())->postJson('/api/v1/expenses', [
            'amount' => 50, 'payment_method' => 'cash', 'description' => "[{$category}] Tea",
        ])->assertStatus(201)->json('id');
        $expense = CashBook::find($id);
        CashBook::where('id', $id)->update(['updated_at' => now()->subDay()]);

        return $expense->fresh();
    }

    public function test_renaming_a_category_resurfaces_its_expenses_in_the_delta_feed(): void
    {
        $expense = $this->expenseIn('Office');
        $cursor = now()->subMinute()->toIso8601String();
        $cat = ExpenseCategory::where('name', 'Office')->first();

        $this->withHeaders($this->h())->putJson("/api/v1/expense-categories/{$cat->id}", ['name' => 'Office Supplies'])->assertStatus(200);

        $feed = collect($this->withHeaders($this->h())->getJson('/api/v1/expenses?updated_since=' . urlencode($cursor))->json());
        $row = $feed->firstWhere('id', $expense->id);
        $this->assertNotNull($row, 'renamed category must push its expenses into the delta feed');
        $this->assertSame('Office Supplies', $row['expense_category']['name']);
    }

    public function test_deleting_a_category_keeps_its_expenses_as_general(): void
    {
        $expense = $this->expenseIn('Travel');
        $cursor = now()->subMinute()->toIso8601String();
        $cat = ExpenseCategory::where('name', 'Travel')->first();

        $this->withHeaders($this->h())->deleteJson("/api/v1/expense-categories/{$cat->id}")->assertStatus(204);

        $this->assertNull($expense->fresh()->expense_category_id);
        $this->assertNotNull($expense->fresh(), 'the expense itself is not deleted');
        $row = collect($this->withHeaders($this->h())->getJson('/api/v1/expenses?updated_since=' . urlencode($cursor))->json())->firstWhere('id', $expense->id);
        $this->assertNotNull($row);
        $this->assertNull($row['expense_category']);
    }
}
