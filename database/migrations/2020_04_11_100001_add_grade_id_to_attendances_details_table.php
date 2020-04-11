<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddGradeIdToAttendancesDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('attendances_details', function (Blueprint $table) {
            //
            $table->integer('grade_id')->comment('班级ID');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('attendances_details', function (Blueprint $table) {
            //
            $table->dropColumn('grade_id');
        });
    }
}
