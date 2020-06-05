<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateOaProjectTaskFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('oa_project_task_files', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('task_id')->comment('任务ID');
            $table->string('url',150)->comment('路径');
            $table->string('file_name',100)->comment('文件名');
        });
        DB::statement(" ALTER TABLE oa_project_task_files comment '任务图片' ");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('oa_project_task_files');
    }
}
