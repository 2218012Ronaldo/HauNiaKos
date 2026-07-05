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
        // Convert boarding_houses.price from integer (IDR) to decimal (USD)
        Schema::table('boarding_houses', function (Blueprint $table) {
            $table->decimal('price', 10, 2)->change();
        });

        // Convert rooms.price_per_month from integer (IDR) to decimal (USD)
        Schema::table('rooms', function (Blueprint $table) {
            $table->decimal('price_per_month', 10, 2)->change();
        });

        // Convert existing IDR prices to USD (divide by 17500)
        \DB::statement('UPDATE boarding_houses SET price = price / 17500 WHERE price IS NOT NULL');
        \DB::statement('UPDATE rooms SET price_per_month = price_per_month / 17500 WHERE price_per_month IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Convert back to IDR (multiply by 17500)
        \DB::statement('UPDATE boarding_houses SET price = price * 17500 WHERE price IS NOT NULL');
        \DB::statement('UPDATE rooms SET price_per_month = price_per_month * 17500 WHERE price_per_month IS NOT NULL');

        // Convert back to integer
        Schema::table('boarding_houses', function (Blueprint $table) {
            $table->integer('price')->change();
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->integer('price_per_month')->change();
        });
    }
};