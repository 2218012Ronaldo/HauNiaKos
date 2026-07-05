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
        Schema::table('transactions', function (Blueprint $table) {
            $table
                ->enum('approval_status', ['pending_owner', 'approved', 'rejected'])
                ->default('pending_owner')
                ->after('payment_status');
            $table->timestamp('approved_at')->nullable()->after('approval_status');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'approved_at', 'rejected_at']);
        });     
    }
};
