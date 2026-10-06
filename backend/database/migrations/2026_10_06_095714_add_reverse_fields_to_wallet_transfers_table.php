<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_transfers', function (Blueprint $table) {
            // completed = normal transfer, reversed = refunded by admin
            if (! Schema::hasColumn('wallet_transfers', 'status')) {
                $table->string('status', 20)->default('completed')->after('note');
                $table->index('status');
            }

            if (! Schema::hasColumn('wallet_transfers', 'reversed_at')) {
                $table->timestamp('reversed_at')->nullable()->after('status');
            }

            if (! Schema::hasColumn('wallet_transfers', 'reversed_by')) {
                $table->foreignId('reversed_by')
                    ->nullable()
                    ->after('reversed_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('wallet_transfers', 'reverse_reason')) {
                $table->string('reverse_reason', 500)->nullable()->after('reversed_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transfers', function (Blueprint $table) {
            if (Schema::hasColumn('wallet_transfers', 'reversed_by')) {
                $table->dropConstrainedForeignId('reversed_by');
            }

            if (Schema::hasColumn('wallet_transfers', 'status')) {
                $table->dropIndex(['status']);
            }

            foreach (['reverse_reason', 'reversed_at', 'status'] as $column) {
                if (Schema::hasColumn('wallet_transfers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};