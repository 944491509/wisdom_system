<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStudentAdditionaInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('student_addition_information', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->comment('学生id');
            $table->text('reward')->nullable()->comment('奖励记录');
            $table->text('punishment')->nullable()->comment('惩罚记录');
            $table->string('people')->nullable()->comment('寄宿联系人');
            $table->string('mobile', 11)->nullable()->comment('寄宿联系人');
            $table->string('address')->nullable()->comment('寄宿地址');
            $table->timestamps();
        });

        DB::statement(" ALTER TABLE student_addition_information comment '学生附加信息表' ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('student_addition_information');
    }
}
