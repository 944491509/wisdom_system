<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 18/10/19
 * Time: 9:20 PM
 */

namespace App\Dao\Users;

use App\Models\Acl\Role;
use App\Models\Users\GradeUser;
use App\Utils\Misc\ConfigurationTool;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class GradeUserDao
{
    const TYPE_SELECT = 1;
    const TYPE_UPDATE = 2;

    private $currentUser;

    public function __construct($user = null)
    {
        $this->currentUser = $user;
    }

    public function create($data)
    {
        return GradeUser::create($data);
    }

    /**
     * 模糊查找用户的信息
     * @param $name
     * @param $schoolId
     * @param $userType : 需要限定的用户类型
     * @return Collection
     */
    public function getUsersWithNameLike($name, $schoolId, $userType = null)
    {
        $where = [
            ['school_id', '=', $schoolId],
            ['name', 'like', $name . '%'],
        ];
        $query = GradeUser::select(['id', 'user_id', 'name', 'user_type', 'department_id', 'major_id', 'grade_id'])
            ->where($where);

        if ($userType) {
            if (is_array($userType)) {
                // 如果同时定位多个角色
                $query->whereIn('user_type', $userType);
            } else {
                $query->where('user_type', $userType);
            }
        }

        return $query->get();
    }

    /**
     * 获取用户的学校信息
     * @param null $userId
     * @return \Illuminate\Support\Collection
     */
    public function getSchoolsId($userId = null)
    {
        if (is_null($userId)) {
            $userId = $this->currentUser->id;
        }
        return DB::table('grade_users')->select('school_id')->where('user_id', $userId)->get();
    }


    /**
     * @param $gradeId
     * @return Collection
     */
    public function getGradeUserByGradeId($gradeId)
    {
        return GradeUser::where('grade_id', $gradeId)->get();
    }

    /**
     * @param $grades
     * @return Collection
     */
    public function getGradeUserWhereInGrades($grades)
    {
        return GradeUser::whereIn('grade_id', $grades)->get();
    }

    /**
     * 学生分页
     * @param $gradeId
     * @return mixed'
     */
    public function getGradeUserPageGradeId($gradeId)
    {
        return GradeUser::where('grade_id', $gradeId)
            ->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);
    }

    /**
     * 根据学校获取 id
     * @param $schoolId
     * @param $types
     * @return Collection
     */
    public function getBySchool($schoolId, $types)
    {
        return GradeUser::where('school_id', $schoolId)->whereIn('user_type', $types)->paginate();
    }


    /**
     * @param $schoolId
     * @param $where
     * @return mixed
     */
    public function getGradeTeacherBySchool($schoolId, $where)
    {
        $map = ['grade_users.school_id' => $schoolId];
        // 聘任状态
        if (isset($where['status'])) {
            $map['users.status'] = $where['status'];
        }
        // 聘任方式
        if (isset($where['mode'])) {
            $map['teacher_profiles.mode'] = $where['mode'];
        }
        // 职称
        if (isset($where['title'])) {
            $map['teacher_profiles.title'] = $where['title'];
        }
        // 学历
        if (isset($where['education'])) {
            $map['teacher_profiles.education'] = $where['education'];
        }

        $query = GradeUser::where($map)
            ->select('users.status', 'users.name', 'user_type', 'grade_users.school_id', 'grade_users.user_id')
            ->join('users', 'users.id', '=', 'grade_users.user_id')
            ->join('teacher_profiles', 'teacher_profiles.user_id', '=', 'grade_users.user_id')
            ->whereIn('user_type', Role::GetTeacherUserTypes());

        // 教师姓名, 手机号
        if (isset($where['keyword'])) {
            $keyword = $where['keyword'];
            $query->where(function ($sql) use ($keyword) {
                $sql->where('users.name', 'like', '%' . $keyword . '%')
                    ->orWhere('users.mobile', 'like', '%' . $keyword . '%');
            });
        }

        return $query->paginate();
    }

    /**
     * @param $schoolId
     * @param $where
     * @param int $type 1 查询 2 修稿
     * @param null $updateStatus
     * @return mixed
     */
    public function getByStudentsBySchool($schoolId, $where, $type = self::TYPE_SELECT, $updateStatus = null)
    {
        $map = ['grade_users.school_id' => $schoolId];
        // 年级
        if (isset($where['year'])) {
            $map['student_profiles.year'] = $where['year'];
        }
        // 专业
        if (isset($where['major_id'])) {
            $map['grade_users.major_id'] = $where['major_id'];
        }
        // 班级
        if (isset($where['grade_id'])) {
            $map['grade_users.grade_id'] = $where['grade_id'];
        }
        // 学生状态
        if (isset($where['status'])) {
            $map['users.status'] = $where['status'];
        }

        $query = GradeUser::where($map)
            ->select('users.status', 'users.name', 'user_type', 'grade_users.*', 'mobile')
            ->join('users', 'users.id', '=', 'grade_users.user_id')
            ->join('student_profiles', 'student_profiles.user_id', '=', 'grade_users.user_id')
            ->whereIn('user_type', array_merge(Role::GetStudentUserTypes(), [Role::REGISTERED_USER]));

        // 学生姓名, 手机号, 身份证号
        if (isset($where['keyword'])) {
            $keyword = $where['keyword'];
            $query->where(function ($sql) use ($keyword) {
                $sql->where('users.name', 'like', '%' . $keyword . '%')
                    ->orWhere('users.mobile', 'like', '%' . $keyword . '%')
                    ->orWhere('student_profiles.id_number', 'like', '%' . $keyword . '%');
            });
        }

        if ($type == self::TYPE_SELECT) {
            return $query->paginate();
        } else {
            return $query->update(['users.status' => $updateStatus]);
        }
    }


    /**
     * 根据学校获取 id
     * @param $gradeId
     * @return Collection
     */
    public function getByGradeForApp($gradeId)
    {
        return GradeUser::select(['id', 'user_id', 'name'])
            ->where('grade_id', $gradeId)
            ->where('user_type', Role::VERIFIED_USER_STUDENT)
            ->with('studentProfile')
            ->get();
    }

    /**
     * 根据学校 id 和 用户 id 来检查是否存在
     * @param $schoolId
     * @param $userId
     * @param $simple : 简单数据即可
     * @return GradeUser
     */
    public function isUserInSchool($userId, $schoolId, $simple = true)
    {
        $query = GradeUser::where('school_id', $schoolId)->where('user_id', $userId);
        if ($simple) {
            $query->select('name');
        }
        return $query->first();
    }

    /**
     * 根据给定的校园 id 值, 获取用户信息
     * @param $campusId
     * @param $type
     * @return Collection
     */
    public function paginateUserByCampus($campusId, $type)
    {
        return $this->_paginateUsersBy($type, 'campus_id', $campusId);
    }

    /**
     * 根据给定的学院 id 值, 获取用户信息
     * @param $id
     * @param $type
     * @return Collection
     */
    public function paginateUserByInstitute($id, $type)
    {
        return $this->_paginateUsersBy($type, 'institute_id', $id);
    }

    /**
     * 根据给定的系 id 值, 获取用户信息
     * @param $id
     * @param $type
     * @return Collection
     */
    public function paginateUserByDepartment($id, $type)
    {
        return $this->_paginateUsersBy($type, 'department_id', $id);
    }

    /**
     * 根据给定的班级 id 值, 获取用户信息
     * @param $id
     * @param $type
     * @return Collection
     */
    public function paginateUserByGrade($id, $type)
    {
        return $this->_paginateUsersBy($type, 'grade_id', $id);
    }

    /**
     * 根据给定的学生专业 id 值, 获取用户信息
     * @param $id
     * @param $type
     * @return Collection
     */
    public function paginateUserByMajor($id, $type)
    {
        return $this->_paginateUsersBy($type, 'major_id', $id);
    }

    /**
     * @param $type
     * @param $fieldName
     * @param $fieldValue
     * @param string $orderBy
     * @param string $direction
     * @return Collection
     */
    private function _paginateUsersBy($type, $fieldName, $fieldValue, $orderBy = 'name', $direction = 'asc')
    {
        if (is_array($type)) {
            return GradeUser::where($fieldName, $fieldValue)
                ->whereIn('user_type', $type)
                ->orderBy($orderBy, $direction)->paginate();
        }
        return GradeUser::where($fieldName, $fieldValue)
            ->where('user_type', $type)
            ->orderBy($orderBy, $direction)->paginate();
    }

    /**
     * 获取指定学校的第一位老师
     *
     * @param $schoolId
     * @return GradeUser
     */
    public function getAnyTeacher($schoolId)
    {
        return GradeUser::where('school_id', $schoolId)->where('user_type', Role::TEACHER)->first();
    }

    /**
     * 获取班级通讯录
     * @param $gradeId
     * @return GradeUser
     */
    public function getGradeAddressBook($gradeId)
    {
        return GradeUser::where('grade_id', $gradeId)->get();
    }

    /**
     * 根据学校ID 获取所有学生
     * @param $schoolId
     * @return GradeUser
     */
    public function getAllStudentBySchoolId($schoolId)
    {
        return GradeUser::where(['school_id' => $schoolId], ['user_type' => Role::VERIFIED_USER_STUDENT])->get();
    }

    /**
     * 插入多条用户班级关系
     * @param $data
     * @return bool
     */
    public function addGradUser($data)
    {
        return DB::table('grade_users')->insert($data);
    }

    /**
     * 根据用户ID获取用户信息
     * @param $userId
     * @return GradeUser
     */
    public function getUserInfoByUserId($userId)
    {
        return GradeUser::where('user_id', $userId)->first();
    }


    /**
     * 根据用户ID获取学校ID
     * @param $userId
     * @return mixed
     */
    public function getSchoolIdByUserId($userId)
    {
        return GradeUser::where('user_id', $userId)->select('school_id')->first();
    }


    /**
     * 获取指定学校的第一个学生
     * @param $schoolId
     * @return mixed
     */
    public function getStudentBySchoolId($schoolId)
    {
        $map = ['school_id' => $schoolId, 'user_type' => Role::VERIFIED_USER_STUDENT];
        return GradeUser::where($map)->with('user')->first();
    }


    /**
     * 获取学校管理员
     * @param $schoolId
     * @return mixed
     */
    public function getSchoolManagerBySchoolId($schoolId)
    {
        return GradeUser::where('school_id', $schoolId)
            ->whereIn('user_type', [Role::SCHOOL_MANAGER, Role::TEACHER])
            ->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);
    }


    /**
     * 根据用户ID修改信息
     * @param $userId
     * @param $data
     * @return mixed
     */
    public function updateDataByUserId($userId, $data)
    {
        return GradeUser::where('user_id', $userId)
            ->update($data);
    }

    /**
     * 获取退学休学的学生
     * @param $gradeId
     * @param $type
     * @return mixed
     */
    public function getStudentsByGradeId($gradeId, $type)
    {
        return GradeUser::where('grade_id', $gradeId)
            ->join('users', 'users.id', '=', 'grade_users.user_id')
            ->where('grade_users.user_type', Role::REGISTERED_USER)
            ->where('users.status', $type)
            ->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);
    }

}
