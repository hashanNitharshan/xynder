<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('address')->nullable()->after('phone');
            $table->string('nic')->nullable()->after('address');
            $table->string('aadhaar')->nullable()->after('nic');
            $table->string('bank_name')->nullable()->after('aadhaar');
            $table->string('branch')->nullable()->after('bank_name');
            $table->string('account_number')->nullable()->after('branch');
            $table->boolean('is_online')->default(false)->after('balance');
            $table->timestamp('last_seen_at')->nullable()->after('is_online');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'address','nic','aadhaar','bank_name','branch',
                'account_number','is_online','last_seen_at'
            ]);
        });
    }
};