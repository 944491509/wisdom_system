<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeToSimpleaclPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('simpleacl_permissions', function (Blueprint $table) {
            //
            $table->tinyInteger('type')->comment('权限分类');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('simpleacl_permissions', function (Blueprint $table) {
            //
            $table->dropColumn('type');
        });
    }
}
