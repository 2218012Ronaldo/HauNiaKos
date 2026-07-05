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
         Schema::table('boarding_houses', function (Blueprint $table) {
        $table->text('rules')
              ->after('description');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('boarding_houses', function (Blueprint $table) {
        $table->dropColumn('rules');
        });
    }
};
