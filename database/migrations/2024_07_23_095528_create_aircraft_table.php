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
        Schema::create('manufacturers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('aircraft_types', function (Blueprint $table) {
            $table->id();
            $table->string("model");
            $table->string("series");
            $table->string("varient");
            $table->bigInteger("manufacturer_id");
            $table->foreign('manufacturer_id')->references('id')->on('manufacturers')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manufacturers');
        Schema::dropIfExists('aircraft_types');
    }
};
