<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('wallet_requests', function (Blueprint $table) {
            $table->foreignId('merchant_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->string('payment_slip')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('wallet_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('merchant_id');
            $table->dropColumn('payment_slip');
        });
    }
};