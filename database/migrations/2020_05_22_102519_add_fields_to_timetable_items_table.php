<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToTimetableItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('timetable_items', function (Blueprint $table) {
            //
            $table->tinyInteger('type')->default(0)->comment('类型 0:正常 1:代课 2:调课');
            $table->integer('substitute_id')->default(0)->comment('调课ID');
            $table->tinyInteger('initiative')->default(1)->comment('调课主动和被动 0被动 1主动');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('timetable_items', function (Blueprint $table) {
            //
            $table->dropColumn('type');
            $table->dropColumn('substitute_id');
            $table->dropColumn('initiative');
        });
    }
}
