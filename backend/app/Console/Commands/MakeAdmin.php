<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class MakeAdmin extends Command
{
    protected $signature   = 'admin:make';
    protected $description = 'Create or reset the admin account interactively (no credentials stored in code)';

    public function handle(): int
    {
        $this->info('');
        $this->info('  ── Admin Account Setup ──────────────────────────');
        $this->info('');

        $name = $this->ask('Admin name', 'Admin');

        $email = $this->ask('Admin email');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Invalid email address.');
            return self::FAILURE;
        }

        $password = $this->secret('Password (min 12 chars, input hidden)');
        if (strlen($password) < 12) {
            $this->error('Password must be at least 12 characters.');
            return self::FAILURE;
        }

        $confirm = $this->secret('Confirm password');
        if ($password !== $confirm) {
            $this->error('Passwords do not match.');
            return self::FAILURE;
        }

        $phone = $this->ask('Phone', '+94770000000');

        $this->info('');
        $this->table(['Field', 'Value'], [
            ['Name',  $name],
            ['Email', $email],
            ['Phone', $phone],
            ['Role',  'admin'],
        ]);

        if (! $this->confirm('Create / update this admin account?', true)) {
            $this->warn('Aborted — no changes made.');
            return self::SUCCESS;
        }

        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'name'              => $name,
                'phone'             => $phone,
                'password'          => Hash::make($password),
                'role'              => 'admin',
                'balance'           => 0,
                'status'            => 'active',
                'is_active'         => true,
                'is_verified'       => true,
                'wallet_id'         => 'XYWADMIN001',
                'email_verified_at' => now(),
            ]
        );

        $this->info('');
        $this->info('  ✅  Admin ' . ($admin->wasRecentlyCreated ? 'created' : 'updated') . ': ' . $email);
        $this->info('');

        return self::SUCCESS;
    }
}