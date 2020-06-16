<?php

namespace App\Exports;

use App\Models\Acl\Role;
use App\Models\Teachers\TeacherProfile;
use App\User;
use Maatwebsite\Excel\Concerns\FromCollection;

class TeacherExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $category_teach = [
            1 => '文化课',
            2 => '专业课',
            3 => '实训课',
            4 => '其他'
        ];

        $teachers = User::whereIn('type', Role::GetTeacherUserTypes())->join('teacher_profiles', 'users.id', '=', 'teacher_profiles.user_id')->get();
        foreach ($teachers as $key => $row) {
            $output[$key]['name'] = $row->name;
            $output[$key]['gender'] = $row->gender == 1 ?'男':'女';
            $output[$key]['nation_name'] = $row->nation_name;
            $output[$key]['birthday'] = $row->birthday;
            $output[$key]['serial_number'] = $row->serial_number;
            $output[$key]['mobile'] = $row->mobile;
            $output[$key]['id_number'] = $row->id_number."\t";
            $output[$key]['resident'] = $row->resident;
            $output[$key]['political_name'] = $row->political_name;
            $output[$key]['party_time'] = $row->party_time;
            $output[$key]['home_address'] = $row->home_address;
            $output[$key]['education'] = $row->education;
            $output[$key]['major'] = $row->major;
            $output[$key]['degree'] = $row->degree;
            $output[$key]['graduation_school'] = $row->graduation_school;
            $output[$key]['graduation_time'] = $row->graduation_time;
            $output[$key]['graduation_time'] = $row->graduation_time;
            $output[$key]['final_education'] = $row->final_education;
            $output[$key]['final_major'] = $row->final_major;
            $output[$key]['final_degree'] = $row->final_degree;
            $output[$key]['final_graduation_school'] = $row->final_graduation_school;
            $output[$key]['final_graduation_time'] = $row->final_graduation_time;
            $output[$key]['title'] = $row->title;
            $output[$key]['title_start_at'] = $row->title_start_at;
            $output[$key]['work_start_at'] = $row->work_start_at;
            $output[$key]['hired_at'] = $row->hired_at;
            $output[$key]['status'] = $row->getTeacherText();
            $output[$key]['mode'] = $row->mode;
            $output[$key]['category_teach'] = $row->category_teach?$category_teach[$row->category_teach]:'';
            $output[$key]['notes'] = $row->notes;
        }
        $arr = [
            '姓名','性别','民族','出生日期','教师编号','手机号','身份证号','户籍所在地','政治面貌','入党时间','家庭住址',
            '第一学历','第一学历专业', '学位', '毕业学校', '毕业时间','最高学历', '最高学历专业','学位', '毕业学校', '毕业时间',
            '目前职称', '职称获取时间', '参加工作时间', '本校聘任时间', '聘任状态', '聘任方式', '授课类别', '备注'
        ];
        array_unshift($output, $arr);
        return collect($output);
    }
}
