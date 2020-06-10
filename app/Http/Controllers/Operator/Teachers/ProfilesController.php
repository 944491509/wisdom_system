<?php

namespace App\Http\Controllers\Operator\Teachers;

use App\Dao\Performance\TeacherPerformanceDao;
use App\Dao\Schools\CampusDao;
use App\Dao\Schools\InstituteDao;
use App\Dao\Schools\OrganizationDao;
use App\Dao\Schools\SchoolDao;
use App\Dao\Teachers\QualificationDao;
use App\Dao\Teachers\TeacherProfileDao;
use App\Dao\Users\GradeUserDao;
use App\Dao\Users\UserDao;
use App\Dao\Users\UserOrganizationDao;
use App\Exports\TeacherExport;
use App\Http\Requests\MyStandardRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\NetworkDisk\MediaRequest;
use App\Models\Acl\Role;
use App\Models\NetworkDisk\Media;
use App\Models\Schools\Organization;
use App\Models\Teachers\Teacher;
use App\Models\Teachers\TeacherQualification;
use App\User;
use App\Utils\FlashMessageBuilder;
use App\Utils\JsonBuilder;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Ramsey\Uuid\Uuid;

class ProfilesController extends Controller
{
    /**
     * 添加教师
     * @param MyStandardRequest $request
     * @return string
     * @throws Exception
     */
    public function save(MyStandardRequest $request)
    {

        DB::beginTransaction();
        try {
            $teacherData = $request->get('teacher');
            $profileData = $request->get('profile');
            $schoolId    = $request->get('school_id');
            $campusId    = $request->get('campus_id');

            $userDao    = new UserDao();
            $profileDao = new TeacherProfileDao();
            $mobile     = $userDao->getUserByMobile($teacherData['mobile']);
            if ($mobile) {
                return JsonBuilder::Error('该手机号已注册过了');
            }

            $idNumber = $profileDao->getTeacherProfileByIdNumber($profileData['id_number']);
            if ($idNumber) {
                return JsonBuilder::Error('该身份证号已注册过了');
            }

            $teacherData['uuid']      = Uuid::uuid4()->toString();
            $teacherData['api_token'] = Uuid::uuid4()->toString();
            $teacherData['type']      = Role::TEACHER;
            $pwd                      = substr($profileData['id_number'], -6); // 身份证的后六位
            $teacherData['password']  = Hash::make($pwd);


            $user = $userDao->createUser($teacherData);


            $profileData['school_id'] = $schoolId;
            $profileData['user_id']   = $user->id;
            $profileData['uuid']      = Uuid::uuid4()->toString();
            $profileDao->createProfile($profileData);

            $gGao = new GradeUserDao();

            $gGao->create([
                'user_id'         => $user->id,
                'name'            => $user->name,
                'user_type'       => Role::TEACHER,
                'school_id'       => $schoolId,
                'campus_id'       => $campusId,
                'institute_id'    => 0,
                'department_id'   => 0,
                'grade_id'        => 0,
                'last_updated_by' => Auth::user()->id,
            ]);

            DB::commit();
            return JsonBuilder::Success('教师档案保存成功, 登陆用户名:' . $user->mobile . '登陆密码:' . $pwd . '(即身份证的后 6 位)');
        } catch (Exception $exception) {
            DB::rollBack();
            return JsonBuilder::Error('保存失败');
        }
    }

    /**
     * 添加教师页面
     * @param MyStandardRequest $request
     * @return Application|Factory|View
     */
    public function add_new(MyStandardRequest $request)
    {
        $this->dataForView['pageTitle'] = '教师档案管理';
        $schoolId                       = session('school.id');
        $this->dataForView['school_id'] = $schoolId;
        return view('teacher.profile.add_new', $this->dataForView);
    }

    public function edit(MyStandardRequest $request)
    {
        $schoolId = session('school.id');
        if ($request->isMethod('post')) {
            $data = $request->all();
            $userId = $data['uuid'];
            unset($data['uuid']);
            unset($data['_token']);
            $userOrgan = new UserOrganizationDao();
            $userDao = new UserDao();
            $user = $userDao->getTeacherById($userId);
            $data['name'] = $user->name;
            $data['title'] = Organization::getTitleByTitleId($data['title_id']);
            $data['school_id'] = $schoolId;
            $data['user_id'] = $userId;
            $re = $userOrgan->create($data);
            if($re->isSuccess()) {
                FlashMessageBuilder::Push($request, 'success','设置成功');
            } else {
                FlashMessageBuilder::Push($request, 'warning','设置失败');
            }
            return redirect()->route('school_manager.teachers.edit-profile',['uuid'=>$userId]);

        } else {
            $this->dataForView['pageTitle'] = '教师档案管理';

            $id = $request->uuid();
            $dao = new UserDao();
            /**
             * @var Teacher $teacher
             */
            $teacher = $dao->getTeacherByIdOrUuid($id);
            $this->dataForView['teacher'] = $teacher;
            $this->dataForView['userOrganization'] = Teacher::myUserOrganization($teacher->id);
            $this->dataForView['profile'] = $teacher->profile;
            // 行政方面的职务
            $this->dataForView['organizations'] = (new OrganizationDao())->getBySchoolId($schoolId);
            $this->dataForView['titles'] = Organization::AllTitles();

            // 教学方面的职务: 是否隶属于任何的教研组
            $this->dataForView['groups'] = Teacher::myTeachingAndResearchGroup($teacher->id);
            // 学生管理方面的职务: 是否班主任
            $this->dataForView['gradeManager'] = Teacher::myGradeManger($teacher->id);
            $this->dataForView['yearManager'] = Teacher::myYearManger($teacher->id);

            // 该教师历年的考核记录
            $schoolDao = new SchoolDao();
            $school = $schoolDao->getSchoolById($schoolId);
            $this->dataForView['school'] = $school;
            $this->dataForView['configs'] = $school->teacherPerformanceConfigs;
            $this->dataForView['history'] = $teacher->performances ?? [];


            // 该教师的评聘佐证材料
            $qualificationDao                   = new QualificationDao;
            $qualification                      = $qualificationDao->getTeacherQualificationByTeacherId($teacher->id);
            $this->dataForView['qualification'] = $qualification;

            return view('teacher.profile.edit', $this->dataForView);
        }
    }

    /**
     * 教师信息详情
     * @param MyStandardRequest $request
     * @return string
     */
    public function teacherProfileInfo(MyStandardRequest $request)
    {
        $teacherId = $request->get('teacher_id');
        $data      = [];

        $profileDao = new TeacherProfileDao;
        $profile    = $profileDao->getTeacherProfileByTeacherIdOrUuid((int)$teacherId);
        $teacher    = $profile->user;
        $data       = [
            'campus_id' => $teacher->gradeUser[0]->campus_id,
            'school_id' => $profile->school_id,
            'teacher'   => [
                'name'   => $teacher->name,
                'mobile' => $teacher->mobile,
                'status' => $teacher->status,
            ],
            'profile'   => [
                'gender'                  => $profile->gender,
                'nation_name'             => $profile->nation_name,
                'birthday'                => $profile->birthday,
                'serial_number'           => $profile->serial_number,
                'id_number'               => $profile->id_number,
                'resident'                => $profile->resident,
                'political_name'          => $profile->political_name,
                'party_time'              => $profile->party_time,
                'home_address'            => $profile->home_address,
                'education'               => $profile->education,
                'degree'                  => $profile->degree,
                'major'                   => $profile->major,
                'graduation_school'       => $profile->graduation_school,
                'graduation_time'         => $profile->graduation_time,
                'final_education'         => $profile->final_education,
                'final_degree'            => $profile->final_degree,
                'final_major'             => $profile->final_major,
                'final_graduation_school' => $profile->final_graduation_school,
                'final_graduation_time'   => $profile->final_graduation_time,
                'title'                   => $profile->title,
                'title_start_at'          => $profile->title_start_at,
                'work_start_at'           => $profile->work_start_at,
                'hired_at'                => $profile->hired_at,
                'mode'                    => $profile->mode,
                'category_teach'          => $profile->category_teach,
                'notes'                   => $profile->notes,
            ]
        ];
        return JsonBuilder::Success($data);
    }

    /**
     * 修改教师信息
     * @param MyStandardRequest $request
     * @return string
     * @throws Exception
     */
    public function editTeacherInfo(MyStandardRequest $request)
    {
        $teacherData = $request->get('teacher');
        $profileData = $request->get('profile');
        $schoolId    = $request->get('school_id');
        $campusId    = $request->get('campus_id');
        $teacherId   = $request->get('teacher_id');

        $userDao    = new UserDao();
        $profileDao = new TeacherProfileDao();
        $mobile     = $userDao->getUserByMobile($teacherData['mobile']);
        if (!empty($mobile) && $mobile->id != $teacherId) {
            return JsonBuilder::Error('该手机号已注册过了');
        }

        $idNumber = $profileDao->getTeacherProfileByIdNumber($profileData['id_number']);
        if (!empty($idNumber) && $idNumber->user_id != $teacherId) {
            return JsonBuilder::Error('该身份证号已注册过了');
        }

        DB::beginTransaction();
        try {
            $userDao    = new UserDao;
            $profileDao = new TeacherProfileDao;
            $gradeDao   = new  GradeUserDao;

            $userDao->updateUserInfo($teacherId, $teacherData);
            $profileDao->updateTeacherProfile($teacherId, $profileData);
            $gradeDao->updateDataByUserId($teacherId, ['school_id' => $schoolId, 'campus_id' => $campusId]);
            DB::commit();
            return JsonBuilder::Success('教师档案修改成功');
        } catch (Exception $exception) {
            DB::rollBack();
            return JsonBuilder::Error('修改识别:' . $exception->getMessage());
        }

    }


    /**
     * 教师年终考评
     * @param MyStandardRequest $request
     * @return Factory|View
     */
    public function manage_performance(MyStandardRequest $request)
    {
        $schoolDao                    = new SchoolDao();
        $school                       = $schoolDao->getSchoolById(session('school.id'));
        $this->dataForView['configs'] = $school->teacherPerformanceConfigs;

        $dao                          = new UserDao();
        $teacher                      = $dao->getTeacherByUuid($request->uuid());
        $this->dataForView['teacher'] = $teacher;

        return view('teacher.profile.manage_performance', $this->dataForView);
    }

    /**
     * 保存
     * @param MyStandardRequest $request
     * @return RedirectResponse
     */
    public function manage_performance_save(MyStandardRequest $request)
    {
        $data   = $request->all();
        $userId = $data['performance']['user_id'];
        $year   = $data['performance']['year'];

        $dao         = new TeacherPerformanceDao($request->session()->get('school.id'));
        $performance = $dao->getPerformanceByUserIdAndYear($userId, $year);

        if ($performance) {
            FlashMessageBuilder::Push($request, 'error', '当前用户' . $year . '年, 已经考评过了');
        } else {
            $result = $dao->create($data['performance'], $data['items'], $request->user());
            if ($result->isSuccess()) {
                FlashMessageBuilder::Push($request, 'success', '年终评估已经保存');
            } else {
                FlashMessageBuilder::Push($request, 'error', $result->getMessage());
            }
        }

        return redirect()->route('school_manager.teachers.edit-profile', ['uuid' => $data['performance']['user_id']]);
    }

    /**
     * 教职工的档案照片管理
     * @param MediaRequest $request
     * @return Factory|View
     */
    public function avatar(MediaRequest $request)
    {
        if ($request->method() === 'GET') {
            $this->dataForView['pageTitle'] = '教职工档案照片';
            $this->dataForView['user'] = (new UserDao())->getTeacherByIdOrUuid($request->uuid());
            return view('teacher.profile.update_avatar', $this->dataForView);
        }
        elseif ($request->method() === 'POST'){
            $user = (new UserDao())->getTeacherByIdOrUuid($request->get('user')['id']);
            $file = $request->getFile();
            $path = Media::DEFAULT_UPLOAD_PATH_PREFIX.$user->id;
            $url = $file->storeAs($path, Str::random(10).'.'.$file->getClientOriginalExtension()); // 上传并返回路径
            $profile = $user->profile;
            $profile->avatar = str_replace('public/','storage/',$url);
            $profile->save();
            FlashMessageBuilder::Push($request, 'success','照片已更新');
            return redirect()->route('school_manager.teachers.edit-avatar',['uuid'=>$user->id]);
        }
    }

    public function export()
    {
        return Excel::download(new TeacherExport, 'teachers.xlsx');
    }

    /**
     * 佐证材料列表
     * @param MyStandardRequest $request
     * @return Factory|View
     */
    public function listQualification(MyStandardRequest $request)
    {

        $id = $request->uuid();
        $dao = new UserDao();

        $teacher = $dao->getTeacherByIdOrUuid($id);

        $qualificationDao =  new QualificationDao;
        $qualification = $qualificationDao->getTeacherQualificationByTeacherId($teacher->id);

        $this->dataForView['uuid'] = $id;
        $this->dataForView['data'] = $qualification;
        return view('teacher.profile.list_qualification', $this->dataForView);
    }


    /**
     * 评聘添加页面
     * @param MyStandardRequest $request
     * @return Factory|View
     *
     */
    public function addQualification(MyStandardRequest $request)
    {
        $this->dataForView['uuid'] = $request->uuid();
        return view('teacher.profile.add_qualification', $this->dataForView);
    }

    /**
     * 保存评聘资料
     * @param MyStandardRequest $request
     * @return RedirectResponse
     */
    public function saveQualification(MyStandardRequest $request)
    {

        $uuid = $request->uuid();
        $data = $request->get('qualification');
        $userDao = new UserDao;
        $user = $userDao->getUserByIdOrUuid($uuid);

        $path = TeacherQualification::DEFAULT_UPLOAD_PATH_PREFIX. $user->id .'/qualification';
        $file = $request->file('file')->store($path);

        $data['path'] = TeacherQualification::qualificationUploadPathToUrl($file);
        $data['user_id'] = $user->id;
        $dao = new  QualificationDao;
        $result = $dao->create($data);

        if($result->isSuccess()) {
            FlashMessageBuilder::Push($request, 'success','添加资料成功');
        } else {
            FlashMessageBuilder::Push($request, 'error',$result->getMessage());
        }

        return redirect()->route('school_manager.teachers.edit-profile',['uuid'=> $uuid]);
    }

    /**
     * 删除评聘
     * @param MyStandardRequest $request
     * @return RedirectResponse
     */
    public function delQualification(MyStandardRequest $request)
    {
        $id = $request->get('id');
        $dao = new QualificationDao;

        $result = $dao->del($id);
        if ($result) {
            FlashMessageBuilder::Push($request, FlashMessageBuilder::SUCCESS, '删除成功');
        } else {
            FlashMessageBuilder::Push($request, FlashMessageBuilder::DANGER, '删除失败');
        }
        $this->dataForView['uuid'] = $request->uuid();
        return redirect()->route('school_manager.teachers.list.qualification', $this->dataForView);
    }

}
