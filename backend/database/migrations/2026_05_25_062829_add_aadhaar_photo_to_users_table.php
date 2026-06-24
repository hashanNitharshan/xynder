<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'aadhaar_photo')) {
                $table->string('aadhaar_photo')->nullable()->after('aadhaar');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'aadhaar_photo')) {
                $table->dropColumn('aadhaar_photo');
            }
        });
    }
};