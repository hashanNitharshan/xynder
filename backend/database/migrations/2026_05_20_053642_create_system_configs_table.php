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
    Schema::create('system_configs', function (Blueprint $table) {
        $table->id();
        $table->decimal('usd_rate', 15, 4)->default(1);
        $table->decimal('inr_rate', 15, 4)->default(1);
        $table->decimal('xynder_fee', 15, 2)->default(0);
        $table->decimal('network_fee', 15, 2)->default(0);
        $table->date('effective_date')->default(now());
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_configs');
    }
};
