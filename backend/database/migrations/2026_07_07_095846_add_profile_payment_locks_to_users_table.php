<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'profile_locked_at')) {
                $table->timestamp('profile_locked_at')->nullable()->after('aadhaar_photo');
            }

            if (! Schema::hasColumn('users', 'payment_details_locked_at')) {
                $table->timestamp('payment_details_locked_at')->nullable()->after('profile_locked_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['profile_locked_at', 'payment_details_locked_at']);
        });
    }
};