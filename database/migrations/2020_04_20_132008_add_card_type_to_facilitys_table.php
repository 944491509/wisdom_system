<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCardTypeToFacilitysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('facilitys', function (Blueprint $table) {
            $table->tinyInteger('card_type')->nullable()->comment('班牌类型 0公共班牌 1独立班牌');
            $table->integer('grade_id')->nullable()->comment('独立班牌所属班级');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('facilitys', function (Blueprint $table) {
            $table->dropColumn('card_type');
            $table->dropColumn('grade_id');
        });
    }
}
