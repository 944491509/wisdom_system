<?php

namespace App\Dao\Teachers;

use App\Models\Teachers\TeacherProfile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class ShoolDao
 * @package App\Dao\Users
 */
class TeacherProfileDao
{
    /**
     * 根据 uuid 或者 id 获取教师详情
     * @param $teacherIdOrUuid
     * @return TeacherProfile|null
     */
    public  function getTeacherProfileByTeacherIdOrUuid($teacherIdOrUuid)
    {
        if(is_string($teacherIdOrUuid) && strlen($teacherIdOrUuid) > 10){
            return TeacherProfile::where('uuid', $teacherIdOrUuid)->first();
        }
        elseif (is_int($teacherIdOrUuid)){
            return TeacherProfile::where('user_id', $teacherIdOrUuid)->first();
        }
        return null;
    }

    /**
     * @param $name
     * @param $schoolId
     * @param $majorsId
     * @return \Illuminate\Support\Collection
     */
    public function searchTeacherByNameSimple($name, $schoolId, $majorsId = []){
        if(!empty($majorsId)){
            return DB::table('grade_users')
                ->select(DB::raw('user_id as id, name'))
                ->where('school_id',$schoolId)
                ->whereIn('major_id',$majorsId)
                ->where('name','like',$name.'%')
                ->get();
        }
        return DB::table('grade_users')
            ->select(DB::raw('user_id as id, name'))
            ->where('school_id',$schoolId)
            ->where('name','like',$name.'%')
            ->get();
    }

    /**
     * 创建老师的 Profile 模型
     * @param $data
     * @return TeacherProfile
     */
    public function createProfile($data){
        return TeacherProfile::create($data);
    }

    /**
     * 获取老师列表
     * @param $map
     * @param $field
     * @return Collection
     */
    public function getTeachers($map,$field)
    {
        $list = TeacherProfile::where($map)->select($field)->with('users')->get();
        return $list;
    }
}
