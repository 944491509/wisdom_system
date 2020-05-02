<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSimpleaclMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('simpleacl_menus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('菜单名称');
            $table->tinyInteger('type')->comment('菜单分类');
            $table->unsignedBigInteger('parent_id')->default(0)->comment('上级id');
            $table->smallInteger('sort')->default(0)->comment('排序权重');
            $table->string('icon')->nullable()->comment('图标');
            $table->string('href')->nullable()->comment('点击事件');
            $table->tinyInteger('need_uuid')->default(0)->comment('是否需要uuid');
            $table->string('param')->nullable()->comment('附加参数');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('simpleacl_menus');
    }
}
