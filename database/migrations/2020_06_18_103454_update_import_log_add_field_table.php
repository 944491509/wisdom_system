<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateImportLogAddFieldTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('import_log', function (Blueprint $table) {
           $table->dropColumn('type');
           $table->dropColumn('source');
           $table->dropColumn('target');
           $table->dropColumn('table_name');
           $table->dropColumn('result');
           $table->dropColumn('school_id');
           $table->dropColumn('only_flag');
           $table->dropColumn('task_status');

           $table->integer('number')->nullable()->comment('表格行号');
           $table->string('name')->nullable()->comment('姓名');
           $table->string('id_number')->nullable()->comment('身份证号');
           $table->string('error_log')->nullable()->comment('错误原因');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('import_log', function (Blueprint $table) {
           $table->string('type');
           $table->string('source');
           $table->string('target');
           $table->string('table_name');
           $table->string('result');
           $table->string('school_id');
           $table->string('only_flag');
           $table->string('task_status');

           $table->dropColumn('number');
           $table->dropColumn('name');
           $table->dropColumn('id_number');
           $table->dropColumn('error_log');
        });
    }
}
