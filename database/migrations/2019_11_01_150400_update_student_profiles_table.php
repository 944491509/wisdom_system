<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateStudentProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            // 报名信息
            $table->text('source_place')->nullable()->comment('生源地');
            $table->string('country', '50')->nullable()->comment('籍贯')->change();
            $table->date('birthday')->nullable()->comment('出生年月日')->change();
            $table->string('qq', '20')->nullable()->comment('QQ号');
            $table->string('wx', '30')->nullable()->comment('微信号');
            $table->string('examination_score', '11')->nullable()->comment('中/高考分数');
            // 家长信息
            $table->string('parent_name', '50')->nullable()->comment('家长姓名');
            $table->string('parent_mobile', '11')->nullable()->comment('家长手机号');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
