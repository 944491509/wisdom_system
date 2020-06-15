<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateTeacherStudentProfileDelField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->dropColumn('group_name');
            $table->dropColumn('political_code');
            $table->dropColumn('nation_code');
            $table->dropColumn('famous');
            $table->dropColumn('category_major');
            $table->dropColumn('title1_at');
            $table->dropColumn('title1_hired_at');
            $table->string('mode', 20)->nullable()->comment('聘任方式');
            $table->string('resident', 50)->nullable()->comment('户籍所在地');
            $table->string('party_time', 40)->nullable()->comment('入党时间');
            $table->string('home_address')->nullable()->comment('家庭住址');
            $table->string('education', 20)->nullable()->comment('第一学历')->nullable()->change();
            $table->string('degree', 20)->nullable()->comment('第一学位')->nullable()->change();
            $table->string('graduation_school', 40)->nullable()->comment('第一毕业学校');
            $table->string('graduation_time', 40)->nullable()->comment('第一毕业时间');
            $table->string('final_degree', 20)->nullable()->comment('最高学位');
            $table->string('final_graduation_school', 40)->nullable()->comment('最高毕业学校');
            $table->string('final_graduation_time', 40)->nullable()->comment('最高毕业时间');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('teacher_profiles', function (Blueprint $table) {
            $table->string('group_name')->nullable();
            $table->string('political_code')->nullable();
            $table->string('nation_code')->nullable();
            $table->string('famous')->nullable();
            $table->string('category_major')->nullable();
            $table->string('title1_at')->nullable();
            $table->string('title1_hired_at')->nullable();
            $table->string('education', 20)->comment('第一学历')->change();
            $table->string('degree', 20)->comment('第一学位')->change();
            $table->dropColumn('graduation_school');
            $table->dropColumn('graduation_time');
            $table->dropColumn('final_graduation_school');
            $table->dropColumn('final_graduation_time');
            $table->dropColumn('resident');
            $table->dropColumn('party_time');
            $table->dropColumn('home_address');
            $table->dropColumn('mode');
            $table->dropColumn('final_degree');
        });
    }
}
