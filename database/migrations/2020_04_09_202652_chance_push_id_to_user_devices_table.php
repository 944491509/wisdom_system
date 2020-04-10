<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChancePushIdToUserDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_devices', function (Blueprint $table) {
            //
            DB::statement('ALTER TABLE `user_devices` CHANGE `push_id` `push_id` VARCHAR(100) DEFAULT \'\'  NULL  COMMENT \'极光推送ID\';');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_devices', function (Blueprint $table) {
            //
            DB::statement('ALTER TABLE `user_devices` CHANGE `push_id` `push_id` VARCHAR(100) DEFAULT \'0\'  NOT NULL  COMMENT \'极光推送ID\';');
        });
    }
}
