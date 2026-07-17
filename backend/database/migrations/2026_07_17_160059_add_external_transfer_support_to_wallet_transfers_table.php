<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Existing wallet transfers are internal transfers.
         * External transfers do not have a registered receiver,
         * so receiver_id must allow NULL.
         */

        Schema::table('wallet_transfers', function (Blueprint $table) {
            $table->string('transfer_type', 20)
                ->default('internal')
                ->after('receiver_wallet_id')
                ->index();
        });

        /*
         * Change receiver_id from required to nullable.
         * This allows external transfers without a BitxNow receiver.
         */
        DB::statement(
            'ALTER TABLE wallet_transfers
             MODIFY receiver_id BIGINT UNSIGNED NULL'
        );

        /*
         * Mark all existing transfers as internal transfers.
         */
        DB::table('wallet_transfers')
            ->whereNull('transfer_type')
            ->orWhere('transfer_type', '')
            ->update([
                'transfer_type' => 'internal',
            ]);
    }

    public function down(): void
    {
        /*
         * The migration cannot be reversed when external transfers
         * already exist because external transfers have no receiver_id.
         */
        if (
            DB::table('wallet_transfers')
                ->whereNull('receiver_id')
                ->exists()
        ) {
            throw new RuntimeException(
                'Cannot rollback because external wallet transfers exist.'
            );
        }

        Schema::table('wallet_transfers', function (Blueprint $table) {
            $table->dropIndex(['transfer_type']);
            $table->dropColumn('transfer_type');
        });

        DB::statement(
            'ALTER TABLE wallet_transfers
             MODIFY receiver_id BIGINT UNSIGNED NOT NULL'
        );
    }
};