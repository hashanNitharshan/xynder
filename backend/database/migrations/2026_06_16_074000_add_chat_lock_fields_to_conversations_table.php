<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (! Schema::hasColumn('conversations', 'chat_started_at')) {
                $table->timestamp('chat_started_at')->nullable()->after('wallet_request_id');
            }

            if (! Schema::hasColumn('conversations', 'locked_at')) {
                $table->timestamp('locked_at')->nullable()->after('chat_started_at');
            }

            if (! Schema::hasColumn('conversations', 'lock_reason')) {
                $table->string('lock_reason')->nullable()->after('locked_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn([
                'chat_started_at',
                'locked_at',
                'lock_reason',
            ]);
        });
    }
};