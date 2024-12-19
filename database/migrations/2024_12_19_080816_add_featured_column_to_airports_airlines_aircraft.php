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
        Schema::table('airports', function (Blueprint $table) {
            Schema::table('airports', function (Blueprint $table) {
                $table->tinyInteger('featured')->default(0);
            });

            Schema::table('aircraft', function (Blueprint $table) {
                $table->tinyInteger('featured')->default(0);
            });

            Schema::table('airlines', function (Blueprint $table) {
                $table->tinyInteger('featured')->default(0);
            });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
