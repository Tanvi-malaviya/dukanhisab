<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AppSetting;
use App\Models\User;
use App\Support\BillingMail;
use App\Support\BillingSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** Admin > Settings > Billing & Invoices: business details, numbering and the billing SMTP account. */
class BillingSettingsTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::create(['name' => 'R', 'email' => 'admin@test.com', 'password' => 'secret123', 'role' => 'superadmin', 'status' => 'active']);
    }

    private function save(array $overrides = [])
    {
        return $this->actingAs($this->admin, 'admin')->post(route('admin.settings.billing.update'), array_merge([
            'billing_business_name' => 'DukanHisab',
            'billing_address' => '12, Ring Road',
            'billing_state' => 'Gujarat',
            'billing_invoice_prefix' => 'DH',
            'billing_refund_prefix' => 'DH/RF',
            'billing_number_digits' => 4,
            'billing_mail_enabled' => 'yes',
            'billing_mail_host' => 'smtp.gmail.com',
            'billing_mail_port' => 465,
            'billing_mail_encryption' => 'ssl',
            'billing_mail_username' => 'billing@dukanhisab.com',
            'billing_mail_password' => 'app-pass-1234',
            'billing_mail_from_address' => 'billing@dukanhisab.com',
            'billing_mail_from_name' => 'DukanHisab Billing',
            'billing_mail_copy_to' => 'accounts@dukanhisab.com',
        ], $overrides));
    }

    public function test_page_loads_with_defaults_and_number_preview(): void
    {
        $this->actingAs($this->admin, 'admin')->get(route('admin.settings.billing'))
            ->assertOk()
            ->assertSee('DH/' . BillingSettings::financialYear() . '/0001');
    }

    public function test_settings_save_and_password_is_encrypted_and_never_shown(): void
    {
        $this->save()->assertSessionHasNoErrors();

        $this->assertSame('Gujarat', BillingSettings::get('billing_state'));
        $stored = AppSetting::get(BillingSettings::PASSWORD_KEY);
        $this->assertNotSame('app-pass-1234', $stored);
        $this->assertSame('app-pass-1234', BillingSettings::password());

        $this->actingAs($this->admin, 'admin')->get(route('admin.settings.billing'))
            ->assertOk()->assertDontSee('app-pass-1234')->assertDontSee($stored);
    }

    public function test_blank_password_keeps_the_saved_one_and_checkbox_clears_it(): void
    {
        $this->save();
        $this->save(['billing_mail_password' => '']);
        $this->assertSame('app-pass-1234', BillingSettings::password());

        $this->save(['billing_mail_password' => '', 'billing_mail_password_clear' => 1]);
        $this->assertNull(BillingSettings::password());
    }

    public function test_enabling_email_needs_host_and_from_address(): void
    {
        $this->save(['billing_mail_host' => '', 'billing_mail_from_address' => ''])
            ->assertSessionHasErrors(['billing_mail_host', 'billing_mail_from_address']);
    }

    public function test_prefixes_are_validated(): void
    {
        $this->save(['billing_invoice_prefix' => 'DH 2026!'])->assertSessionHasErrors('billing_invoice_prefix');
        $this->save(['billing_refund_prefix' => 'DH'])->assertSessionHasErrors('billing_refund_prefix');
    }

    public function test_saved_account_becomes_the_billing_mailer(): void
    {
        $this->save();
        $this->assertTrue(BillingSettings::applyMailer());

        $config = config('mail.mailers.billing');
        $this->assertSame('smtp.gmail.com', $config['host']);
        $this->assertSame('smtps', $config['scheme']);
        $this->assertSame('app-pass-1234', $config['password']);
    }

    public function test_disabled_account_falls_back_to_default_mailer(): void
    {
        $this->save(['billing_mail_enabled' => 'no']);
        config(['mail.mailers.billing' => null]);

        $this->assertFalse(BillingSettings::applyMailer());
        $this->assertNull(BillingSettings::mailer());
    }

    public function test_billing_emails_use_the_from_address_and_bcc_copy(): void
    {
        $this->save(['billing_mail_reply_to' => 'support@dukanhisab.com']);
        BillingSettings::applyMailer();
        config(['mail.mailers.billing' => ['transport' => 'array']]);
        app('mail.manager')->purge('billing');

        BillingMail::send(User::factory()->create(['email' => 'owner@shop.com']), 'Subject', 'Headline', 'Body');

        $sent = app('mail.manager')->mailer('billing')->getSymfonyTransport()->messages();
        $this->assertCount(1, $sent);
        $message = $sent[0]->getOriginalMessage();
        $this->assertSame('billing@dukanhisab.com', $message->getFrom()[0]->getAddress());
        $this->assertSame('accounts@dukanhisab.com', $message->getBcc()[0]->getAddress());
        $this->assertSame('support@dukanhisab.com', $message->getReplyTo()[0]->getAddress());
    }

    public function test_test_email_reports_a_friendly_error_when_smtp_is_unreachable(): void
    {
        $this->actingAs($this->admin, 'admin')->postJson(route('admin.settings.billing.test_email'), [
            'test_to' => 'me@test.com',
            'billing_mail_host' => '127.0.0.1',
            'billing_mail_port' => 1,
            'billing_mail_encryption' => 'none',
            'billing_mail_from_address' => 'billing@dukanhisab.com',
        ])->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_financial_year_runs_april_to_march(): void
    {
        $this->assertSame('2026-27', BillingSettings::financialYear(new \DateTime('2026-04-01')));
        $this->assertSame('2025-26', BillingSettings::financialYear(new \DateTime('2026-03-31')));
    }
}
