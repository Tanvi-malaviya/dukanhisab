<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::create([
            'name' => 'Super Admin',
            'email' => 'admin@dukanhisab.com',
            'password' => Hash::make('admin123'),
            'role' => 'superadmin',
            'status' => 'active',
            'last_login_at' => now(),
        ]);

        Admin::create([
            'name' => 'Support Rep',
            'email' => 'support@dukanhisab.com',
            'password' => Hash::make('support123'),
            'role' => 'support',
            'status' => 'active',
        ]);
    }
}
