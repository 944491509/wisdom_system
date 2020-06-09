<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateStudentProfilesDelFieldAndAddField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn('postcode');
            $table->dropColumn('political_code');
            $table->dropColumn('nation_code');
            $table->dropColumn('qr_code_url');
            $table->dropColumn('qr_code_url_tmp');
            $table->dropColumn('account_money');
            $table->dropColumn('red_envelope');
            $table->dropColumn('source_place');
            $table->dropColumn('special_support');
            $table->dropColumn('very_poor');
            $table->dropColumn('disability');
            $table->dropColumn('state');
            $table->dropColumn('city');
            $table->dropColumn('area');

            $table->string('student_code', 100)->nullable()->comment('学籍号');
            $table->integer('health_status')->default(0)->comment('健康状态');
            $table->string('graduate_school', 100)->nullable()->comment('毕业学校');
            $table->string('graduate_type', 50)->nullable()->comment('毕业来源 应届 往届');
            $table->integer('cooperation_type')->default(0)->comment('联招合作类型');
            $table->string('source_place_state')->nullable()->comment('生源地 省');
            $table->string('source_place_city')->nullable()->comment('生源地 市');
            $table->string('recruit_type')->nullable()->comment('招生方式 统一招生, 自主招生, 其他');
            $table->string('volunteer')->nullable()->comment('报考志愿');
            $table->string('examination_site')->nullable()->comment('考点');
            $table->string('resident_state', 50)->nullable()->comment('户籍所在省');
            $table->string('resident_city', 50)->nullable()->comment('户籍所在市');
            $table->string('resident_area', 50)->nullable()->comment('户籍所在区');
            $table->string('resident_suburb', 50)->nullable()->comment('户籍所在乡镇')->change();
            $table->string('resident_village', 50)->nullable()->comment('户籍所在村')->change();
            $table->string('detailed_address')->nullable()->comment('户籍详细地址')->change();
            $table->integer('family_poverty_status')->default(0)->comment('家庭贫困状况');
            $table->string('zip_code', 100)->nullable()->comment('家庭地址邮编');
            $table->integer('residence_type')->default(0)->comment('学生居住地类型');
            $table->string('current_residence')->nullable()->comment('现在居住地');
            $table->integer('relationship')->default(0)->comment('与本人关系');
            $table->date('enrollment_at')->nullable()->comment('入学年月');
            $table->string('learning_form')->nullable()->comment('学习形式 全日志, 非全日制');
            $table->integer('educational_system')->default(0)->comment('学制');
            $table->integer('entrance_type')->default(0)->comment('入学方式');
            $table->integer('student_type')->default(0)->comment('学生类别');
            $table->integer('segmented_type')->default(0)->comment('分段培养方式');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('postcode')->nullable();
            $table->string('political_code')->nullable();
            $table->string('nation_code')->nullable();
            $table->string('qr_code_url')->nullable();
            $table->string('qr_code_url_tmp')->nullable();
            $table->string('account_money')->nullable();
            $table->string('red_envelope')->nullable();
            $table->string('source_place')->nullable();
            $table->string('special_support')->nullable();
            $table->string('very_poor')->nullable();
            $table->string('disability')->nullable();
            $table->string('detailed_address', 100)->nullable()->change();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('area')->nullable();

            $table->dropColumn('graduate_type');
            $table->dropColumn('student_code');
            $table->dropColumn('graduate_school');
            $table->dropColumn('cooperation_type');
            $table->dropColumn('source_place_state');
            $table->dropColumn('source_place_city');
            $table->dropColumn('recruit_type');
            $table->dropColumn('volunteer');
            $table->dropColumn('examination_site');
            $table->dropColumn('resident_state');
            $table->dropColumn('resident_city');
            $table->dropColumn('resident_area');
            $table->dropColumn('family_poverty_status');
            $table->dropColumn('zip_code');
            $table->dropColumn('residence_type');
            $table->dropColumn('current_residence');
            $table->dropColumn('relationship');
            $table->dropColumn('enrollment_at');
            $table->dropColumn('learning_form');
            $table->dropColumn('educational_system');
            $table->dropColumn('entrance_type');
            $table->dropColumn('student_type');
            $table->dropColumn('segmented_type');
            $table->dropColumn('health_status');

        });
    }
}
