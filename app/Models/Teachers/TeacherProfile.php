<?php

namespace App\Models\Teachers;

use App\Models\Acl\Role;
use App\Models\Students\StudentProfile;
use App\User;
use Illuminate\Database\Eloquent\Model;

class TeacherProfile extends Model
{
    protected $fillable = [
        'uuid', 'user_id', 'name', 'school_id', 'serial_number', 'gender', 'title', 'id_number',
        'political_name', 'nation_name', 'education', 'degree', 'birthday', 'joined_at', 'avatar',
        'work_start_at', 'major', 'final_education', 'final_major', 'title_start_at', 'hired_at',
        'hired', 'notes', 'category_teach', 'mode', 'resident', 'party_time', 'home_address',
        'graduation_school', 'graduation_time', 'final_degree', 'final_graduation_school',
        'final_graduation_time', 'mode', 'resident', 'party_time', 'home_address', 'education',
        'degree', 'graduation_school', 'graduation_time', 'final_degree', 'final_graduation_school',
        'final_graduation_time'
    ];

    public $dates = ['joined_at'];

    /**
     * 获取教师ID
     * @return mixed
     */
    public function getTeacherId()
    {
        return $this->user_id;
    }

    /**
     * 获取教师所属学校ID
     * @return mixed
     */
    public function getTeacherSchoolId()
    {
        return $this->school_id;
    }


    public function user() {
        return $this->belongsTo(User::class);
    }

    public function getAvatarAttribute($value){
       if ($this->gender == StudentProfile::GENDER_MAN) {
            return asset(empty($value) ? User::DEFAULT_USER_AVATAR : $value);
        } else {
            return asset(empty($value) ? User::DEFAULT_USER_GIRL_AVATAR : $value);
        }
    }
}
