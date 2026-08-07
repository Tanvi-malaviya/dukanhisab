<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SubscriptionPlan::updateOrCreate(
            ['slug' => 'free'],
            [
                'name' => 'Free Plan',
                'description' => 'Free tier with basic shop website, barcode scanner, and ads.',
                'price' => 0.00,
                'billing_period' => 'free',
                'features' => [
                    'max_shops' => 1,
                    'max_devices' => 1,
                    'advanced_reports' => false,
                    'backup' => false,
                ],
                'status' => 'active',
            ]
        );

        SubscriptionPlan::updateOrCreate(
            ['slug' => 'premium'],
            [
                'name' => 'Premium Plan',
                'description' => 'Ad-free experience, advanced exports, WhatsApp/Email invoices, and cloud backup.',
                'price' => 365.00,
                'billing_period' => 'yearly',
                'features' => [
                    'max_shops' => 2,
                    'max_devices' => 2,
                    'advanced_reports' => true,
                    'backup' => true,
                ],
                'status' => 'active',
            ]
        );

        SubscriptionPlan::updateOrCreate(
            ['slug' => 'business'],
            [
                'name' => 'Business (Lifetime)',
                'description' => 'Lifetime updates, premium website themes, no ads, and priority support.',
                'price' => 999.00,
                'billing_period' => 'lifetime',
                'features' => [
                    'max_shops' => 5,
                    'max_devices' => 5,
                    'advanced_reports' => true,
                    'backup' => true,
                ],
                'status' => 'active',
            ]
        );
    }
}
