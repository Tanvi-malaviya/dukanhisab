<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('app_settings')) {
                $rzpKey = \App\Models\AppSetting::get('razorpay_key_id');
                $rzpSecret = \App\Models\AppSetting::get('razorpay_key_secret');
                $rzpWebhook = \App\Models\AppSetting::get('razorpay_webhook_secret');
                $rzpEnabled = \App\Models\AppSetting::get('razorpay_enabled');
                $rzpMode = \App\Models\AppSetting::get('razorpay_mode');

                if (!empty($rzpKey)) {
                    config(['services.razorpay.key' => $rzpKey]);
                }
                if (!empty($rzpSecret)) {
                    config(['services.razorpay.secret' => $rzpSecret]);
                }
                if (!empty($rzpWebhook)) {
                    config(['services.razorpay.webhook_secret' => $rzpWebhook]);
                }
                if (!empty($rzpEnabled)) {
                    config(['services.razorpay.enabled' => $rzpEnabled === 'yes']);
                }
                if (!empty($rzpMode)) {
                    config(['services.razorpay.mode' => $rzpMode]);
                }
            }
        } catch (\Throwable $e) {
            // Graceful fallback during installation or migrations
        }
    }
}
