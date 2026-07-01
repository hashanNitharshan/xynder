<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE wallet_requests MODIFY status ENUM('pending','approved','rejected','closed') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE wallet_requests MODIFY status ENUM('pending','approved','rejected') DEFAULT 'pending'");
    }
};