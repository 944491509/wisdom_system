<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMorningEndToTeacherAttendanceClocksetsTable extends Migration
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
            $table->time('morning_end')->nullable(true)->comment('上午下班');
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
            $table->dropColumn('morning_end');
        });
    }
}
