<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add separate INR rates for Buy USD (deposit) and Sell USD (withdrawal).
     * The old inr_rate column stays so old data and old app versions keep working.
     */
    public function up(): void
    {
        Schema::table('system_configs', function (Blueprint $table) {
            if (! Schema::hasColumn('system_configs', 'buy_inr_rate')) {
                $table->decimal('buy_inr_rate', 15, 4)->default(1)->after('inr_rate');
            }

            if (! Schema::hasColumn('system_configs', 'sell_inr_rate')) {
                $table->decimal('sell_inr_rate', 15, 4)->default(1)->after('buy_inr_rate');
            }
        });

        // Copy the old single rate into both new columns for all existing rows.
        DB::table('system_configs')->update([
            'buy_inr_rate'  => DB::raw('inr_rate'),
            'sell_inr_rate' => DB::raw('inr_rate'),
        ]);
    }

    public function down(): void
    {
        Schema::table('system_configs', function (Blueprint $table) {
            if (Schema::hasColumn('system_configs', 'sell_inr_rate')) {
                $table->dropColumn('sell_inr_rate');
            }

            if (Schema::hasColumn('system_configs', 'buy_inr_rate')) {
                $table->dropColumn('buy_inr_rate');
            }
        });
    }
};
