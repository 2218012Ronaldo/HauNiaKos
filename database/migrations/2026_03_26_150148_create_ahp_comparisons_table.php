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
        Schema::create('ahp_comparisons', function (Blueprint $table) {
            $table->id();
             $table->foreignId('criteria_id_1')->constrained('criteria')->cascadeOnDelete();
             $table->foreignId('criteria_id_2')->constrained('criteria')->cascadeOnDelete();
             $table->decimal('value', 8, 3);
             $table->unique(['criteria_id_1', 'criteria_id_2']);
             $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ahp_comparisons');
    }
};
