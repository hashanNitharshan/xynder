<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_one_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('user_two_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('wallet_transfer_id')
                  ->nullable()
                  ->constrained('wallet_transfers')
                  ->cascadeOnDelete();

            $table->foreignId('wallet_request_id')
                  ->nullable()
                  ->constrained('wallet_requests')
                  ->cascadeOnDelete();

            $table->timestamps();

            // Each transfer gets exactly ONE conversation
            $table->unique(['wallet_transfer_id']);

            // Each request gets exactly ONE conversation
            $table->unique(['wallet_request_id']);

            // NO unique constraint on (user_one_id, user_two_id)
            // because same two users can have MANY transaction chats
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};