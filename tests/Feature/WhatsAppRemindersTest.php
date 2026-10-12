<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\CashBook;
use App\Models\Customer;
use App\Models\PaymentLink;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\User;
use App\Models\WhatsAppMessageLog;
use App\Models\WhatsAppShopSetting;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsApp\WhatsAppWallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Due reminders with the Pay Now link, the public pay page and "I have paid" claims, and the
 * weekly schedule.
 */
class WhatsAppRemindersTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;
    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        $this->withoutVite();

        AppSetting::set('whatsapp_enabled', 'yes');
        AppSetting::set('whatsapp_phone_number_id', '106540352242922');
        AppSetting::set('whatsapp_access_token', Crypt::encryptString('EAAGtoken'));

        $user = User::factory()->create();
        $this->shop = Shop::create(['owner_id' => $user->id, 'name' => 'Shree Traders', 'status' => 'active', 'upi_id' => 'shree@upi', 'mobile' => '9999999999']);
        Sanctum::actingAs($user);
        $this->customer = Customer::create(['shop_id' => $this->shop->id, 'name' => 'Ramesh', 'mobile' => '9876543210', 'due_amount' => 1200]);

        WhatsAppTemplate::create([
            'key' => 'due_reminder', 'language' => 'en', 'meta_template_name' => 'due_reminder_en', 'meta_language_code' => 'en',
            'body' => 'Hello {{1}}, due {{2}}', 'variables' => ['party_name', 'due_amount'], 'has_pay_button' => true, 'status' => 'active',
        ]);
        WhatsAppTemplate::create([
            'key' => 'supplier_due', 'language' => 'en', 'meta_template_name' => 'supplier_due_en', 'meta_language_code' => 'en',
            'body' => 'Hello {{1}}', 'variables' => ['party_name'], 'status' => 'active',
        ]);
        app(WhatsAppWallet::class)->adjust($this->shop, 10, 'test', null);
    }

    private function h(): array
    {
        return ['X-Shop-ID' => $this->shop->id];
    }

    private function remind(): PaymentLink
    {
        $this->withHeaders($this->h())->postJson("/api/v1/whatsapp/reminders/customer/{$this->customer->id}")->assertStatus(202);

        return PaymentLink::latest('id')->firstOrFail();
    }

    // ------------------------------------------------------------ reminders

    public function test_reminder_issues_a_pay_link_and_puts_its_token_in_the_button(): void
    {
        $link = $this->remind();

        $log = WhatsAppMessageLog::sole();
        $this->assertSame('due_reminder', $log->event);
        $this->assertSame($link->token, $log->payload['button_suffix']);
        $this->assertEquals(1200, $link->amount);
        $this->assertTrue($link->expires_at->isFuture());
        $this->assertNotNull($this->customer->fresh()->last_whatsapp_reminder_at);
    }

    public function test_reminder_refuses_without_due_and_leaves_no_link(): void
    {
        $this->customer->update(['due_amount' => 0]);

        $this->withHeaders($this->h())->postJson("/api/v1/whatsapp/reminders/customer/{$this->customer->id}")->assertStatus(422);

        $this->assertDatabaseCount('payment_links', 0);
    }

    public function test_out_of_credits_rolls_back_the_link(): void
    {
        app(WhatsAppWallet::class)->adjust($this->shop, -10, 'drain', null);

        $this->withHeaders($this->h())->postJson("/api/v1/whatsapp/reminders/customer/{$this->customer->id}")->assertStatus(402);

        $this->assertDatabaseCount('payment_links', 0);
        $this->assertDatabaseCount('whatsapp_message_logs', 0);
    }

    public function test_supplier_due_statement(): void
    {
        $supplier = Supplier::create(['shop_id' => $this->shop->id, 'name' => 'Wholesaler', 'mobile' => '9876500000', 'due_amount' => 800]);

        $this->withHeaders($this->h())->postJson("/api/v1/whatsapp/reminders/supplier/{$supplier->id}")->assertStatus(202);

        $this->assertSame('supplier_due', WhatsAppMessageLog::sole()->event);
        $this->assertDatabaseCount('payment_links', 0);
    }

    // ------------------------------------------------------------ pay page

    public function test_pay_page_opens_upi_with_shop_upi_and_current_due(): void
    {
        $link = $this->remind();

        $this->get("/pay/{$link->token}")
            ->assertOk()
            ->assertSee('Shree Traders')
            ->assertSee('₹1,200.00')
            ->assertSee('upi://pay?pa=shree%40upi&amp;pn=Shree%20Traders&amp;am=1200.00&amp;cu=INR', false)
            ->assertSee('I have paid');
    }

    public function test_pay_page_states(): void
    {
        $link = $this->remind();

        $this->shop->update(['upi_id' => null]);
        $this->get("/pay/{$link->token}")->assertSee('not set up online payment')->assertDontSee('upi://', false);

        $this->shop->update(['upi_id' => 'shree@upi']);
        $this->customer->update(['due_amount' => 0]);
        $this->get("/pay/{$link->token}")->assertSee('no pending dues');

        $this->customer->update(['due_amount' => 100]);
        $this->travel(PaymentLink::VALID_DAYS + 1)->days();
        $this->get("/pay/{$link->token}")->assertSee('expired');

        $this->get('/pay/not-a-real-token')->assertNotFound();
    }

    public function test_pay_page_follows_the_shop_owners_language(): void
    {
        $this->shop->owner->update(['language' => 'gu']);
        $link = $this->remind();

        $this->get("/pay/{$link->token}")->assertSee('મેં ચૂકવણી કરી છે');
    }

    // ------------------------------------------------------------ claims

    public function test_customer_claims_and_owner_confirms_recording_a_upi_payment(): void
    {
        $link = $this->remind();

        $this->post("/pay/{$link->token}/claim", ['utr' => '412345678901', 'amount' => 500])->assertRedirect("/pay/{$link->token}");
        $this->get("/pay/{$link->token}")->assertSee('will confirm your payment');

        $claims = $this->withHeaders($this->h())->getJson('/api/v1/whatsapp/payment-claims')->assertOk()->json('pending');
        $this->assertCount(1, $claims);
        $this->assertSame('412345678901', $claims[0]['claimed_utr']);

        $this->withHeaders($this->h())->postJson("/api/v1/whatsapp/payment-claims/{$link->id}/confirm")->assertOk();

        $this->assertSame('confirmed', $link->fresh()->status);
        $this->assertEquals(700, $this->customer->fresh()->due_amount);
        $this->assertDatabaseHas('cash_books', ['reference_type' => 'customer_payment', 'payment_method' => 'upi', 'amount' => 500]);
        $this->assertStringContainsString('412345678901', CashBook::where('reference_type', 'customer_payment')->value('description'));
        $this->get("/pay/{$link->token}")->assertSee('Payment received');

        // Confirming twice does nothing.
        $this->withHeaders($this->h())->postJson("/api/v1/whatsapp/payment-claims/{$link->id}/confirm")->assertNotFound();
    }

    public function test_confirm_more_than_due_is_refused_and_claim_stays_pending(): void
    {
        $link = $this->remind();
        $this->post("/pay/{$link->token}/claim", ['utr' => 'ABC123456', 'amount' => 5000]);

        $this->withHeaders($this->h())->postJson("/api/v1/whatsapp/payment-claims/{$link->id}/confirm")->assertStatus(422);

        $this->assertSame('claimed', $link->fresh()->status);
        $this->assertEquals(1200, $this->customer->fresh()->due_amount);
    }

    public function test_rejected_claim_can_be_submitted_again(): void
    {
        $link = $this->remind();
        $this->post("/pay/{$link->token}/claim", ['utr' => 'WRONG1234', 'amount' => 1200]);

        $this->withHeaders($this->h())->postJson("/api/v1/whatsapp/payment-claims/{$link->id}/reject")->assertOk();
        $this->get("/pay/{$link->token}")->assertSee('could not find your previous payment');

        $this->post("/pay/{$link->token}/claim", ['utr' => 'RIGHT12345', 'amount' => 1200]);
        $this->assertSame('RIGHT12345', $link->fresh()->claimed_utr);
        $this->assertSame('claimed', $link->fresh()->status);
    }

    public function test_claim_validates_utr_and_ignores_resubmits_while_pending(): void
    {
        $link = $this->remind();

        $this->post("/pay/{$link->token}/claim", ['utr' => '12', 'amount' => 100])->assertSessionHasErrors('utr');

        $this->post("/pay/{$link->token}/claim", ['utr' => 'FIRST12345', 'amount' => 100]);
        $this->post("/pay/{$link->token}/claim", ['utr' => 'SECOND1234', 'amount' => 100]);
        $this->assertSame('FIRST12345', $link->fresh()->claimed_utr);
    }

    public function test_another_shop_cannot_see_or_confirm_claims(): void
    {
        $link = $this->remind();
        $this->post("/pay/{$link->token}/claim", ['utr' => 'ABC123456', 'amount' => 100]);

        $other = User::factory()->create();
        $otherShop = Shop::create(['owner_id' => $other->id, 'name' => 'Other', 'status' => 'active']);
        Sanctum::actingAs($other);

        $this->withHeaders(['X-Shop-ID' => $otherShop->id])->getJson('/api/v1/whatsapp/payment-claims')->assertJsonCount(0, 'pending');
        $this->withHeaders(['X-Shop-ID' => $otherShop->id])->postJson("/api/v1/whatsapp/payment-claims/{$link->id}/confirm")->assertNotFound();
    }

    // ------------------------------------------------------------ weekly schedule

    private function schedule(array $o = []): WhatsAppShopSetting
    {
        return WhatsAppShopSetting::create($o + [
            'shop_id' => $this->shop->id, 'event' => 'due_reminder', 'enabled' => true,
            'schedule_days' => ['thu'], 'schedule_time' => '10:00', 'min_due_amount' => 500,
        ]);
    }

    public function test_scheduled_run_sends_to_eligible_customers_once(): void
    {
        $this->schedule();
        Customer::create(['shop_id' => $this->shop->id, 'name' => 'Small due', 'mobile' => '9876500001', 'due_amount' => 100]);
        Customer::create(['shop_id' => $this->shop->id, 'name' => 'Opted out', 'mobile' => '9876500002', 'due_amount' => 900, 'whatsapp_opt_out' => true]);
        Customer::create(['shop_id' => $this->shop->id, 'name' => 'No mobile', 'due_amount' => 900]);
        Customer::create(['shop_id' => $this->shop->id, 'name' => 'Has credit', 'mobile' => '9876500003', 'due_amount' => 900, 'credit_balance' => 800]);

        Carbon::setTestNow(Carbon::parse('2026-10-01 09:59')); // Thursday, before the slot
        $this->artisan('whatsapp:send-scheduled')->assertSuccessful();
        $this->assertDatabaseCount('whatsapp_message_logs', 0);

        Carbon::setTestNow(Carbon::parse('2026-10-01 10:05'));
        $this->artisan('whatsapp:send-scheduled')->assertSuccessful();
        $this->artisan('whatsapp:send-scheduled')->assertSuccessful(); // same slot again

        $this->assertSame(['Ramesh'], WhatsAppMessageLog::all()->map(fn ($l) => $l->payload['values']['party_name'])->all());
        $setting = WhatsAppShopSetting::first();
        $this->assertSame(1, $setting->last_run_result['sent']);
    }

    public function test_schedule_skips_wrong_day_and_slots_older_than_the_window(): void
    {
        $this->schedule();

        Carbon::setTestNow(Carbon::parse('2026-10-02 10:05')); // Friday
        $this->artisan('whatsapp:send-scheduled');
        Carbon::setTestNow(Carbon::parse('2026-10-01 13:00')); // Thursday, 3h late
        $this->artisan('whatsapp:send-scheduled');

        $this->assertDatabaseCount('whatsapp_message_logs', 0);
    }

    public function test_schedule_runs_again_next_week_and_stops_when_credits_run_out(): void
    {
        $this->schedule();
        Customer::create(['shop_id' => $this->shop->id, 'name' => 'Suresh', 'mobile' => '9876500009', 'due_amount' => 900]);
        app(WhatsAppWallet::class)->adjust($this->shop, -9, 'leave one', null);

        Carbon::setTestNow(Carbon::parse('2026-10-01 10:05'));
        $this->artisan('whatsapp:send-scheduled');
        $this->assertSame(['sent' => 1, 'skipped_no_credits' => 1, 'failed' => 0], array_intersect_key(WhatsAppShopSetting::first()->last_run_result, array_flip(['sent', 'skipped_no_credits', 'failed'])));

        app(WhatsAppWallet::class)->adjust($this->shop, 5, 'top up', null);
        Carbon::setTestNow(Carbon::parse('2026-10-08 10:05')); // next Thursday
        $this->artisan('whatsapp:send-scheduled');
        $this->assertDatabaseCount('whatsapp_message_logs', 3);
    }

    public function test_schedule_does_nothing_when_disabled_globally_or_by_shop(): void
    {
        $this->schedule(['enabled' => false]);
        Carbon::setTestNow(Carbon::parse('2026-10-01 10:05'));
        $this->artisan('whatsapp:send-scheduled');

        WhatsAppShopSetting::query()->update(['enabled' => true]);
        AppSetting::set('whatsapp_enabled', 'no');
        $this->artisan('whatsapp:send-scheduled');

        $this->assertDatabaseCount('whatsapp_message_logs', 0);
    }

    public function test_supplier_due_statement_schedule(): void
    {
        Supplier::create(['shop_id' => $this->shop->id, 'name' => 'Wholesaler', 'mobile' => '9876500000', 'due_amount' => 800]);
        $this->schedule(['event' => 'supplier_due', 'min_due_amount' => 100]);

        Carbon::setTestNow(Carbon::parse('2026-10-01 10:05'));
        $this->artisan('whatsapp:send-scheduled');

        $this->assertSame('supplier_due', WhatsAppMessageLog::sole()->event);
    }
}
