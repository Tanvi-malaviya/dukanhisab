<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\ContainerType;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Forms send cleared fields as blank, which Laravel turns into null. These columns are NOT NULL, so
 * such saves used to fail with a 500 — e.g. the web product form shows a 0 purchase price as an
 * empty box, so linking a container to that product could not be saved.
 */
class BlankFormFieldsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Shop $shop;
    private ContainerType $tub;

    protected function setUp(): void
    {
        parent::setUp();
        $user = $this->user = User::factory()->create();
        $this->shop = Shop::create(['owner_id' => $user->id, 'name' => 'S', 'status' => 'active']);
        $this->shop->forceFill(['features' => ['containers']])->save();
        $this->tub = ContainerType::create(['shop_id' => $this->shop->id, 'name' => 'Tub', 'deposit_amount' => 200]);
        Sanctum::actingAs($user);
    }

    private function api(string $method, string $uri, array $data = [])
    {
        return $this->withHeaders(['X-Shop-ID' => $this->shop->id])->json($method, $uri, $data);
    }

    public function test_creating_a_product_with_blank_numbers_and_a_container_saves_defaults(): void
    {
        $id = $this->api('POST', '/api/v1/products', [
            'name' => 'Vanilla 5L', 'selling_price' => 500, 'purchase_price' => '', 'stock' => '', 'low_stock_threshold' => '',
            'container_type_id' => (string) $this->tub->id, 'containers_per_unit' => 1,
        ])->assertStatus(201)->json('id');

        $product = Product::find($id);
        $this->assertEquals(0, $product->purchase_price);
        $this->assertSame(0, (int) $product->stock);
        $this->assertSame(5, (int) $product->low_stock_threshold);
        $this->assertSame($this->tub->id, (int) $product->container_type_id);
    }

    public function test_linking_a_container_to_a_product_with_zero_purchase_price_saves(): void
    {
        $product = Product::create(['shop_id' => $this->shop->id, 'name' => 'Vanilla 5L', 'selling_price' => 500, 'purchase_price' => 0, 'stock' => 10]);

        $this->api('PUT', '/api/v1/products/' . $product->id, [
            'name' => 'Vanilla 5L', 'selling_price' => 500, 'purchase_price' => '',
            'container_type_id' => (string) $this->tub->id, 'containers_per_unit' => 2,
        ])->assertStatus(200);

        $product->refresh();
        $this->assertSame($this->tub->id, (int) $product->container_type_id);
        $this->assertSame(2, $product->containers_per_unit);
        $this->assertSame(10, (int) $product->stock);
    }

    public function test_editing_customer_or_supplier_with_blank_due_saves_zero(): void
    {
        $customer = Customer::create(['shop_id' => $this->shop->id, 'name' => 'Ramesh', 'due_amount' => 0]);
        $supplier = Supplier::create(['shop_id' => $this->shop->id, 'name' => 'Amul', 'due_amount' => 0]);

        $this->api('PUT', '/api/v1/customers/' . $customer->id, ['name' => 'Ramesh K', 'due_amount' => ''])->assertStatus(200);
        $this->api('PUT', '/api/v1/suppliers/' . $supplier->id, ['name' => 'Amul Dairy', 'due_amount' => ''])->assertStatus(200);

        $this->assertEquals(0, $customer->fresh()->due_amount);
        $this->assertEquals(0, $supplier->fresh()->due_amount);
    }

    public function test_editing_bank_account_with_blank_opening_balance_saves_zero(): void
    {
        $this->api('GET', '/api/v1/bank-accounts')->assertStatus(200);
        $bank = BankAccount::where('shop_id', $this->shop->id)->firstOrFail();

        $this->api('PUT', '/api/v1/bank-accounts/' . $bank->id, ['name' => 'SBI', 'opening_balance' => ''])->assertStatus(200);
        $this->assertEquals(0, $bank->fresh()->opening_balance);
    }

    public function test_blank_invoice_settings_keep_their_defaults(): void
    {
        $this->api('POST', '/api/v1/invoice-settings', [
            'starting_invoice_number' => '', 'paper_size' => '', 'theme_color' => '', 'show_tax' => '', 'auto_print' => true,
        ])->assertStatus(200)
            ->assertJsonPath('auto_print', true);
    }

    public function test_blank_profile_preferences_keep_current_values(): void
    {
        $this->user->forceFill(['language' => 'gu'])->save();

        $this->api('POST', '/api/v1/shopowner/profile', [
            'language' => '', 'currency' => '', 'date_format' => '', 'time_format' => '', 'theme' => '', 'mobile' => '',
        ])->assertStatus(200);

        $this->assertSame('gu', $this->user->fresh()->language);
    }
}
