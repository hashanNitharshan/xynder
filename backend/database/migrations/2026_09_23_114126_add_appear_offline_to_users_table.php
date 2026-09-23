<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'appear_offline')) {
                // When true, the user always shows as offline, even while active.
                // Used by the admin "Go Offline" option.
                $table->boolean('appear_offline')->default(false)->after('is_online');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'appear_offline')) {
                $table->dropColumn('appear_offline');
            }
        });
    }
};