<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSimpleaclPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('simpleacl_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->comment('权限名称');
            $table->string('router')->comment('权限节点');
            $table->unsignedBigInteger('simpleacl_menu_id')->comment('菜单id');

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
        Schema::dropIfExists('simpleacl_permissions');
    }
}
