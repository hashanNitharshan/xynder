<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('wallet_id')->nullable()->unique()->after('balance');
        });

        DB::table('users')->orderBy('id')->each(function ($user) {
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'wallet_id' => 'XYW' . str_pad($user->id, 5, '0', STR_PAD_LEFT) . strtoupper(Str::random(5)),
                ]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['wallet_id']);
            $table->dropColumn('wallet_id');
        });
    }
};