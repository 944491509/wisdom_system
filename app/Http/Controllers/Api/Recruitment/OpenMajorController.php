<?php

namespace App\Http\Controllers\Api\Recruitment;

use App\Events\SystemNotification\ApproveOpenMajorEvent;
use App\Events\User\Student\ApplyRecruitmentPlanEvent;
use App\Events\User\Student\ApproveRegistrationEvent;
use App\Events\User\Student\EnrolRegistrationEvent;
use App\Events\User\Student\RefuseRegistrationEvent;
use App\Events\User\Student\RejectRegistrationEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\RecruitStudent\PlanRecruitRequest;
use App\Dao\RecruitmentPlan\RecruitmentPlanDao;
use App\Dao\Users\UserDao;
use App\Dao\Schools\GradeDao;
use App\Dao\RecruitStudent\RegistrationInformaticsDao;
use App\Dao\Students\StudentProfileDao;
use App\Models\RecruitStudent\RegistrationInformatics;
use App\Models\Users\GradeUser;
use App\Utils\JsonBuilder;
use App\Utils\Time\GradeAndYearUtil;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class OpenMajorController extends Controller
{
    /**
     * 招生专业
     * @param PlanRecruitRequest $request
     * @return string
     */
    public function major(PlanRecruitRequest $request)
    {
        $schoolId = $request->get('school_id');
        $mobile = $request->get('mobile');
        $idNumber = $request->get('id_number');

        $dao  = new RecruitmentPlanDao($schoolId);
        $userDao  = new UserDao;
        $informaticsDao = new RegistrationInformaticsDao;
        $studentProfileDao = new StudentProfileDao;

        $data = $dao->getAllMajorBySchoolId($schoolId);

        $info = [];
        if (isset($mobile)) {
            $user = $userDao->getUserByMobile($mobile);
            if (!empty($user->id)) {
                $info = $informaticsDao->getInformaticsByUserId($user->id)->toArray();
            }
        }
        if (isset($idNumber)) {
            $user = $studentProfileDao->getStudentInfoByIdNumber($idNumber);
            if (!empty($user->user_id)) {
                $info = $informaticsDao->getInformaticsByUserId($user->user_id)->toArray();
            }
        }

        if (empty($info)) {
            foreach ($data as $key => $val) {
                $data[$key]['status'] = 0;
                unset($data[$key]['major_id']);
            }
        } else {
            $majorIdArr = array_column($info, 'major_id','major_id');
            foreach ($data  as $key => $val ) {
                if(in_array($val['major_id'],$majorIdArr)) {
                    $data[$key]['status'] = 1;
                } else {
                    $data[$key]['status'] = 0;
                }
                unset($data[$key]['major_id']);
            }
        }

        return JsonBuilder::Success(['majors'=> $data ?? []]);
    }

    /**
     * 学生尝试加载自己已经报名过的招生信息
     * @param PlanRecruitRequest $request
     * @return string
     */
    public function studentProfile(PlanRecruitRequest $request)
    {
        $mobile = $request->getMobile();            // 手机号
        $idNumber = $request->getStudentIdNumber(); // 身份证号

        $userId = null;
        $userProfile = null;

        // 优先通过提交的身份证进行查询
        if ($idNumber) {
            $studentProfileDao = new StudentProfileDao;
            $userProfile = $studentProfileDao->getStudentInfoByIdNumber($idNumber);
            $userId = $userProfile->user_id ?? null;
        }

        // 如果这个时候没有取到用户
        if (!$userId && $mobile) {
            $userDao  = new UserDao;
            $user = $userDao->getUserByMobile($mobile);
            $userId = $user->id ?? null;
        }
        $result = [];

        if ($userId) {
            $regDao = new RegistrationInformaticsDao();
            $result = $regDao->getInformaticsByUserId($userId, $simple = true); // 获取简单的数据即可
        }

        return JsonBuilder::Success(['applied'=>$result]);
    }

    /**
     * 学生报名
     * @param PlanRecruitRequest $request
     * @return string
     * @throws \Exception
     */
    public function signUp(PlanRecruitRequest $request)
    {
        $formData = $request->getSignUpFormData();
        //验证提交的数据
        $rules = [
            'name'=>'required|between:2,10', // 姓名
            'id_number'=>'required', // 身份证号
            'gender'=>'required|between:1,2', // 性别
            'nation_name'=>'required|between:2,10', // 民族
            'political_name'=>'required', // 政治面貌
            'source_place'=>'required', // 生源地
            'country'=>'required', // 籍贯
            'mobile'=>'required|regex:/^1[34578]\d{9}$/', // 联系电话
            'qq'=>'required|numeric', // qq
            'wx'=>'required', // 微信号
            'email'=>'required|regex:/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/', // 邮箱
            'parent_name'=>'required|between:2,10', // 家长姓名
            'parent_mobile'=>'required|regex:/^1[34578]\d{9}$/', // 家长电话
            'province'=>'required', // 省份
            'city'=>'required', // 城市
            'district'=>'required', // 地区
            'address'=>'required', // 联系地址
            'postcode'=>'required', // 邮编
            'examination_score'=>'required', // 高考分数
        ];
        $message = [
            'name.required'=>'请填写姓名',
            'name.between'=>'姓名在2-10位之间',
            'id_number.required'=>'请填写身份证号',
            'gender.required'=>'请选择性别',
            'gender.between'=>'性别值错误',
            'nation_name.required'=>'请填写民族',
            'nation_name.between'=>'民族在2-10位之间',
            'political_name.required'=>'请选择政治面貌',
            'source_place.required'=>'请填写生源地',
            'country.required'=>'请填写籍贯',
            'mobile.required'=>'联系电话不能为空',
            'mobile.regex'=>'联系电话格式不正确',
            'qq.required'=>'QQ号不能为空',
            'qq.numeric'=>'QQ号必须为数字',
            'wx.required'=>'微信号不能为空',
            'email.required'=>'邮箱不能为空',
            'email.regex'=>'邮箱格式错误',
            'parent_name.required'=>'家长姓名不能为空',
            'parent_name.between'=>'家长姓名在2-10位之间',
            'parent_mobile.required'=>'家长电话不能为空',
            'parent_mobile.regex'=>'家长电话格式不正确',
            'province.required'=>'请选择省份',
            'city.required'=>'请选择市',
            'district.required'=>'请选择地区',
            'address.required'=>'请填写联系地址',
            'postcode.required'=>'请填写邮编',
            'examination_score.required'=>'请填写高考分数',
        ];
        $validator = Validator::make($formData, $rules, $message);
        if ($validator->fails()) {
            return JsonBuilder::Error($validator->errors()->first());
        }

        $formData['origin'] =3; // 数据来源

        // 验证手机号是否存在
        $userObj = new UserDao();
        $userInfo1 = $userObj->getUserByMobile($formData['mobile']);
        $userInfo2 = $userObj->getUserByEmail($formData['email']);
        // 获取专业信息
        $plan = $request->getPlan();
        // 外部提交
        if (isset($formData['is_reg']) && $formData['is_reg'] == 1) {
            // 存在
            if($userInfo1){
                // 验证邮箱是否被使用
                if (!empty($userInfo2) && $userInfo2->id != $userInfo1->id) {
                    return JsonBuilder::Error('邮箱已存在,请更换其他邮箱1');
                }
                // 获取我是否可以报名
                $regDao = new RegistrationInformaticsDao();
                $statusMessageArr = $regDao->getRegistrationInformaticsStatusInfo($userInfo1->id, $plan);
                if ($statusMessageArr['status'] != 100) {
                    return JsonBuilder::Error($statusMessageArr['message']);
                }
                // 修改学生档案基础信息
                $dao = new RegistrationInformaticsDao;
                $returnData = $dao->eidtUser($userInfo1, $formData, $plan);
                if ($returnData['status'] == true) {
                    $user = $returnData['data']['user'];
                } else {
                    return JsonBuilder::Error($returnData['message']);
                }
            } else {
                // 验证邮箱是否被使用
                if (!empty($userInfo1)) {
                    return JsonBuilder::Error('手机号已存在,请更换其他手机号');
                }
                // 验证邮箱是否被使用
                if (!empty($userInfo2)) {
                    return JsonBuilder::Error('邮箱已存在,请更换其他邮箱');
                }
                // 注册账号信息
                $dao = new RegistrationInformaticsDao;
                $msgBag = $dao->addUser($formData, $plan);
                if ($msgBag->isSuccess()) {
                    $user = $msgBag->getData()['user'];
                    // 添加grade_users 表信息
                    $addData['user_id'] = $user->id; // 学生id
                    $addData['name'] = $user->name; // 姓名
                    $addData['user_type'] = 5; // 普通用户
                    $addData['grade_id'] = 0; // 班级id
                    $addData['major_id'] = 0; // 专业id
                    $addData['department_id'] = 0; // 系
                    $addData['institute_id'] = 0; // 学院
                    $addData['campus_id'] = 0; // 校区ID
                    $addData['school_id'] = $plan->school_id; // 学校id
                    $addData['last_updated_by'] = $user->id; // 最后更新的用户id
                    $addData['created_at'] = Carbon::now()->format('Y-m-d H:i:s'); // 添加时间
                    GradeUser::insert($addData);
                    // 获取我是否可以报名
                    $regDao = new RegistrationInformaticsDao();
                    $statusMessageArr = $regDao->getRegistrationInformaticsStatusInfo($user->id, $plan);
                    if ($statusMessageArr['status'] != 100) {
                        return JsonBuilder::Error($statusMessageArr['message']);
                    }
                } else {
                    return JsonBuilder::Error($msgBag->getMessage());
                }
            }
        } else {
            $user = $request->user('api');
            // 验证邮件是否被注册
            if (!empty($userInfo1) && ($user->email != $userInfo1->email)) {
                return JsonBuilder::Error('手机号已存在,请更换其他手机号');
            }
            // 验证邮件是否被注册
            if (!empty($userInfo2) && ($user->email != $userInfo2->email)) {
                return JsonBuilder::Error('邮箱已存在,请更换其他邮箱');
            }
            // 获取我是否可以报名
            $regDao = new RegistrationInformaticsDao();
            $statusMessageArr = $regDao->getRegistrationInformaticsStatusInfo($user->id, $plan);
            if ($statusMessageArr['status'] != 100) {
                return JsonBuilder::Error($statusMessageArr['message']);
            }
            // 修改学生档案基础信息
            $dao = new RegistrationInformaticsDao;
            $returnData = $dao->eidtUser($user, $formData, $plan);
            if ($returnData['status'] == true) {
                $user = $returnData['data']['user'];
            } else {
                return JsonBuilder::Error($returnData['message']);
            }
        }
        // 保存报名计划
        $result = $user ? $dao->signUp($formData, $user) : false;
        if ($result && $result->isSuccess()) {
              // 通知老师, 有个新报名的学生
            event(new ApplyRecruitmentPlanEvent($result->getData()));
            return JsonBuilder::Success('报名成功');
        } else {
            return JsonBuilder::Error('报名失败');
        }
    }

    /**
     * 批准或者拒绝某个报名表格
     *
     * @param PlanRecruitRequest $request
     * @return string
     */
    public function approve_or_reject(PlanRecruitRequest $request){
        $form = $request->getApprovalForm();
        $userUuid = $request->uuid();
        $userDao = new UserDao();

        $manager = $userDao->getUserByUuid($userUuid);
        if($manager && ($manager->isSchoolAdminOrAbove() || $manager->isTeacher())){
            // 操作者至少应该是学校的员工
            $dao = new RegistrationInformaticsDao();

            if($request->isApprovedAction()) {
                // 验证当前手机号是否被注册
                $registrationInformaticsInfo = RegistrationInformatics::find($form['currentId']);

                $getUserByMobile = $userDao->getUserByMobile($registrationInformaticsInfo->mobile);
                if(!empty($getUserByMobile) && $getUserByMobile->id != $registrationInformaticsInfo->user_id){
                    return JsonBuilder::Error('学生电话已被占用');
                }

                // 更新操作
                $bag = $dao->approve($form['currentId'],$manager,$form['note']??null);

                // 更新手机号为通过的手机号
                $getUserById = $userDao->getUserById($registrationInformaticsInfo->user_id);
                if(!empty($getUserById) && $getUserById->id){
                    $userDao->updateUserInfo($getUserById->id, [
                        'type'=> 5, // 已注册的手机号
                        'mobile'=>$registrationInformaticsInfo->mobile
                    ]);
                }

                event(new ApproveOpenMajorEvent($bag->getData()));
                event(new ApproveRegistrationEvent($bag->getData()));
            }else{
                $bag = $dao->refuse($form['currentId'],$manager,$form['note']??null);
                event(new ApproveOpenMajorEvent($bag->getData()));
                event(new RefuseRegistrationEvent($bag->getData()));
            }
            if($bag->isSuccess()){
                return JsonBuilder::Success($bag->getMessage());
            }
            else{
                return JsonBuilder::Error($bag->getMessage());
            }
        }
        return JsonBuilder::Error('无权执行此操作');
    }

    /**
     * 录取学生
     *
     * @param PlanRecruitRequest $request
     * @return string
     */
    public function enrol_or_reject(PlanRecruitRequest $request){
        $form = $request->getApprovalForm();
        $userUuid = $request->uuid();
        $userDao = new UserDao();
        $manager = $userDao->getUserByUuid($userUuid);
        if($manager && ($manager->isSchoolAdminOrAbove() || $manager->isTeacher())){
            // 操作者至少应该是学校的员工
            $dao = new RegistrationInformaticsDao();
            if($request->isEnrolAction()){
                $bag = $dao->enrol($form['currentId'],$manager,$form['note']??null);
                event(new EnrolRegistrationEvent($bag->getData()));
            }else{
                $bag = $dao->reject($form['currentId'],$manager,$form['note']??null);
                // event(new RejectRegistrationEvent($bag->getData()));
            }
            if($bag->isSuccess()){
                return JsonBuilder::Success($bag->getMessage());
            }
            else{
                return JsonBuilder::Error($bag->getMessage());
            }
        }
        return JsonBuilder::Error('无权执行此操作');
    }

    /**
     * 验证身份证号码是符合规范的
     * @param PlanRecruitRequest $request
     * @return string
     */
    public function verify_id_number(PlanRecruitRequest $request){
        $idNumber = $request->get('id_number');
        $bag = GradeAndYearUtil::IdNumberToBirthday($idNumber);
        if($bag->isSuccess()){
            return JsonBuilder::Success(_printDate($bag->getData()));
        }else{
            return JsonBuilder::Error($bag->getMessage());
        }
    }

    /**
     * Func 获取分班信息
     * @param PlanRecruitRequest $request
     * @return string
     */
    public function get_class_list(PlanRecruitRequest $request){
        $uuid = $request->post('uuid');
        $planId = (Int)$request->post('planId');

        // 获取用专业对应的班级
        $infos = [];
        if ($planId) {
            $recruitmentPlanObj = new RecruitmentPlanDao();
            $getPlanOneInfo = $recruitmentPlanObj->getPlan($planId);
            if (!empty($getPlanOneInfo) && $getPlanOneInfo->major_id) {
                $infos = (new GradeDao)->getGradesByMajorAndYear($getPlanOneInfo->major_id,date('Y'),
                    ['grades.id','grades.name',DB::raw("( select count(*) from grade_users where grades.id = grade_users.grade_id) count")]);
            }
        }
        return JsonBuilder::Success($infos);
    }

    /**
     * Func 保存分班信息
     *
     * @param PlanRecruitRequest $request
     * @return string
     */
    public function save_class_info(PlanRecruitRequest $request){
        $form = $request->getApprovalForm();
        $userUuid = $request->uuid();
        $userDao = new UserDao();
        $manager = $userDao->getUserByUuid($userUuid);
        if($manager && ($manager->isSchoolAdminOrAbove() || $manager->isTeacher())){
            // 操作者至少应该是学校的员工
            $dao = new RegistrationInformaticsDao();
            // $form 数据格式：Array ( [note] => [planId] => 7 [formId] => 7 [classId] => 17 )
            if ($dao->joinClass($form,$manager)) {
                return JsonBuilder::Success('操作成功');
            } else {
                return JsonBuilder::Error('操作失败,请稍后重试');
            }
        }
        return JsonBuilder::Error('无权执行此操作');
    }

}
