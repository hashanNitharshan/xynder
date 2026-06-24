<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up(): void
{
    Schema::table('wallet_requests', function (Blueprint $table) {
        $table->decimal('usd_rate', 15, 4)->default(1)->after('amount');
        $table->decimal('inr_rate', 15, 4)->default(1)->after('usd_rate');
        $table->decimal('xynder_fee', 15, 2)->default(0)->after('inr_rate');
        $table->decimal('network_fee', 15, 2)->default(0)->after('xynder_fee');
        $table->decimal('converted_amount', 15, 2)->default(0)->after('network_fee');
    });
}

public function down(): void
{
    Schema::table('wallet_requests', function (Blueprint $table) {
        $table->dropColumn([
            'usd_rate',
            'inr_rate',
            'xynder_fee',
            'network_fee',
            'converted_amount',
        ]);
    });
}
};
