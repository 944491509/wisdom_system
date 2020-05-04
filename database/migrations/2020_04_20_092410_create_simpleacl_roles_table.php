<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSimpleaclRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('simpleacl_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('权限组名称');
            $table->tinyInteger('type')->comment('权限组分类');
            $table->text('description')->comment('权限组描述');
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
        Schema::dropIfExists('simpleacl_roles');
    }
}
