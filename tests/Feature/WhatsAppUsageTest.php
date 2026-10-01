<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Shop;
use App\Models\User;
use App\Models\WhatsAppCreditLedger;
use App\Models\WhatsAppMessageLog;
use App\Models\WhatsAppPackPurchase;
use App\Services\WhatsApp\WhatsAppWallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Shop-owner message history, and the admin WhatsApp usage page with credit adjustments.
 */
class WhatsAppUsageTest extends TestCase
{
    use RefreshDatabase;

    private User $owner;
    private Shop $shop;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->owner = User::factory()->create();
        $this->shop = Shop::create(['owner_id' => $this->owner->id, 'name' => 'Shree Traders', 'status' => 'active']);
        $this->customer = Customer::create(['shop_id' => $this->shop->id, 'name' => 'Ramesh', 'mobile' => '9876543210']);
    }

    private function log(array $o = []): WhatsAppMessageLog
    {
        return WhatsAppMessageLog::create($o + [
            'shop_id' => $this->shop->id, 'event' => 'sale_invoice', 'recipient_type' => 'customer', 'recipient_id' => $this->customer->id,
            'phone' => '919876543210', 'template_name' => 'sale_invoice_en', 'language' => 'en', 'payload' => [], 'status' => 'delivered',
        ]);
    }

    private function actingAsAdmin(): void
    {
        $admin = Admin::create(['name' => 'Root', 'email' => 'root@test.com', 'password' => 'secret123', 'role' => 'superadmin', 'status' => 'active']);
        $this->actingAs($admin, 'admin');
    }

    // ------------------------------------------------------------ shop owner history

    public function test_message_history_lists_own_messages_with_names_and_filters(): void
    {
        $this->log();
        $this->log(['status' => 'failed', 'error' => 'Recipient is not on WhatsApp.']);
        $other = Shop::create(['owner_id' => User::factory()->create()->id, 'name' => 'Other', 'status' => 'active']);
        $this->log(['shop_id' => $other->id]);
        Sanctum::actingAs($this->owner);

        $this->withHeaders(['X-Shop-ID' => $this->shop->id])->getJson('/api/v1/whatsapp/messages')
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonPath('data.0.recipient_name', 'Ramesh')
            ->assertJsonPath('data.0.event_label', 'Sale invoice')
            ->assertJsonMissingPath('data.0.payload');

        $this->withHeaders(['X-Shop-ID' => $this->shop->id])->getJson('/api/v1/whatsapp/messages?status=failed')
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.error', 'Recipient is not on WhatsApp.');
    }

    // ------------------------------------------------------------ admin usage

    public function test_usage_page_shows_totals_shops_and_purchases(): void
    {
        $this->actingAsAdmin();
        $this->log();
        $this->log(['status' => 'failed']);
        app(WhatsAppWallet::class)->adjust($this->shop, 40, 'gift', null);
        WhatsAppPackPurchase::create([
            'shop_id' => $this->shop->id, 'user_id' => $this->owner->id, 'pack_name' => 'Starter', 'credits' => 100, 'amount' => 99,
            'razorpay_order_id' => 'order_1', 'razorpay_payment_id' => 'pay_1', 'status' => 'paid', 'paid_at' => now(),
        ]);

        $this->get(route('admin.whatsapp.usage'))
            ->assertOk()
            ->assertSee('Shree Traders')
            ->assertSee('₹99.00')
            ->assertSee('pay_1')
            ->assertSee('WhatsApp Usage');
    }

    public function test_admin_can_add_and_remove_credits_but_not_below_zero(): void
    {
        $this->actingAsAdmin();
        $wallet = app(WhatsAppWallet::class);

        $this->post(route('admin.whatsapp.usage.adjust'), ['shop_id' => $this->shop->id, 'credits' => 25, 'note' => 'Support gift'])
            ->assertRedirect()->assertSessionHas('success');
        $this->assertSame(25, $wallet->balance($this->shop->id));
        $this->assertNotNull(WhatsAppCreditLedger::where('type', 'adjustment')->value('admin_id'));

        $this->post(route('admin.whatsapp.usage.adjust'), ['shop_id' => $this->shop->id, 'credits' => -30, 'note' => 'Too much'])
            ->assertRedirect()->assertSessionHas('error');
        $this->assertSame(25, $wallet->balance($this->shop->id));

        $this->post(route('admin.whatsapp.usage.adjust'), ['shop_id' => $this->shop->id, 'credits' => 0, 'note' => 'x'])
            ->assertSessionHasErrors('credits');
    }

    public function test_shop_owner_cannot_open_admin_usage(): void
    {
        $this->actingAs($this->owner);

        $this->get(route('admin.whatsapp.usage'))->assertRedirect();
        $this->post(route('admin.whatsapp.usage.adjust'), ['shop_id' => $this->shop->id, 'credits' => 100, 'note' => 'free'])->assertRedirect();
        $this->assertSame(0, app(WhatsAppWallet::class)->balance($this->shop->id));
    }
}
