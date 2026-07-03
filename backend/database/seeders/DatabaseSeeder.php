<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = env('ADMIN_EMAIL', 'admin@gmail.com');
        $adminPassword = env('ADMIN_PASSWORD', '12345');

        $admin = User::where('email', $adminEmail)
            ->orWhere('wallet_id', 'XYWADMIN001')
            ->first();

        if (! $admin) {
            $admin = new User();
        }

        $admin->name = env('ADMIN_NAME', 'Super Admin');
        $admin->email = $adminEmail;
        $admin->role = 'admin';
        $admin->phone = env('ADMIN_PHONE', null);
        $admin->balance = 0;
        $admin->status = 'active';
        $admin->is_active = true;
        $admin->is_verified = true;
        $admin->is_online = false;
        $admin->wallet_id = 'XYWADMIN001';
        $admin->email_verified_at = now();
        $admin->password = Hash::make($adminPassword);
        $admin->save();

        $this->command->info('✅ Admin user created/updated successfully.');
    }
}