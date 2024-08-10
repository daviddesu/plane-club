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
        Schema::table('aircraft_logs', function (Blueprint $table) {
            $table->bigInteger('airport_id');
            $table->foreign('airport_id')->references('id')->on('airports')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aircraft_logs', function (Blueprint $table) {
            //
        });
    }
};
