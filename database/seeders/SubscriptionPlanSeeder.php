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
                'description' => 'Perfect for small shop owners starting their business.',
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
            ['slug' => 'monthly'],
            [
                'name' => 'Monthly Plan',
                'description' => 'Monthly plan for 1 shop with single device login.',
                'price' => 49.00,
                'billing_period' => 'monthly',
                'features' => [
                    'max_shops' => 1,
                    'max_devices' => 1,
                    'advanced_reports' => true,
                    'backup' => true,
                ],
                'status' => 'active',
            ]
        );

        SubscriptionPlan::updateOrCreate(
            ['slug' => 'yearly'],
            [
                'name' => 'Yearly Plan',
                'description' => 'Best value yearly plan for up to 3 shops with 3 device logins.',
                'price' => 499.00,
                'billing_period' => 'yearly',
                'features' => [
                    'max_shops' => 3,
                    'max_devices' => 3,
                    'advanced_reports' => true,
                    'backup' => true,
                ],
                'status' => 'active',
            ]
        );

        // Keep premium updated for legacy reference
        SubscriptionPlan::updateOrCreate(
            ['slug' => 'premium'],
            [
                'name' => 'Yearly Plan (Premium)',
                'description' => 'Yearly plan for up to 3 shops with 3 device logins.',
                'price' => 499.00,
                'billing_period' => 'yearly',
                'features' => [
                    'max_shops' => 3,
                    'max_devices' => 3,
                    'advanced_reports' => true,
                    'backup' => true,
                ],
                'status' => 'active',
            ]
        );
    }
}
