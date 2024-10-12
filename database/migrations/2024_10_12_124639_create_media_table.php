<?php

use App\Enums\Media;
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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->enum("type", [Media::IMAGE->value, Media::VIDEO->value]);
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string("path");
            $table->bigInteger('aircraft_log_id')->nullable();
            $table->foreign('aircraft_log_id')->references('id')->on('aircraft_logs')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
