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
        Schema::table('criteria_weights', function (Blueprint $table) {
            $table->renameColumn('criteria_Id', 'criteria_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('criteria_weights', function (Blueprint $table) {
            $table->renameColumn('criteria_id', 'criteria_Id');
        });
    }
};
