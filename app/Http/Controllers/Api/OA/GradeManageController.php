<?php

namespace App\Http\Controllers\Api\OA;

use App\Dao\Schools\GradeDao;
use App\Dao\Schools\GradeManagerDao;
use App\Dao\Schools\GradeResourceDao;
use App\Dao\Students\StudentProfileDao;
use App\Dao\Users\GradeUserDao;
use App\Dao\Users\UserDao;
use App\Http\Controllers\Controller;
use App\Http\Requests\MyStandardRequest;
use App\Models\Acl\Role;
use App\Models\Schools\GradeManager;
use App\Models\Schools\GradeResource;
use App\Utils\JsonBuilder;
use Exception;

class GradeManageController extends Controller
{

    /**
     * 获取班级
     * @param MyStandardRequest $request
     * @return string
     */
    public function index(MyStandardRequest $request)
    {
        $teacher = $request->user();
        $yearManger = $teacher->yearManger;

        $data = [];
        if ($yearManger) {
            // 年级主任
            $gradeDao = new GradeDao;
            $yearGrades = $gradeDao->gradeListByYear($teacher->getSchoolId(), $yearManger->year);
            foreach ($yearGrades as $key => $value) {
                 $data[$key]['grade_id'] = $value->id ?? '';
                 $data[$key]['name'] = $value->name ?? '';
                 $data[$key]['image'] = [];
                 foreach ($value->gradeResource as $k => $v) {
                    $data[$key]['image'][$k]['image_id'] = $v->id;
                    $data[$key]['image'][$k]['path'] = $v->path;
                 }
            }
        } else {
            // 班主任
            $dao = new GradeManagerDao;
            $grades = $dao->getAllGradesByAdviserId($teacher->id);
            foreach ($grades as $key => $val) {
                 $data[$key]['grade_id'] = $val->grade->id ?? '';
                 $data[$key]['name'] = $val->grade->name ?? '';

                 $data[$key]['image'] = [];
                 foreach ($val->grade->gradeResource as $k => $v) {
                    $data[$key]['image'][$k]['image_id'] = $v->id;
                    $data[$key]['image'][$k]['path'] = $v->path;
                 }
            }
        }

        return JsonBuilder::Success($data);
    }

    /**
     * 上传班级风采
     * @param MyStandardRequest $request
     * @return string
     */
    public function uploadGradeResource(MyStandardRequest $request)
    {
         $gradeId = $request->get('grade_id');
         $file = $request->file('file');
         $data['grade_id'] = $gradeId;
         $data['name'] = $file->getClientOriginalName();
         $data['type'] = $file->extension();
         $data['size'] = getFileSize($file->getSize());
         $data['path'] = GradeResource::gradeResourceUploadPathToUrl($file->store(GradeResource::DEFAULT_UPLOAD_PATH_PREFIX));

         $dao = new GradeResourceDao;
         $result = $dao->create($data);
         if($result) {
             return JsonBuilder::Success('上传成功');
         } else {
             return JsonBuilder::Error('上传失败');
         }
    }

    /**
     * 刪除班級风采
     * @param MyStandardRequest $request
     * @return string
     */
    public function delGradeResource(MyStandardRequest $request)
    {
        $id = $request->get('image_id');
        $dao  = new GradeResourceDao;
        $result = $dao->delete($id);
        if ($result) {
            return JsonBuilder::Success('删除成功');
        } else {
            return JsonBuilder::Error('删除失败');
        }
    }

    /**
     * 班级列表
     * @param MyStandardRequest $request
     * @return string
     */
    public function gradesList(MyStandardRequest $request)
    {
        $teacher = $request->user();
        $yearManger = $teacher->yearManger;
        if ($yearManger) {
            // 年级主任
            $gradeDao = new GradeDao;
            $yearGrades = $gradeDao->gradeListByYear($teacher->getSchoolId(), $yearManger->year);
            $grades = [];
            foreach ($yearGrades as $key => $value) {
                $grades[] = $value->GradeManager;
            }
        } else {
            // 班主任
            $dao = new GradeManagerDao;
            $grades = $dao->getAllGradesByAdviserId($teacher->id);
        }
        $data = [];
        foreach ($grades as $key => $val) {
            $data[$key]['grade_id'] = $val->grade->id ?? '';
            $data[$key]['name'] = $val->grade->name ?? '';
        }

        return JsonBuilder::Success($data);
    }

    /**
     * 学生列表
     * @param MyStandardRequest $request
     * @return string
     */
    public function studentList(MyStandardRequest $request)
    {

        $gradeId = $request->get('grade_id');
        $dao = new GradeUserDao;
        $data = $dao->paginateUserByGrade($gradeId, Role::VERIFIED_USER_STUDENT);
        $output = [];
        foreach ($data as $key => $val) {
            $output[$key]['student_id'] = $val->user_id;
            $output[$key]['name'] = $val->name;
        }
        return [
            'code' => JsonBuilder::CODE_SUCCESS,
            'message' => "ok",
            'data' => $output,
            'currentPage' => $data->currentPage(),
            'lastPage'    => $data->lastPage(),
            'total'       => $data->total(),
        ];
    }

    /**
     * 学生详情信息
     * @param MyStandardRequest $request
     * @return string
     */
    public function studentInfo(MyStandardRequest $request)
    {
        $studentId = $request->get('student_id');

        $dao = new  UserDao;
        $user = $dao->getUserById($studentId);
        $profile = $user->profile;
        $gradeUser = $user->gradeUser;
        $grade     = $user->gradeUser->grade;
        $monitor   = $user->monitor;
        $group     = $user->group;
        $data = [
            'grade_id'       => $grade->id,
            'student_id'     => $user->id,
            'name'           => $user->name,  // 姓名
            'id_number'      => $profile->id_number,
            'gender'         => $profile->gender, // 男女
            'birthday'       => $profile->birthday, // 出生年月日
            'nation_name'    => $profile->nation_name, // 民族
            'political_name' => $profile->political_name, // 政治面貌
            'source_place'   => $profile->source_place,  // 生源地
            'country'        => $profile->country, // 籍贯
            'contact_number' => $profile->contact_number, // 联系电话
            'qq'             => $profile->qq,
            'wx'             => $profile->wx,
            'parent_name'    => $profile->parent_name,  // 家长姓名
            'parent_mobile'  => $profile->parent_mobile, // 家长联系电话
            'state'          => $profile->state, //省市名称
            'city'           => $profile->city, // 市名称
            'area'           => $profile->area, //地区名称
            'address_line'   => $profile->address_line, // 详情地址
            'email'          => $user->email,
            'school_year'    => '4', // 学制
            'education'      => '', // 学历
            'institute'      => $gradeUser->institute->name,
            'major'          => $gradeUser->major->name,
            'year'           => $grade->year.'级',
            'monitor'        => $monitor == null ? false : true, // 班长
            'group'          => $group == null ? false : true,  // 团支书
        ];

        return JsonBuilder::Success($data);
    }

    /**
     * 教师修改学生信息
     * @param MyStandardRequest $request
     * @return string
     * @throws Exception
     */
    public function updateStudentInfo(MyStandardRequest $request)
    {
        $studentId = $request->get('student_id');
        $data = $request->get('data');
        $monitor = $request->get('monitor');
        $group = $request->get('group');

        $dao = new StudentProfileDao;
        $userDao = new UserDao;
        if (isset($data['email']) && !empty($data['email']) && !is_null($data['email'])) {
            $result = $userDao->getUserByEmail($data['email']);
            if ($result  && $result['id'] != $studentId) {
                return  JsonBuilder::Error('邮箱已经有人用了');
            }
        }
        $manger = [
            'monitor' => $monitor,
            'group' => $group
        ];
        $result = $dao->updateStudentInfoAndClassPositionByUserId($studentId, $data, $manger);
        if ($result->isSuccess()) {
            return JsonBuilder::Success('修改成功');
        } else {
            return JsonBuilder::Error('修改失败');
        }
    }


  /**
   * 是否为班主任
   * @param MyStandardRequest $request
   * @return string
   */
    public function isAdviser(MyStandardRequest $request)
    {
        $teacher = $request->user();
        if (is_null($teacher->isAdviser)) {
           $data = ['is_adviser' => GradeManager::ADVISER_0];
        } else {
           $data = ['is_adviser' => GradeManager::ADVISER_1];
        }

        return  JsonBuilder::Success($data);
    }

  /**
   * 是否为学校管理员
   * @param MyStandardRequest $request
   * @return string
   */
    public function isSchoolManager(MyStandardRequest $request)
    {
        $teacher = $request->user();
        $data = ['is_school_manger' => $teacher->isSchoolManager()];
        return  JsonBuilder::Success($data);
    }
}
