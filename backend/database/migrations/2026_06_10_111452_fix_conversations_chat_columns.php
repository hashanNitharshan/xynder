<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('conversations', 'wallet_transfer_id')) {
                $table->foreignId('wallet_transfer_id')
                    ->nullable()
                    ->after('user_two_id')
                    ->constrained('wallet_transfers')
                    ->cascadeOnDelete();
            }

            if (!Schema::hasColumn('conversations', 'wallet_request_id')) {
                $table->foreignId('wallet_request_id')
                    ->nullable()
                    ->after('wallet_transfer_id')
                    ->constrained('wallet_requests')
                    ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (Schema::hasColumn('conversations', 'wallet_request_id')) {
                $table->dropConstrainedForeignId('wallet_request_id');
            }

            if (Schema::hasColumn('conversations', 'wallet_transfer_id')) {
                $table->dropConstrainedForeignId('wallet_transfer_id');
            }
        });
    }
};