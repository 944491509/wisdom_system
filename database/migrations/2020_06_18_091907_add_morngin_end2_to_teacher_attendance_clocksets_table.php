<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMornginEnd2ToTeacherAttendanceClocksetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teacher_attendance_clocksets', function (Blueprint $table) {
            //
            $table->time('morning_end2')->nullable(true)->comment('上午下班结束');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('teacher_attendance_clocksets', function (Blueprint $table) {
            //
            $table->dropColumn('morning_end2');
        });
    }
}
