<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AddOn;

class AddOnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AddOn::updateOrCreate(
            ['slug' => 'shop'],
            [
                'title' => 'Extra Shop',
                'type' => 'shop',
                'description' => 'Add one more shop to your account. Each purchase grants 1 additional shop for a full year.',
                'price' => 200.00,
                'billing_period' => 'yearly',
                'status' => 'active',
            ]
        );

        AddOn::updateOrCreate(
            ['slug' => 'website'],
            [
                'title' => 'Shop Website',
                'type' => 'website',
                'description' => 'Publish a public website to showcase your products online, with a shareable link for your customers.',
                'price' => 200.00,
                'billing_period' => 'yearly',
                'status' => 'active',
            ]
        );
    }
}
