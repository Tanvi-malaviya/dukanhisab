<?php

namespace App\Services\WhatsApp;

use App\Models\Customer;
use App\Models\PaymentLink;
use App\Models\Shop;
use App\Models\Supplier;
use App\Models\WhatsAppMessageLog;
use App\Models\WhatsAppShopSetting;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * Due reminders to customers (with a Pay Now link) and due statements to suppliers — sent one at
 * a time from the Reminders screen, or in bulk on the shop's weekly schedule.
 */
class WhatsAppReminderSender
{
    /** A schedule slot still runs if the server was busy/down for up to this long after it. */
    public const SCHEDULE_WINDOW_MINUTES = 120;

    public function __construct(private WhatsAppMessenger $messenger)
    {
    }

    public function remindCustomer(Shop $shop, Customer $customer): WhatsAppMessageLog
    {
        $due = (float) $customer->net_balance;
        if ($due <= 0) {
            throw new WhatsAppException("{$customer->name} has no pending due.");
        }

        // The link, the log and the credit debit commit together, or not at all.
        $log = DB::transaction(function () use ($shop, $customer, $due) {
            $context = [];
            $template = $this->messenger->templateFor($shop, 'due_reminder');
            if ($template?->has_pay_button) {
                // Issued even without a UPI ID: the template's button needs a target, and the
                // page then asks the customer to pay the shop directly.
                $context['button_suffix'] = PaymentLink::issue($shop, $customer, $due)->token;
            }

            return $this->messenger->send($shop, 'due_reminder', $customer, $context);
        });

        $this->stamp($customer);

        return $log;
    }

    public function remindSupplier(Shop $shop, Supplier $supplier): WhatsAppMessageLog
    {
        if ((float) $supplier->net_balance <= 0) {
            throw new WhatsAppException("Nothing is due to {$supplier->name}.");
        }

        $log = $this->messenger->send($shop, 'supplier_due', $supplier);
        $this->stamp($supplier);

        return $log;
    }

    /** Whether this weekly slot should run now: right day, slot time passed, not already run. */
    public function isDue(WhatsAppShopSetting $setting, CarbonInterface $now): bool
    {
        if (!$setting->enabled || !$setting->schedule_time || !in_array(strtolower($now->format('D')), $setting->schedule_days ?? [], true)) {
            return false;
        }

        $slot = $now->copy()->setTimeFromTimeString($setting->schedule_time);

        return $now->gte($slot)
            && $now->lt($slot->copy()->addMinutes(self::SCHEDULE_WINDOW_MINUTES))
            && (!$setting->last_run_at || $setting->last_run_at->lt($slot));
    }

    /**
     * Sends the scheduled reminders of one shop: everyone with at least the minimum due who hasn't
     * opted out and wasn't reminded today. Stops when credits run out.
     *
     * @return array{sent: int, skipped_no_credits: int, failed: int}
     */
    public function runSchedule(WhatsAppShopSetting $setting): array
    {
        $shop = $setting->shop ?? Shop::find($setting->shop_id);
        $isCustomer = $setting->event === 'due_reminder';
        $model = $isCustomer ? Customer::class : Supplier::class;
        $minDue = max((float) $setting->min_due_amount, 0.01);

        $recipients = $model::where('shop_id', $shop->id)
            ->where('whatsapp_opt_out', false)
            ->whereNotNull('mobile')
            ->where(fn ($q) => $q->whereNull('last_whatsapp_reminder_at')->orWhere('last_whatsapp_reminder_at', '<', now()->startOfDay()))
            ->get()
            ->filter(fn ($r) => (float) $r->net_balance >= $minDue && WhatsAppClient::normalizePhone($r->mobile));

        $result = ['sent' => 0, 'skipped_no_credits' => 0, 'failed' => 0];
        foreach ($recipients->values() as $i => $recipient) {
            try {
                $isCustomer ? $this->remindCustomer($shop, $recipient) : $this->remindSupplier($shop, $recipient);
                $result['sent']++;
            } catch (InsufficientCreditsException) {
                $result['skipped_no_credits'] = $recipients->count() - $i;
                break;
            } catch (WhatsAppException) {
                $result['failed']++;
            }
        }

        return $result;
    }

    /** Raw update so the record's updated_at (used by app sync) doesn't change. */
    private function stamp(Customer|Supplier $recipient): void
    {
        $recipient->newQuery()->whereKey($recipient->id)->toBase()->update(['last_whatsapp_reminder_at' => now()]);
    }
}
