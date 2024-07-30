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
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aircraft_logs', function (Blueprint $table) {
            $table->bigInteger('aircraft_id');
            $table->bigInteger('airport_id');
            $table->dateTime("spotted_time", precision: 0);
            $table->foreign('aircraft_id')->references('id')->on('aircraft')->cascadeOnDelete();
            $table->foreign('airport_id')->references('id')->on('airports')->cascadeOnDelete();
        });
    }
};
