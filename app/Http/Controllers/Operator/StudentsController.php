<?php

namespace App\Http\Controllers\Operator;

use App\Dao\Schools\GradeDao;
use App\Dao\Schools\MajorDao;
use App\Dao\Students\StudentProfileDao;
use App\Dao\Users\GradeUserDao;
use App\Dao\Users\UserDao;
use App\Http\Controllers\Controller;
use App\Http\Requests\MyStandardRequest;
use App\Http\Requests\User\StudentRequest;
use App\Models\Acl\Role;
use App\User;
use App\Utils\FlashMessageBuilder;
use App\Utils\JsonBuilder;
use App\Utils\Time\GradeAndYearUtil;
use Exception;
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

    public function add(MyStandardRequest $request){
        $this->dataForView['pageTitle'] = '学生档案管理';
        $schoolId                       = session('school.id');
        $this->dataForView['school_id'] = $schoolId;

        // 列出学校所有专业
        $this->dataForView['majors'] = (new MajorDao())->getMajorsBySchool($request->getSchoolId());
        $this->dataForView['grades'] = (new GradeDao())->getAllBySchool($request->getSchoolId());

        return view('teacher.profile.add_new_student', $this->dataForView);
    }

    public function update(StudentRequest $request)
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
                    'school_id'       => $request->getSchoolId(),
                    'campus_id'       => $major->campus_id,
                    'institute_id'    => $major->institute_id,
                    'department_id'   => $major->department_id,
                    'major_id'        => $major->id,
                    'grade_id'        => $gradeId,
                    'last_updated_by' => $request->user()->id
                ]);
            }


            $studentProfileDao       = new StudentProfileDao();
            $profileData['user_id']  = $user->id;
            $profileData['uuid']     = Uuid::uuid4()->toString();
            $profileData['birthday'] = GradeAndYearUtil::IdNumberToBirthday($profileData['id_number'])->getData();
            $studentProfileDao->create($profileData);
            DB::commit();
            return JsonBuilder::Success('档案创建成功, 登陆密码为学生身份证的后六位: ' . substr($profileData['id_number'], -6));
        } catch (Exception $exception) {
            DB::rollBack();
            return JsonBuilder::Error('添加失败');
        }
    }


    /**
     * 已注册用户
     * @param StudentRequest $request
     * @return Factory|View
     */
    public function school_users(StudentRequest $request)
    {
        $this->dataForView['pageTitle'] = '已注册用户管理';
        return view('teacher.users.users', $this->dataForView);

    }
}
