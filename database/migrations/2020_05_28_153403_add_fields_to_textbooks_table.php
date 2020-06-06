<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToTextbooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('textbooks', function (Blueprint $table) {
            //
            $table->tinyInteger('year')->default(1)->comment('年级');
            $table->tinyInteger('term')->default(1)->comment('学期 1:上学期 2:下学期');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('textbooks', function (Blueprint $table) {
            //
            $table->dropColumn('year');
            $table->dropColumn('term');
        });
    }
}
