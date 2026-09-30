<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\InvoiceApiController;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use ReflectionMethod;
use Tests\TestCase;

/**
 * The invoice PDF's "Payment Status" line (right side, next to Bill To/Supplier) used to only
 * ever show Returned/Partially Returned/Unpaid — anything else, including a genuinely partial
 * payment (status = "Partially Paid" on both sales and purchases), silently fell through and
 * showed a plain green "Paid" on the sale invoice. These lock in that all three real payment
 * states — fully paid, due (unpaid), and partially paid — render their own distinct, correctly
 * translated label on both sale and purchase invoices.
 */
class InvoicePaymentStatusTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->shop = Shop::create(['owner_id' => $user->id, 'name' => 'Status Shop', 'status' => 'active']);
        Sanctum::actingAs($user);
    }

    private function product(): Product
    {
        return Product::create([
            'shop_id' => $this->shop->id, 'name' => 'P' . uniqid(), 'stock' => 20,
            'selling_price' => 100, 'purchase_price' => 60,
        ]);
    }

    private function saleHtml(string $status, float $paidAmount): string
    {
        $product = $this->product();
        $sale = Sale::create([
            'shop_id' => $this->shop->id,
            'sale_number' => 'INV-STATUS-' . uniqid(),
            'subtotal' => 100,
            'grand_total' => 100,
            'payment_type' => 'Credit',
            'status' => $status,
            'paid_amount' => $paidAmount,
        ]);
        $sale->items()->create(['product_id' => $product->id, 'quantity' => 1, 'selling_price' => 100, 'total' => 100]);
        $sale->load('items.product', 'customer');

        $method = new ReflectionMethod(InvoiceApiController::class, 'buildSaleInvoiceHtml');
        $method->setAccessible(true);
        return $method->invoke(new InvoiceApiController(), $sale);
    }

    private function purchaseHtml(string $status, float $paidAmount): string
    {
        $product = $this->product();
        $purchase = Purchase::create([
            'shop_id' => $this->shop->id,
            'purchase_number' => 'PUR-STATUS-' . uniqid(),
            'total_amount' => 100,
            'payment_type' => 'Credit',
            'status' => $status,
            'paid_amount' => $paidAmount,
        ]);
        $purchase->items()->create(['product_id' => $product->id, 'quantity' => 1, 'purchase_price' => 100, 'total' => 100]);
        $purchase->load('items.product', 'supplier');

        $method = new ReflectionMethod(InvoiceApiController::class, 'buildPurchaseInvoiceHtml');
        $method->setAccessible(true);
        return $method->invoke(new InvoiceApiController(), $purchase);
    }

    public function test_sale_shows_fully_paid_when_completed(): void
    {
        $html = $this->saleHtml('Completed', 100);
        $this->assertStringContainsString(__('fully_paid'), $html);
        $this->assertStringNotContainsString(__('due'), $html);
        $this->assertStringNotContainsString(__('partially_paid'), $html);
    }

    public function test_sale_shows_due_when_unpaid(): void
    {
        $html = $this->saleHtml('Unpaid', 0);
        $this->assertStringContainsString(__('due'), $html);
        $this->assertStringNotContainsString(__('fully_paid'), $html);
    }

    public function test_sale_shows_partially_paid_when_partially_paid(): void
    {
        $html = $this->saleHtml('Partially Paid', 40);
        $this->assertStringContainsString(__('partially_paid'), $html);
        $this->assertStringNotContainsString(__('fully_paid'), $html);
        $this->assertStringNotContainsString('>' . __('due') . '<', $html);
    }

    public function test_purchase_shows_fully_paid_when_completed(): void
    {
        $html = $this->purchaseHtml('Completed', 100);
        $this->assertStringContainsString(strtoupper(__('fully_paid')), $html);
    }

    public function test_purchase_shows_due_when_unpaid(): void
    {
        $html = $this->purchaseHtml('Unpaid', 0);
        $this->assertStringContainsString(strtoupper(__('due')), $html);
        $this->assertStringNotContainsString(strtoupper(__('fully_paid')), $html);
    }

    public function test_purchase_shows_partially_paid_when_partially_paid(): void
    {
        $html = $this->purchaseHtml('Partially Paid', 40);
        $this->assertStringContainsString(strtoupper(__('partially_paid')), $html);
        $this->assertStringNotContainsString(strtoupper(__('fully_paid')), $html);
    }

    /**
     * The invoice pipeline itself currently forces English for every static label regardless of
     * the shop's locale (a separate, pre-existing workaround), so this checks the translation
     * dictionary directly rather than through the PDF pipeline — the strings are ready for
     * Hindi/Gujarati the moment that override is lifted.
     */
    public function test_payment_status_labels_are_translated_in_hindi_and_gujarati(): void
    {
        app()->setLocale('hi');
        $this->assertEquals('पूर्ण भुगतान', __('fully_paid'));
        $this->assertEquals('बकाया', __('due'));
        $this->assertEquals('आंशिक भुगतान', __('partially_paid'));

        app()->setLocale('gu');
        $this->assertEquals('સંપૂર્ણ ચૂકવેલ', __('fully_paid'));
        $this->assertEquals('બાકી', __('due'));
        $this->assertEquals('અંશતઃ ચૂકવેલ', __('partially_paid'));

        app()->setLocale('en');
    }
}
