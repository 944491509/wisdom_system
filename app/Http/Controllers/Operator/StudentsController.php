<?php

namespace App\Http\Controllers\Operator;

use App\Dao\Schools\MajorDao;
use App\Dao\Students\StudentAdditionInformationDao;
use App\Dao\Students\StudentProfileDao;
use App\Dao\Users\GradeUserDao;
use App\Dao\Users\UserDao;
use App\Http\Controllers\Controller;
use App\Http\Requests\MyStandardRequest;
use App\Http\Requests\User\StudentRequest;
use App\Models\Acl\Role;
use App\User;
use App\Utils\JsonBuilder;
use App\Utils\Time\GradeAndYearUtil;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Ramsey\Uuid\Uuid;

class StudentsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 添加学生页面
     * @param MyStandardRequest $request
     * @return Application|Factory|View
     */
    public function add()
    {
        $this->dataForView['pageTitle'] = '学生档案管理';
        $this->dataForView['school_id'] = session('school.id');
        return view('teacher.profile.add_new_student', $this->dataForView);
    }

    /**
     * 添加学生
     * @param StudentRequest $request
     * @return string
     */
    public function create(StudentRequest $request)
    {

        $userData    = $request->get('user');
        $profileData = $request->get('profile');
        $addition    = $request->get('addition');
        $majorId     = $request->get('major_id');
        $gradeId     = $request->get('grade_id');
        $status      = $request->get('status');

        DB::beginTransaction();

        try {
            // 创建用户数据
            // 创建用户班级的关联
            // 创建用户的档案
            // 附加信息
            $userDao    = new UserDao();
            $profileDao = new StudentProfileDao();
            $mobile     = $userDao->getUserByMobile($userData['mobile']);
            if (!empty($mobile)) {
                return JsonBuilder::Error('该手机号已注册过了');
            }

            $idNumber = $profileDao->getStudentInfoByIdNumber($profileData['id_number']);
            if (!empty($idNumber)) {
                return JsonBuilder::Error('该身份证号已注册过了');
            }

            if ($status == User::STATUS_WAITING_FOR_MOBILE_TO_BE_VERIFIED) {
                $user = $userDao->importUser($userData['mobile'], $userData['name'],
                    substr($profileData['id_number'], -6),
                    Role::REGISTERED_USER,
                    User::STATUS_WAITING_FOR_MOBILE_TO_BE_VERIFIED
                );
            } else {
                $user = $userDao->importUser($userData['mobile'], $userData['name'], substr($profileData['id_number'], -6));
            }
            $gradeUserDao = new GradeUserDao();

            $major = (new MajorDao())->getMajorById($majorId);

            if ($status == User::STATUS_WAITING_FOR_MOBILE_TO_BE_VERIFIED) {
                $gradeUserDao->create([
                    'school_id'       => 1,
                    'user_id'         => $user->id,
                    'name'            => $user->name,
                    'user_type'       => $user->type,
                    'last_updated_by' => $request->user()->id
                ]);
            } else {
                $gradeUserDao->create([
                    'user_id'         => $user->id,
                    'name'            => $user->name,
                    'user_type'       => $user->type,
                    'school_id'       => 1,
                    'campus_id'       => $major->campus_id ?? 0,
                    'institute_id'    => $major->institute_id ?? 0,
                    'department_id'   => $major->department_id ?? 0,
                    'major_id'        => $major->id ?? 0,
                    'grade_id'        => $gradeId ?? 0,
                    'last_updated_by' => $request->user()->id
                ]);
            }

            $studentProfileDao       = new StudentProfileDao();
            $profileData['user_id']  = $user->id;
            $profileData['uuid']     = Uuid::uuid4()->toString();
            $profileData['year']     = date('Y');
            $profileData['serial_number']  = 0;
            if ($status == User::STATUS_WAITING_FOR_MOBILE_TO_BE_VERIFIED) { // 未认证用户
                $profileData['educational_system']  = 0;
                $profileData['entrance_type']  = 0;
                $profileData['student_type']  = 0;
                $profileData['segmented_type']  = 0;
            }
            $profileData['birthday'] = GradeAndYearUtil::IdNumberToBirthday($profileData['id_number'])->getData();
            $studentProfileDao->create($profileData);

            $additionDao = new StudentAdditionInformationDao;

            $addition['user_id'] = $user->id;
            $additionDao->create($addition);

            DB::commit();
            return JsonBuilder::Success('档案创建成功, 登陆密码为学生身份证的后六位: ' . substr($profileData['id_number'], -6));
        } catch (Exception $exception) {
            DB::rollBack();
            return JsonBuilder::Error('添加失败, 异常信息:' . $exception->getMessage());
        }
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
        $majorId  = $request->get('major_id');
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
            $gradeDao->updateDataByUserId($studentId, ['grade_id' => $gradeId, 'campus_id' => $majorId]);
            DB::commit();
            return JsonBuilder::Success('学生档案修改成功');
        } catch (Exception $exception) {
            DB::rollBack();
            return JsonBuilder::Error('修改失败'. $exception->getMessage());
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
            'status'   => $user->status,
        ];
        return JsonBuilder::Success($data);
    }



    /**
     * 已注册用户
     * @param StudentRequest $request
     * @return Factory|View
     */
    public function school_users()
    {
        $this->dataForView['pageTitle'] = '已注册用户管理';
        return view('teacher.users.users', $this->dataForView);

    }
}
