<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateStudentAdditionInformationInType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_addition_information', function (Blueprint $table) {
            $table->integer('borrow_type')->default(0)->comment('寄宿类型');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_addition_information', function (Blueprint $table) {
            $table->dropColumn('borrow_type');
        });
    }
}
