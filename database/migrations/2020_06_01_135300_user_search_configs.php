<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UserSearchConfigs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_search_configs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('名称');
            $table->tinyInteger('type')->comment('类型 0:民族 1:政治面貌 2:学历 3:学位 4:目前职称 5:聘任状态 6:聘任方式');
        });

        DB::statement(" ALTER TABLE user_search_configs comment '搜索条件配置表' ");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       Schema::dropIfExists('user_search_configs');
    }
}
