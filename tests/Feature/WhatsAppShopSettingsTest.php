<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\Shop;
use App\Models\User;
use App\Models\WhatsAppShopSetting;
use App\Models\WhatsAppTemplate;
use App\Services\WhatsApp\WhatsAppException;
use App\Services\WhatsApp\WhatsAppMessenger;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The shop owner's WhatsApp setup: which messages are on, the weekly reminder schedule,
 * read-only template previews and per-person opt-out.
 */
class WhatsAppShopSettingsTest extends TestCase
{
    use RefreshDatabase;

    private Shop $shop;

    protected function setUp(): void
    {
        parent::setUp();
        AppSetting::set('whatsapp_enabled', 'yes');
        $user = User::factory()->create();
        $this->shop = Shop::create(['owner_id' => $user->id, 'name' => 'Shree Traders', 'status' => 'active', 'upi_id' => 'shree@upi']);
        Sanctum::actingAs($user);

        WhatsAppTemplate::create([
            'key' => 'due_reminder', 'language' => 'en', 'meta_template_name' => 'due_reminder_en', 'meta_language_code' => 'en',
            'body' => 'Hello {{1}}, your pending balance at {{2}} is {{3}}.', 'variables' => ['party_name', 'shop_name', 'due_amount'],
            'has_pay_button' => true, 'status' => 'active',
        ]);
    }

    private function h(): array
    {
        return ['X-Shop-ID' => $this->shop->id];
    }

    private function event(array $events, string $key): array
    {
        return collect($events)->firstWhere('key', $key);
    }

    public function test_settings_list_every_event_off_by_default_with_previews(): void
    {
        $events = $this->withHeaders($this->h())->getJson('/api/v1/whatsapp/settings')
            ->assertOk()
            ->assertJson(['available' => true, 'balance' => 0, 'upi_id' => 'shree@upi'])
            ->json('events');

        $this->assertCount(count(WhatsAppTemplate::EVENTS), $events);
        $this->assertFalse(collect($events)->contains('enabled', true));

        $due = $this->event($events, 'due_reminder');
        $this->assertTrue($due['scheduled']);
        $this->assertTrue($due['template_available']);
        $this->assertTrue($due['has_pay_button']);
        $this->assertSame(['mon'], $due['schedule_days']);
        $this->assertSame('Hello Ramesh, your pending balance at Shree Traders is ₹1,200.00.', $due['preview']);

        $invoice = $this->event($events, 'sale_invoice');
        $this->assertFalse($invoice['scheduled']);
        $this->assertFalse($invoice['template_available']);
        $this->assertNull($invoice['preview']);
    }

    public function test_pay_now_is_hidden_without_a_upi_id(): void
    {
        $this->shop->update(['upi_id' => null]);

        $events = $this->withHeaders($this->h())->getJson('/api/v1/whatsapp/settings')->json('events');

        $this->assertFalse($this->event($events, 'due_reminder')['has_pay_button']);
    }

    public function test_saving_toggles_and_schedule(): void
    {
        $this->withHeaders($this->h())->postJson('/api/v1/whatsapp/settings', ['events' => [
            ['key' => 'sale_invoice', 'enabled' => true],
            ['key' => 'due_reminder', 'enabled' => true, 'schedule_days' => ['thu', 'mon'], 'schedule_time' => '18:30', 'min_due_amount' => 500],
        ]])->assertOk();

        $this->assertTrue(WhatsAppShopSetting::isEnabled($this->shop->id, 'sale_invoice'));
        $due = WhatsAppShopSetting::where('shop_id', $this->shop->id)->where('event', 'due_reminder')->first();
        $this->assertSame(['mon', 'thu'], $due->schedule_days); // stored in week order
        $this->assertSame('18:30', $due->schedule_time);
        $this->assertEquals(500, $due->min_due_amount);
        $this->assertFalse(WhatsAppShopSetting::isEnabled($this->shop->id, 'payment_received'));
    }

    public function test_schedule_allows_at_most_two_days_and_needs_days_when_enabled(): void
    {
        $this->withHeaders($this->h())->postJson('/api/v1/whatsapp/settings', ['events' => [
            ['key' => 'due_reminder', 'enabled' => true, 'schedule_days' => ['mon', 'wed', 'fri'], 'schedule_time' => '10:00'],
        ]])->assertStatus(422)->assertJsonValidationErrors('events.0.schedule_days');

        $this->withHeaders($this->h())->postJson('/api/v1/whatsapp/settings', ['events' => [
            ['key' => 'due_reminder', 'enabled' => true, 'schedule_days' => [], 'schedule_time' => '10:00'],
        ]])->assertStatus(422)->assertJsonValidationErrors('events.0.schedule_days');

        $this->withHeaders($this->h())->postJson('/api/v1/whatsapp/settings', ['events' => [
            ['key' => 'due_reminder', 'enabled' => true, 'schedule_days' => ['mon'], 'schedule_time' => '25:00'],
        ]])->assertStatus(422)->assertJsonValidationErrors('events.0.schedule_time');

        $this->withHeaders($this->h())->postJson('/api/v1/whatsapp/settings', ['events' => [
            ['key' => 'not_an_event', 'enabled' => true],
        ]])->assertStatus(422);
    }

    public function test_settings_are_per_shop(): void
    {
        $other = User::factory()->create();
        $otherShop = Shop::create(['owner_id' => $other->id, 'name' => 'Other', 'status' => 'active']);

        $this->withHeaders($this->h())->postJson('/api/v1/whatsapp/settings', ['events' => [
            ['key' => 'sale_invoice', 'enabled' => true],
        ]])->assertOk();

        $this->assertFalse(WhatsAppShopSetting::isEnabled($otherShop->id, 'sale_invoice'));
    }

    public function test_customer_can_be_opted_out_and_is_then_never_messaged(): void
    {
        $customer = Customer::create(['shop_id' => $this->shop->id, 'name' => 'Ramesh', 'mobile' => '9876543210', 'due_amount' => 500]);

        $this->withHeaders($this->h())->putJson("/api/v1/customers/{$customer->id}", ['whatsapp_opt_out' => true])
            ->assertOk();
        $this->assertTrue($customer->fresh()->whatsapp_opt_out);

        $this->expectException(WhatsAppException::class);
        $this->expectExceptionMessage('turned off WhatsApp messages');
        app(WhatsAppMessenger::class)->send($this->shop, 'due_reminder', $customer->fresh());
    }

    public function test_whatsapp_page_is_part_of_the_web_panel(): void
    {
        $this->withoutVite();

        $this->get('/shop/whatsapp')->assertOk()->assertSee('whatsappPage()', false);
    }
}
