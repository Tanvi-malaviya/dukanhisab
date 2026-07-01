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
        SubscriptionPlan::create([
            'name' => 'Free Plan',
            'slug' => 'free',
            'description' => 'Perfect for small shop owners starting their business.',
            'price' => 0.00,
            'billing_period' => 'free',
            'features' => [
                'max_products' => 50,
                'max_devices' => 1,
                'advanced_reports' => false,
                'backup' => false,
            ],
            'status' => 'active',
        ]);

        SubscriptionPlan::create([
            'name' => 'Premium Plan',
            'slug' => 'premium',
            'description' => 'Unlimited features, analytics, database backups, and multiple device support.',
            'price' => 499.00,
            'billing_period' => 'monthly',
            'features' => [
                'max_products' => -1, // unlimited
                'max_devices' => 5,
                'advanced_reports' => true,
                'backup' => true,
            ],
            'status' => 'active',
        ]);
    }
}
