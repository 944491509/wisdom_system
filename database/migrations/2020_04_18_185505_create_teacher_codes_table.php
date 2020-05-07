<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTeacherCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teacher_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->comment('设备名称');
            $table->string('type')->comment('设备类型');
            $table->tinyInteger('status')->comment('状态');
            $table->timestamps();
        });
        DB::statement(" ALTER TABLE teacher_codes comment '一码通 开通设备表' ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teacher_codes');
    }
}
