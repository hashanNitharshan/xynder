<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('wallet_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('wallet_requests', 'transaction_no')) {
                $table->string('transaction_no')->nullable()->unique()->after('id');
            }
        });

        Schema::table('wallet_transfers', function (Blueprint $table) {
            if (!Schema::hasColumn('wallet_transfers', 'transaction_no')) {
                $table->string('transaction_no')->nullable()->unique()->after('id');
            }
        });

        DB::table('wallet_requests')->orderBy('id')->get()->each(function ($r) {
            DB::table('wallet_requests')
                ->where('id', $r->id)
                ->update(['transaction_no' => 'TNS' . str_pad($r->id, 9, '0', STR_PAD_LEFT)]);
        });

        DB::table('wallet_transfers')->orderBy('id')->get()->each(function ($t) {
            DB::table('wallet_transfers')
                ->where('id', $t->id)
                ->update(['transaction_no' => 'TRA' . str_pad($t->id, 9, '0', STR_PAD_LEFT)]);
        });
    }

    public function down(): void
    {
        Schema::table('wallet_requests', function (Blueprint $table) {
            $table->dropColumn('transaction_no');
        });

        Schema::table('wallet_transfers', function (Blueprint $table) {
            $table->dropColumn('transaction_no');
        });
    }
};