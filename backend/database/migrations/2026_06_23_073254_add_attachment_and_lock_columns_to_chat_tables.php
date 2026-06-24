<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('chat_messages', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('message');
            }

            if (! Schema::hasColumn('chat_messages', 'attachment_url')) {
                $table->string('attachment_url')->nullable()->after('attachment_path');
            }

            if (! Schema::hasColumn('chat_messages', 'attachment_name')) {
                $table->string('attachment_name')->nullable()->after('attachment_url');
            }

            if (! Schema::hasColumn('chat_messages', 'attachment_mime')) {
                $table->string('attachment_mime')->nullable()->after('attachment_name');
            }

            if (! Schema::hasColumn('chat_messages', 'attachment_size')) {
                $table->unsignedBigInteger('attachment_size')->nullable()->after('attachment_mime');
            }

            if (! Schema::hasColumn('chat_messages', 'attachment_type')) {
                $table->string('attachment_type')->nullable()->after('attachment_size');
            }
        });

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
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn([
                'attachment_path',
                'attachment_url',
                'attachment_name',
                'attachment_mime',
                'attachment_size',
                'attachment_type',
            ]);
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn([
                'chat_started_at',
                'locked_at',
                'lock_reason',
            ]);
        });
    }
};