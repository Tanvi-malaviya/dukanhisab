<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\AddOn;

/**
 * The Shop Website add-on becomes a one-time purchase (permanent access, no renewal) instead of a
 * recurring yearly subscription. 'lifetime' here follows the same convention subscription_plans
 * already uses for "never expires". Existing purchasers who paid under the old yearly subscription
 * keep their current UserAddOn row as-is — any live Razorpay subscription for them needs cancelling
 * separately in the Razorpay dashboard so they aren't charged again next year.
 */
return new class extends Migration
{
    public function up(): void
    {
        AddOn::where('slug', 'website')->update([
            'billing_period' => 'lifetime',
            'description' => 'Publish a public website to showcase your products online, with a shareable link for your customers. One-time purchase — pay once, keep it forever.',
        ]);
    }

    public function down(): void
    {
        AddOn::where('slug', 'website')->update([
            'billing_period' => 'yearly',
            'description' => 'Publish a public website to showcase your products online, with a shareable link for your customers.',
        ]);
    }
};
