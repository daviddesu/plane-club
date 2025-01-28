<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->renameColumn('aircraft_log_id', 'sighting_id');
            $table->foreign('sighting_id')
                ->references('id')
                ->on('sightings')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropForeign(['sighting_id']);
            $table->renameColumn('sighting_id', 'aircraft_log_id');
        });
    }
};
