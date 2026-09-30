<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\InvoiceApiController;
use App\Models\InvoiceSetting;
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
 * Admin > Settings > Invoice Layout saves a default logo, footer text and watermark toggle,
 * but no invoice PDF ever read them — a shop with no logo/footer of its own just showed a blank
 * spot instead of falling back to the admin's global branding, and "watermark enabled" did
 * nothing at all. These lock in that the fallback/opt-in actually reaches the HTML that dompdf/
 * mpdf render from (invoice number prefix is intentionally left out of scope — see the settings
 * page banner).
 */
class InvoiceBrandingTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Shop $shop;
    private ?string $tempLogoPath = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->shop = Shop::create(['owner_id' => $this->user->id, 'name' => 'Branding Shop', 'status' => 'active']);
        Sanctum::actingAs($this->user);
    }

    protected function tearDown(): void
    {
        if ($this->tempLogoPath && file_exists($this->tempLogoPath)) {
            @unlink($this->tempLogoPath);
        }
        parent::tearDown();
    }

    /**
     * getBase64Image() resolves relative paths straight through storage_path(), bypassing the
     * Storage facade, so a real file has to land there directly (Storage::fake would divert it).
     */
    private function writeRealLogoAndPointSettingAtIt(): void
    {
        $dir = storage_path('app/public/invoice_logos');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = 'test-' . uniqid() . '.png';
        $this->tempLogoPath = $dir . '/' . $filename;
        // Smallest valid 1x1 transparent PNG.
        file_put_contents($this->tempLogoPath, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
        ));
        InvoiceSetting::set('default_logo', 'invoice_logos/' . $filename);
    }

    private function h(): array
    {
        return ['X-Shop-ID' => $this->shop->id];
    }

    private function buildSaleHtml(): string
    {
        $product = Product::create([
            'shop_id' => $this->shop->id, 'name' => 'P' . uniqid(), 'stock' => 20,
            'selling_price' => 100, 'purchase_price' => 60,
        ]);
        $saleId = $this->withHeaders($this->h())->postJson('/api/v1/sales', [
            'subtotal' => 100, 'grand_total' => 100, 'payment_type' => 'Cash',
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'selling_price' => 100]],
        ])->json('id');

        $sale = Sale::with('items.product', 'customer')->findOrFail($saleId);

        $method = new ReflectionMethod(InvoiceApiController::class, 'buildSaleInvoiceHtml');
        $method->setAccessible(true);
        return $method->invoke(new InvoiceApiController(), $sale);
    }

    private function buildPurchaseHtml(): string
    {
        $product = Product::create([
            'shop_id' => $this->shop->id, 'name' => 'P' . uniqid(), 'stock' => 20,
            'selling_price' => 100, 'purchase_price' => 60,
        ]);
        $purchaseId = $this->withHeaders($this->h())->postJson('/api/v1/purchases', [
            'total_amount' => 100, 'payment_type' => 'Cash',
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'purchase_price' => 100]],
        ])->json('id');

        $purchase = Purchase::with('items.product', 'supplier')->findOrFail($purchaseId);

        $method = new ReflectionMethod(InvoiceApiController::class, 'buildPurchaseInvoiceHtml');
        $method->setAccessible(true);
        return $method->invoke(new InvoiceApiController(), $purchase);
    }

    public function test_sale_invoice_falls_back_to_admin_default_logo_when_shop_has_none(): void
    {
        $this->assertNull($this->shop->logo);
        $this->writeRealLogoAndPointSettingAtIt();

        $html = $this->buildSaleHtml();
        $this->assertStringContainsString('<img class="header-logo"', $html);
        $this->assertStringContainsString('data:image', $html);
    }

    public function test_sale_invoice_has_no_logo_when_neither_shop_nor_admin_have_one(): void
    {
        $html = $this->buildSaleHtml();
        $this->assertStringNotContainsString('<img class="header-logo"', $html);
    }

    public function test_sale_invoice_falls_back_to_admin_footer_text_when_shop_has_none(): void
    {
        InvoiceSetting::set('footer_text', 'Global Footer Notice XYZ');
        $html = $this->buildSaleHtml();
        $this->assertStringContainsString('Global Footer Notice XYZ', $html);
    }

    public function test_shop_own_footer_takes_priority_over_admin_default(): void
    {
        InvoiceSetting::set('footer_text', 'Should Not Appear');
        $this->shop->forceFill(['invoice_footer' => 'Shop Specific Footer'])->save();

        $html = $this->buildSaleHtml();
        $this->assertStringContainsString('Shop Specific Footer', $html);
        $this->assertStringNotContainsString('Should Not Appear', $html);
    }

    public function test_sale_invoice_renders_watermark_when_enabled(): void
    {
        InvoiceSetting::set('watermark', 'yes');
        $html = $this->buildSaleHtml();
        $this->assertStringContainsString('<div class="dh-watermark">', $html);
        $this->assertStringContainsString('DukanHisab', $html);
    }

    public function test_sale_invoice_has_no_watermark_when_disabled(): void
    {
        InvoiceSetting::set('watermark', 'no');
        $html = $this->buildSaleHtml();
        $this->assertStringNotContainsString('<div class="dh-watermark">', $html);
    }

    public function test_purchase_invoice_also_gets_admin_footer_and_watermark(): void
    {
        InvoiceSetting::set('footer_text', 'Purchase Footer ABC');
        InvoiceSetting::set('watermark', 'yes');

        $html = $this->buildPurchaseHtml();
        $this->assertStringContainsString('Purchase Footer ABC', $html);
        $this->assertStringContainsString('<div class="dh-watermark">', $html);
    }
}
