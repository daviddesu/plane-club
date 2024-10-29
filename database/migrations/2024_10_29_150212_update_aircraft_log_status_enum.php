<?php

use App\Enums\FlyingStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('aircraft_logs', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->enum("status", [FlyingStatus::DEPARTING->value, FlyingStatus::IN_FLIGHT->value, FlyingStatus::ARRIVING->value])->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('aircfraft_logs', function (Blueprint $table) {

        });
    }
};
