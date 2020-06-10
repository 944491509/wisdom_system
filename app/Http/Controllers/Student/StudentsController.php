<?php
namespace App\Http\Controllers\Student;

use App\Dao\Users\GradeUserDao;
use App\Dao\Users\UserDao;
use App\Models\Acl\Role;
use App\Models\Schools\GradeManager;
use App\Models\Students\StudentAdditionInformation;
use App\Models\Users\GradeUser;
use App\User;
use App\Utils\FlashMessageBuilder;
use App\Http\Controllers\Controller;
use App\Dao\Students\StudentProfileDao;
use App\Http\Requests\User\StudentRequest;
use App\Utils\JsonBuilder;
use Exception;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentsController extends Controller
{
    /**
     * 编辑学生页面
     * @param StudentRequest $request
     * @return Factory|View
     */
    public function edit(StudentRequest $request)
    {
        $dao                               = new UserDao();
        $student                           = $dao->getUserByUuid($request->uuid());
        $this->dataForView['gradeManager'] = GradeManager::where('monitor_id', $student->id)->first();
        $this->dataForView['student']      = $student;
        $grade                             = $student->gradeUser();
        $this->dataForView['is_show']      = $grade ? 1 : 0;
        $this->dataForView['pageTitle']    = '档案管理';
        $this->dataForView['addition']     = StudentAdditionInformation::where('user_id', $student['id'])->first();
        $this->dataForView['gradeUser']    = GradeUser::where('user_id', $student['id'])->first();
        return view('student.edit', $this->dataForView);
    }

    /**
     * 修改学生信息
     * @param StudentRequest $request
     * @return string
     * @throws Exception
     */
    public function update(StudentRequest $request)
    {
        $data      = $request->getFormData();
        $studentId = $request->get('student_id');
        $campusId  = $request->get('campus_id');
        $gradeId   = $request->get('grade_id');

        $userDao    = new UserDao;
        $profileDao = new StudentProfileDao;
        $gradeDao   = new GradeUserDao;
        $mobile     = $userDao->getUserByMobile($data['user']['mobile']);
        if (!empty($mobile) && $mobile->id != $studentId) {
            return JsonBuilder::Error('该手机号已注册过了');
        }

        $idNumber = $profileDao->getStudentInfoByIdNumber($data['profile']['id_number']);
        if (!empty($idNumber) && $idNumber->user_id != $studentId) {
            return JsonBuilder::Error('该身份证号已注册过了');
        }

        DB::beginTransaction();
        try {
            $userDao->updateUserInfo($studentId, $data['user']);
            $profileDao->updateStudentProfile($studentId, $data['profile']);
            $gradeDao->updateDataByUserId($studentId, ['grade_id' => $gradeId, 'campus_id' => $campusId]);
            DB::commit();
            return JsonBuilder::Success('学生档案修改成功');
        } catch (Exception $exception) {
            DB::rollBack();
            return JsonBuilder::Error('修改成功');
        }

    }

    /**
     * 学生信息
     * @param StudentRequest $request
     * @return string
     */
    public function info(StudentRequest $request)
    {
        $studentId = $request->get('student_id');

        $userDao    = new UserDao;
        $profileDao = new StudentProfileDao;
        $data       = [];
        $user       = $userDao->getUserById($studentId);
        $profile    = $profileDao->getStudentInfoByUserId($studentId);
        $data[]     = [
            'user'     => [
                'name'   => $user->name,
                'mobile' => $user->mobile,
                'email'  => $user->email,
            ],
            'profile'  => $profile,
            'addition' => $user->profile->additionInformation,
            'major_id' => $user->gradeUser->major_id,
            'grade_id' => $user->gradeUser->grade_id,
            'status'   => $user->status
        ];
        return JsonBuilder::Success($data);
    }


    /**
     * 通讯录页面
     * @param StudentRequest $request
     * @return Factory|View
     */
    public function contacts_list(StudentRequest $request){
        $this->dataForView['pageTitle'] = '通讯录';
        $this->dataForView['schoolId']  = $request->getSchoolId();
        return view('student.contacts.list',$this->dataForView);
    }
}
