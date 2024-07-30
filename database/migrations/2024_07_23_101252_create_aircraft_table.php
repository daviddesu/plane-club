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

        Schema::create('aircraft', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("aircraft_type_id");
            $table->foreign("aircraft_type_id")->references("id")->on("aircraft_types")->cascadeOnDelete();
            $table->string("registration");
            $table->string("special_livery");
            $table->dateTime("manufacture_date", precision: 0);
            $table->dateTime("entered_service", precision: 0);
            $table->dateTime("retirement_date", precision: 0);
            $table->timestamps();
        });

        Schema::create('aircraft_airline', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("aircraft_id");
            $table->bigInteger("airline_id");
            $table->foreign("aircraft_id")->references("id")->on("aircraft")->cascadeOnDelete();
            $table->foreign("airline_id")->references("id")->on("airlines")->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aircraft');
    }
};
