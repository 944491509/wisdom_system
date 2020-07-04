<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSchoolIdToSimpleaclRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('simpleacl_roles', function (Blueprint $table) {
            //
            $table->bigInteger('school_id')->comment('学校id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('simpleacl_roles', function (Blueprint $table) {
            //
            $table->dropColumn('school_id');
        });
    }
}
