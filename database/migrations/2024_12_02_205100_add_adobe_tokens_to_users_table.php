<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAdobeTokensToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('adobe_access_token')->nullable();
            $table->string('adobe_refresh_token')->nullable();
            $table->timestamp('adobe_token_expires_in')->nullable();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'adobe_access_token',
                'adobe_refresh_token',
                'adobe_token_expires_in',
            ]);
        });
    }
}
