<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToSchoolConfigurationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('school_configurations', function (Blueprint $table) {
            //
            $table->tinyInteger('year')->default(3)->comment('年制');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('school_configurations', function (Blueprint $table) {
            //
            $table->dropColumn('year');
        });
    }
}
