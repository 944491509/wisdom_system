<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdFieldToSchoolCalendarsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_calendars', function (Blueprint $table) {
            //
            $table->dropColumn('tag');
            $table->text('type')->comment('事件类型ID json')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school_calendars', function (Blueprint $table) {
            //
            $table->text('tag')->nullable()->comment('事件标签');
            $table->dropColumn('type');
        });
    }
}
