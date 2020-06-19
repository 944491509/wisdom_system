<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNoticeGradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notice_grades', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('notice_id')->comment('消息ID');
            $table->integer('grade_id')->comment('班级ID');
        });
        DB::statement(" ALTER TABLE notice_grades comment '消息通知班级表' ");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notice_grades');
    }
}
