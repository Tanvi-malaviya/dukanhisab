<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsAppMessage;
use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WhatsAppMessageLog;
use App\Models\WhatsAppShopSetting;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsApp\WhatsAppWallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Instant WhatsApp messages fired by sales, purchases and payments (when the shop switched them
 * on), and the manual "Send via WhatsApp" button on invoices.
 */
class WhatsAppAutoSendTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;
    private Customer $customer;
    private Supplier $supplier;
    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();

        AppSetting::set('whatsapp_enabled', 'yes');
        AppSetting::set('whatsapp_phone_number_id', '106540352242922');
        AppSetting::set('whatsapp_access_token', Crypt::encryptString('EAAGtoken'));

        $user = User::factory()->create();
        $this->shop = Shop::create(['owner_id' => $user->id, 'name' => 'Shree Traders', 'status' => 'active']);
        Sanctum::actingAs($user);

        $this->customer = Customer::create(['shop_id' => $this->shop->id, 'name' => 'Ramesh', 'mobile' => '9876543210']);
        $this->supplier = Supplier::create(['shop_id' => $this->shop->id, 'name' => 'Wholesaler', 'mobile' => '9876500000', 'due_amount' => 1000]);
        $this->product = Product::create(['shop_id' => $this->shop->id, 'name' => 'Rice', 'selling_price' => 100, 'purchase_price' => 80, 'stock' => 50]);

        foreach (array_keys(WhatsAppTemplate::EVENTS) as $key) {
            WhatsAppTemplate::create([
                'key' => $key, 'language' => 'en', 'meta_template_name' => $key . '_en', 'meta_language_code' => 'en',
                'body' => 'Hi {{1}}', 'variables' => ['party_name'], 'status' => 'active',
                'has_document' => in_array($key, ['sale_invoice', 'purchase_record'], true),
            ]);
        }
        app(WhatsAppWallet::class)->adjust($this->shop, 10, 'test', null);
    }

    private function h(): array
    {
        return ['X-Shop-ID' => $this->shop->id];
    }

    private function enable(string ...$events): void
    {
        foreach ($events as $event) {
            WhatsAppShopSetting::create(['shop_id' => $this->shop->id, 'event' => $event, 'enabled' => true]);
        }
    }

    private function createSale(array $o = []): int
    {
        return $this->withHeaders($this->h())->postJson('/api/v1/sales', $o + [
            'customer_id' => $this->customer->id,
            'subtotal' => 200, 'grand_total' => 200, 'payment_type' => 'Cash',
            'items' => [['product_id' => $this->product->id, 'quantity' => 2, 'selling_price' => 100]],
        ])->assertStatus(201)->json('id');
    }

    public function test_sale_sends_invoice_when_enabled(): void
    {
        $this->enable('sale_invoice');

        $saleId = $this->createSale();

        $log = WhatsAppMessageLog::sole();
        $this->assertSame('sale_invoice', $log->event);
        $this->assertSame($saleId, $log->sale_id);
        $this->assertSame('919876543210', $log->phone);
        $this->assertArrayHasKey('document_url', $log->payload);
        Queue::assertPushed(SendWhatsAppMessage::class, 1);
        $this->assertSame(9, app(WhatsAppWallet::class)->balance($this->shop->id));
    }

    public function test_sale_sends_nothing_when_switched_off_or_opted_out_or_walk_in(): void
    {
        $this->createSale(); // event off

        $this->enable('sale_invoice');
        $this->customer->update(['whatsapp_opt_out' => true]);
        $this->createSale();

        $this->createSale(['customer_id' => null]); // walk-in

        $this->assertDatabaseCount('whatsapp_message_logs', 0);
        $this->assertSame(10, app(WhatsAppWallet::class)->balance($this->shop->id));
    }

    public function test_sale_still_succeeds_when_out_of_credits(): void
    {
        $this->enable('sale_invoice');
        app(WhatsAppWallet::class)->adjust($this->shop, -10, 'drain', null);

        $this->createSale();

        $this->assertDatabaseCount('sales', 1);
        $this->assertDatabaseCount('whatsapp_message_logs', 0);
    }

    public function test_offline_sync_sale_also_sends(): void
    {
        $this->enable('sale_invoice');

        $this->withHeaders($this->h())->postJson('/api/v1/sync/batch', ['operations' => [[
            'resource' => 'sales', 'action' => 'create', 'op_id' => '1',
            'data' => [
                'customer_id' => $this->customer->id, 'subtotal' => 100, 'grand_total' => 100, 'payment_type' => 'Cash',
                'items' => [['product_id' => $this->product->id, 'quantity' => 1, 'selling_price' => 100]],
            ],
        ]]])->assertOk();

        $this->assertSame('sale_invoice', WhatsAppMessageLog::sole()->event);
    }

    public function test_customer_payment_sends_receipt_with_amount(): void
    {
        $this->enable('payment_received');
        $this->customer->update(['due_amount' => 500]);

        $this->withHeaders($this->h())->postJson("/api/v1/customers/{$this->customer->id}/collect-payment", [
            'amount' => 200, 'payment_method' => 'UPI',
        ])->assertOk();

        $log = WhatsAppMessageLog::sole();
        $this->assertSame('payment_received', $log->event);
        $this->assertSame('₹200.00', $log->payload['values']['amount']);
        $this->assertSame('₹300.00', $log->payload['values']['due_amount']); // due after the payment
    }

    public function test_purchase_and_supplier_payment_send_to_supplier(): void
    {
        $this->enable('purchase_record', 'supplier_payment');

        $this->withHeaders($this->h())->postJson('/api/v1/purchases', [
            'supplier_id' => $this->supplier->id, 'total_amount' => 160, 'payment_type' => 'Cash',
            'items' => [['product_id' => $this->product->id, 'quantity' => 2, 'purchase_price' => 80]],
        ])->assertStatus(201);

        $this->withHeaders($this->h())->postJson("/api/v1/suppliers/{$this->supplier->id}/pay-due", [
            'amount' => 300, 'payment_method' => 'Cash',
        ])->assertOk();

        $this->assertSame(['purchase_record', 'supplier_payment'], WhatsAppMessageLog::orderBy('id')->pluck('event')->all());
        $this->assertSame('supplier', WhatsAppMessageLog::first()->recipient_type);
    }

    // ------------------------------------------------------------ manual send

    public function test_manual_send_works_even_when_auto_is_off(): void
    {
        $saleId = $this->createSale();

        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$saleId}/whatsapp")
            ->assertStatus(202)
            ->assertJson(['balance' => 9]);

        $this->assertSame($saleId, WhatsAppMessageLog::sole()->sale_id);
    }

    public function test_manual_send_reports_no_credits_and_missing_customer(): void
    {
        $walkIn = $this->createSale(['customer_id' => null]);
        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$walkIn}/whatsapp")->assertStatus(422);

        app(WhatsAppWallet::class)->adjust($this->shop, -10, 'drain', null);
        $saleId = $this->createSale();
        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$saleId}/whatsapp")
            ->assertStatus(402)->assertJson(['code' => 'no_credits']);
    }

    public function test_manual_send_cannot_reach_another_shops_sale(): void
    {
        $other = User::factory()->create();
        $otherShop = Shop::create(['owner_id' => $other->id, 'name' => 'Other', 'status' => 'active']);
        $sale = Sale::create([
            'shop_id' => $otherShop->id, 'sale_number' => 'X-1', 'subtotal' => 10, 'discount' => 0, 'grand_total' => 10,
            'paid_amount' => 10, 'payment_type' => 'Cash', 'status' => 'Completed', 'sale_date' => now(),
        ]);

        $this->withHeaders($this->h())->postJson("/api/v1/sales/{$sale->id}/whatsapp")->assertNotFound();
    }

    public function test_manual_purchase_send(): void
    {
        $purchase = Purchase::create([
            'shop_id' => $this->shop->id, 'supplier_id' => $this->supplier->id, 'purchase_number' => 'P-1',
            'total_amount' => 160, 'discount' => 0, 'paid_amount' => 160, 'payment_type' => 'Cash', 'status' => 'Completed', 'purchase_date' => now(),
        ]);

        $this->withHeaders($this->h())->postJson("/api/v1/purchases/{$purchase->id}/whatsapp")->assertStatus(202);
        $this->assertSame('purchase_record', WhatsAppMessageLog::sole()->event);
    }
}
