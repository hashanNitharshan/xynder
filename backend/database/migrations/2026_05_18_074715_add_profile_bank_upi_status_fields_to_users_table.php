<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'country')) {
                $table->string('country')->nullable()->after('address');
            }

            if (!Schema::hasColumn('users', 'card_number')) {
                $table->string('card_number')->nullable()->after('country');
            }

            if (!Schema::hasColumn('users', 'photo')) {
                $table->string('photo')->nullable()->after('card_number');
            }

            if (!Schema::hasColumn('users', 'account_type')) {
                $table->string('account_type')->nullable()->after('account_number');
            }

            if (!Schema::hasColumn('users', 'ifsc')) {
                $table->string('ifsc')->nullable()->after('account_type');
            }

            if (!Schema::hasColumn('users', 'upi_name')) {
                $table->string('upi_name')->nullable()->after('ifsc');
            }

            if (!Schema::hasColumn('users', 'upi_id')) {
                $table->string('upi_id')->nullable()->after('upi_name');
            }

            if (!Schema::hasColumn('users', 'upi_qr')) {
                $table->string('upi_qr')->nullable()->after('upi_id');
            }

            if (!Schema::hasColumn('users', 'original_name')) {
                $table->string('original_name')->nullable()->after('name');
            }

            if (!Schema::hasColumn('users', 'state')) {
                $table->string('state')->nullable()->after('country');
            }

            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('active')->after('role');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'country',
                'card_number',
                'photo',
                'account_type',
                'ifsc',
                'upi_name',
                'upi_id',
                'upi_qr',
                'original_name',
                'state',
                'status',
            ]);
        });
    }
};