<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUsingMorningToTeacherAttendancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teacher_attendances', function (Blueprint $table) {
            //
            $table->boolean('using_morning')->default(false)->comment('启用上午下班打卡 1启用');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('teacher_attendances', function (Blueprint $table) {
            //
            $table->dropColumn('using_morning');
        });
    }
}
