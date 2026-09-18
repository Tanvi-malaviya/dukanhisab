<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\SubscriptionPlan;

return new class extends Migration
{
    /**
     * Shop limits are no longer tied to the subscription plan. Every account
     * gets 1 shop by default; extra shop slots are purchased via the Shop
     * Add-on (see add_ons table), so drop the stale max_shops feature flag.
     */
    public function up(): void
    {
        SubscriptionPlan::all()->each(function (SubscriptionPlan $plan) {
            $features = $plan->features ?? [];
            if (array_key_exists('max_shops', $features)) {
                unset($features['max_shops']);
                $plan->update(['features' => $features]);
            }
        });
    }

    public function down(): void
    {
        // Intentionally left blank; max_shops is no longer a supported feature flag.
    }
};
