<?php

namespace App\Console\Commands;

use App\Models\WhatsAppShopSetting;
use App\Services\WhatsApp\WhatsAppClient;
use App\Services\WhatsApp\WhatsAppReminderSender;
use Illuminate\Console\Command;

class SendScheduledWhatsAppReminders extends Command
{
    protected $signature = 'whatsapp:send-scheduled';

    protected $description = 'Send WhatsApp due reminders / statements for shops whose weekly slot is now';

    public function handle(WhatsAppReminderSender $sender): int
    {
        if (!WhatsAppClient::isEnabled()) {
            return self::SUCCESS;
        }

        $now = now();
        $settings = WhatsAppShopSetting::with('shop')
            ->whereIn('event', WhatsAppShopSetting::SCHEDULED_EVENTS)
            ->where('enabled', true)
            ->get();

        foreach ($settings as $setting) {
            if (!$setting->shop || $setting->shop->status !== 'active' || !$sender->isDue($setting, $now)) {
                continue;
            }

            // Claim the slot first so an overlapping run can't send the same reminders twice.
            $claimed = WhatsAppShopSetting::whereKey($setting->id)
                ->where(fn ($q) => $q->whereNull('last_run_at')->orWhere('last_run_at', $setting->last_run_at))
                ->update(['last_run_at' => $now]);
            if (!$claimed) {
                continue;
            }

            $result = $sender->runSchedule($setting);
            $setting->update(['last_run_result' => $result + ['at' => $now->toIso8601String()]]);

            $this->info("Shop {$setting->shop_id} {$setting->event}: {$result['sent']} sent, {$result['skipped_no_credits']} skipped (no credits).");
        }

        return self::SUCCESS;
    }
}
