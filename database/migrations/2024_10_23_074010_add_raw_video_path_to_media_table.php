<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->string('raw_video_path')->nullable()->after('thumbnail_path');
        });
    }

    public function down()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('raw_video_path');
        });
    }

};
