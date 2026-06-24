<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL');
        $adminPassword = env('ADMIN_PASSWORD');

        if (! $adminEmail || ! $adminPassword) {
            $this->command->error('');
            $this->command->error('⛔ ADMIN_EMAIL and ADMIN_PASSWORD must be set in .env before seeding.');
            $this->command->error('');
            return;
        }

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Admin',
                'role' => 'admin',
                'phone' => null,
                'balance' => 0,
                'status' => 'active',
                'is_active' => true,
                'is_verified' => true,
                'is_online' => false,
                'wallet_id' => 'XYWADMIN001',
                'email_verified_at' => now(),
                'password' => Hash::make($adminPassword),
            ]
        );

        $this->command->info('✅ Admin user created/updated successfully.');
    }
}