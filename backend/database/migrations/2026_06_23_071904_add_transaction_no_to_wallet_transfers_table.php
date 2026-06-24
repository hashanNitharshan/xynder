<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_transfers', function (Blueprint $table) {
            if (! Schema::hasColumn('wallet_transfers', 'transaction_no')) {
                $table->string('transaction_no')->nullable()->unique()->after('id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transfers', function (Blueprint $table) {
            $table->dropColumn('transaction_no');
        });
    }
};