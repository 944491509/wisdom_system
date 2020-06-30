<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemNotificationsGradesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_notifications_grades', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('system_notifications_id')->comment('消息ID');
            $table->integer('grade_id')->default(0)->comment('班级ID 默认为0 ');
            $table->timestamps();
        });
        DB::statement(" ALTER TABLE system_notifications_grades comment '系统消息与班级关联表' ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_notifications_grades');
    }
}
