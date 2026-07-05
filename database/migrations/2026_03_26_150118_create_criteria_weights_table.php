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
        Schema::create('criteria_weights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criteria_id')->constrained('criteria')->cascadeOnDelete()->unique();
            $table->decimal('weight', 8, 4);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('criteria_weights');
    }
};
