<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\FlyingStatus;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('aircraft_logs', function (Blueprint $table) {
            $table->bigInteger('departure_airport_id')->nullable();
            $table->foreign('departure_airport_id')->references('id')->on('airports')->cascadeOnDelete();
            $table->renameColumn('airport_id', 'arrival_airport_id');
            $table->enum("status", [FlyingStatus::DEPARTING->value, FlyingStatus::IN_FLIGHT->value, FlyingStatus::DEPARTING->value])->nullable();
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
