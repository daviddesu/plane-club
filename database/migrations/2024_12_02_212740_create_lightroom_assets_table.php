<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLightroomAssetsTable extends Migration
{
    public function up()
    {
        Schema::create('lightroom_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('aircraft_log_id')->constrained();
            $table->string('asset_id');
            $table->string('album_id')->nullable();
            $table->string('file_name')->nullable();
            $table->string('media_type')->nullable(); // 'image' or 'video'
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('lightroom_assets');
    }
}

