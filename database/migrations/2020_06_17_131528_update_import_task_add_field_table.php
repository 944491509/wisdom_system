<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateImportTaskAddFieldTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import_task', function (Blueprint $table) {
            $table->dropColumn('file_path');
            $table->dropColumn('file_info');
            $table->dropColumn('config');
            $table->smallInteger('status')->default(0)->comment('0:等待中, 1:导入中, 2:已完成 3:撤回')->change();
            $table->string('path')->comment('文件路径');
            $table->string('file_name')->comment('文件原始名称');
            $table->integer('total')->default(0)->comment('已导入条数');
            $table->integer('surplus')->default(0)->comment('未导入条数');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('import_task', function (Blueprint $table) {
            $table->string('file_path');
            $table->string('config');
            $table->string('file_info');
            $table->smallInteger('status')->change();
            $table->dropColumn('path');
            $table->dropColumn('file_name');
            $table->dropColumn('total');
            $table->dropColumn('surplus');
        });
    }
}
