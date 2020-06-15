<?php

namespace App\Http\Controllers\Operator;

use App\Dao\Schools\DepartmentDao;
use App\Dao\Schools\GradeDao;
use App\Dao\Schools\MajorDao;
use App\Dao\Schools\OrganizationDao;
use App\Dao\Schools\RoomDao;
use App\Dao\Schools\TeachingAndResearchGroupDao;
use App\Dao\Schools\YearManagerDao;
use App\Dao\Users\GradeUserDao;
use App\Dao\Users\UserDao;
use App\Dao\Users\UserOrganizationDao;
use App\Http\Requests\SchoolRequest;
use App\Http\Controllers\Controller;
use App\Dao\Schools\SchoolDao;
use App\Models\Pipeline\Flow\Handler;
use App\Models\Teachers\Teacher;
use App\Models\Users\UserSearchConfig;
use App\User;
use App\Utils\FlashMessageBuilder;
use App\Dao\Schools\InstituteDao;
use App\Utils\JsonBuilder;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SchoolsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function teaching_and_research_group(SchoolRequest $request){
        $this->dataForView['pageTitle'] = '教研组管理';
        $this->dataForView['groups'] = (new TeachingAndResearchGroupDao())->getAllBySchool($request->session()->get('school.id'));
        return view(
            'school_manager.school.teaching_and_research_groups', $this->dataForView
        );
    }

    public function teaching_and_research_group_add(SchoolRequest $request){
        $this->dataForView['pageTitle'] = '创建教研组';
        $this->dataForView['group'] = [];
        return view(
            'school_manager.school.teaching_and_research_group_add', $this->dataForView
        );
    }

    public function teaching_and_research_group_edit(SchoolRequest $request){
        $this->dataForView['pageTitle'] = '修改教研组';
        $this->dataForView['group'] = (new TeachingAndResearchGroupDao())->getById($request->uuid());
        return view(
            'school_manager.school.teaching_and_research_group_add', $this->dataForView
        );
    }

    public function teaching_and_research_group_delete(SchoolRequest $request){
        $done = (new TeachingAndResearchGroupDao())->delete($request->uuid());
        if($done){
            FlashMessageBuilder::Push($request,'success','删除成功');
        }
        else{
            FlashMessageBuilder::Push($request,'error','删除失败');
        }
        return redirect()->route('school_manager.organizations.teaching-and-research-group');
    }

    public function teaching_and_research_group_save(SchoolRequest $request){
        $saved = (new TeachingAndResearchGroupDao())->save($request->get('group'));
        return $saved ? JsonBuilder::Success(): JsonBuilder::Error();
    }

    public function teaching_and_research_group_members(SchoolRequest $request){
        $this->dataForView['pageTitle'] = '管理组员';
        $this->dataForView['group'] = (new TeachingAndResearchGroupDao())->getById($request->uuid());
        return view(
            'school_manager.school.teaching_and_research_group_members', $this->dataForView
        );
    }

    public function teaching_and_research_group_save_members(SchoolRequest $request){
        $saved = (new TeachingAndResearchGroupDao())->saveMembers($request->get('members'));
        return $saved ? JsonBuilder::Success(): JsonBuilder::Error();
    }

    public function teaching_and_research_group_delete_member(SchoolRequest $request){
        return (new TeachingAndResearchGroupDao())->deleteMember($request->get('member_id')) ?
            JsonBuilder::Success() : JsonBuilder::Error();
    }

    /**
     * 管理员选择某个学校作为操作对象
     * @param SchoolRequest $request
     * @return RedirectResponse
     */
    public function enter(SchoolRequest $request){
        $dao = new SchoolDao($request->user());
        // 获取学校
        $school = $dao->getSchoolByUuid($request->uuid());
        $school->savedInSession($request);
        return redirect()->route('school_manager.school.view');
    }

    /**
     * 更新学校的配置信息
     * @param SchoolRequest $request
     * @return RedirectResponse
     */
    public function config_update(SchoolRequest $request){
        $dao = new SchoolDao($request->user());
        $school = $dao->getSchoolByUuid($request->uuid());
        // 要比较学校中每个系的相同的配置项目, 如果学校的要求高于系的要求, 那么就要覆盖系的. 如果低于系的要求, 那么就保留
        if($school){
            $dao->updateConfiguration(
                $school,
                $request->getConfiguration(),
                $request->getElectiveCourseAvailableTerm(1),
                $request->getElectiveCourseAvailableTerm(2),
                $request->getTermStart(),
                $request->getSummerStart(),
                $request->getWinterStart()
            );
            FlashMessageBuilder::Push($request, 'success','配置已更新');
        }
        else{
            FlashMessageBuilder::Push($request, 'danger','无法获取学校数据');
        }

        if($request->get('redirectTo',null)){
            return redirect($request->get('redirectTo',null));
        }
        return redirect()->route('school_manager.school.view');
    }

    public function institutes(SchoolRequest $request){
        $instituteDao = new InstituteDao($request->user());
        $this->dataForView['institutes'] = $instituteDao->getBySchool(session('school.id'));
        $this->dataForView['pageTitle'] = '学院管理';
        return view('school_manager.school.institutes', $this->dataForView);
    }

    public function departments(SchoolRequest $request){
        $dao = new DepartmentDao($request->user());
        $this->dataForView['departments'] = $dao->getBySchool(session('school.id'));
        $this->dataForView['pageTitle'] = '院系管理';
        return view('school_manager.school.departments', $this->dataForView);
    }

    public function majors(SchoolRequest $request){
        $dao = new MajorDao($request->user());
        $this->dataForView['majors'] = $dao->getBySchool(session('school.id'));
        $this->dataForView['pageTitle'] = '专业管理';
        return view('school_manager.school.majors', $this->dataForView);
    }

    public function grades(SchoolRequest $request){
        $dao = new GradeDao($request->user());
        $this->dataForView['grades'] = $dao->getBySchool(session('school.id'));
        $this->dataForView['pageTitle'] = '班级管理';
        return view('school_manager.school.grades', $this->dataForView);
    }

    /**
     * 按年级显示
     * @param SchoolRequest $request
     * @return Factory|View
     */
    public function years(SchoolRequest $request){
        $dao = new GradeDao($request->user());
        $year = $request->get('year',date('Y'));
        $this->dataForView['grades'] = $dao->getBySchoolAndYear(session('school.id'), $year);
        $this->dataForView['year'] = $year;
        $this->dataForView['pageTitle'] = '年级管理';
        $this->dataForView['yearManager'] = (new YearManagerDao())->get(session('school.id'), $year);
        return view('school_manager.school.grades', $this->dataForView);
    }

    public function set_year_manager(SchoolRequest $request){
        if($request->method() === 'GET'){
            $this->dataForView['pageTitle'] = '年级组长管理';
            $this->dataForView['year'] = $request->get('year');
            $this->dataForView['yearManager'] = (new YearManagerDao())->get(session('school.id'), $request->get('year'));
            $this->dataForView['teachers'] = (new UserDao())->getTeachersBySchool(session('school.id'),true);
            $this->dataForView['managers'] = (new YearManagerDao())->getBySchool(session('school.id'));
            return view('school_manager.school.grade_manager', $this->dataForView);
        }
        else{
            $saved = (new YearManagerDao())->save($request->get('manager'));
            return $saved ? JsonBuilder::Success(): JsonBuilder::Error();
        }
    }

    /**
     * 教师/教工页面
     * @param SchoolRequest $request
     * @return Application|Factory|View
     */
    public function teachers(SchoolRequest $request)
    {
        $this->dataForView['pageTitle'] = '统一认证管理';
        return view('teacher.users.teachers', $this->dataForView);
    }

    /**
     * 获取 教师/教工
     * @param SchoolRequest $request
     * @return string
     */
    public function getTeachers(SchoolRequest $request)
    {
        $schoolId = $request->get('school_id');
        $where = $request->get('where');

        $dao = new GradeUserDao($request->user());
        $employees = $dao->getGradeTeacherBySchool($schoolId, $where);
        $result = pageReturn($employees);
        $list = [];

        foreach ($result['list'] as $key => $val) {
            $list[] = [
                'user_id' => $val->user_id,
                'hired' =>  $val->teacherProfile->hired ? '聘用' : '解聘',
                'name' => $val->name,
                'avatar' => $val->teacherProfile->avatar,
                'organization' => '',
                'year_manger' => '',
            ];

            $duties = Teacher::getTeacherAllDuties($val->user_id);

            if ($duties['gradeManger']) {
               $list[$key]['year_manger'] .= $duties['gradeManger']->grade->name.'班主任 ';
            }

            if ($duties['myTeachingAndResearchGroup']) {
                foreach ($duties['myTeachingAndResearchGroup'] as $k => $v) {
                    $list[$key]['year_manger'] .= $v->type.'-'.$v->name;
                }
            }

            if ($duties['myYearManger']) {
                $list[$key]['year_manger'] = $duties['myYearManger']->year.'年级主任';
            }

            foreach ($val->user->organizations as $k => $v) {
                // 行政职务
                $list[$key]['organization'] = $v->title. ' '. $list[$key]['organization'];
            }
        }

        $result['list'] = $list;
        return JsonBuilder::Success($result);
    }


    /**
     * 已认证学生页面
     * @param SchoolRequest $request
     * @return Application|Factory|View
     */
    public function students(SchoolRequest $request)
    {
        $this->dataForView['pageTitle'] = '统一认证管理';
        return view('teacher.users.students', $this->dataForView);
    }

    /**
     * 获取学生
     * @param SchoolRequest $request
     * @return string
     */
    public function getStudents(SchoolRequest $request)
    {
        $schoolId = $request->get('school_id');
        $where    = $request->get('where');

        $dao      = new GradeUserDao($request->user());
        $students = $dao->getByStudentsBySchool($schoolId, $where, GradeUserDao::TYPE_SELECT);
        $result   = pageReturn($students);
        $data     = [];
        foreach ($result['list'] as $student) {
            $data[] = [
                'user_id'        => $student->user_id,
                'student_number' => $student->studentProfile->student_number ?? '-',
                'name'           => $student->name,
                'mobile'         => $student->mobile,
                'grade'          => $student->studyAt(),
                'enquiries'      => count($student->enquiries),
                'grade_id'       => $student->grade_id,
                'uuid'           => $student->user->uuid,
                'status'         => $student->user->getStatusText()
            ];
        }
        $result['list'] = $data;
        return JsonBuilder::Success($result);
    }

    /**
     * 全选修改学生状态
     * @param SchoolRequest $request
     * @return string
     */
    public function updateStudentStatus(SchoolRequest $request)
    {
        $schoolId = $request->get('school_id');
        $where    = $request->get('where');
        $status   = $request->get('update_status');
        $dao      = new GradeUserDao($request->user());
        $result   = $dao->getByStudentsBySchool($schoolId, $where, GradeUserDao::TYPE_UPDATE, $status);
        if ($result) {
            return JsonBuilder::Success('修改成功');
        } else {
            return JsonBuilder::Error('修改失败');
        }
    }

    /**
     * 批量修改学生状态
     * @param SchoolRequest $request
     * @return string
     */
    public function updateStatus(SchoolRequest $request)
    {
        $userIds = $request->get('user_id');
        $status = $request->get('status');

        $dao = new UserDao;
        $result = $dao->updateStudentStatusByIds(explode(',', $userIds), $status);
        if ($result) {
            return JsonBuilder::Success('修改成功');
        } else {
            return JsonBuilder::Error('修改失败');
        }
    }




    /**
     * 搜索条件
     * @param SchoolRequest $request
     * @return string
     */
    public function searchConfig(SchoolRequest $request)
    {
        $type = $request->get('type');
        $data = UserSearchConfig::where('type', $type)->get();
        return JsonBuilder::Success($data);
    }

    /**
     * 搜索条件 学生状态
     * @param SchoolRequest $request
     * @return string
     */
    public function studentStatus(SchoolRequest $request)
    {
        $data = [
            User::STATUS_VERIFIED   => User::STUDENT_STATUS_VERIFIED_TEXT,
            User::STATUS_SUSPENSION => User::STUDENT_STATUS_SUSPENSION_TEXT,
            User::STATUS_DROP_OUT   => User::STUDENT_STATUS_DROP_OUT_TEXT,
            User::STATUS_TRANSFER   => User::STUDENT_STATUS_TRANSFER_TEXT,
            User::STATUS_FINISH     => User::STUDENT_STATUS_FINISH_TEXT,
        ];
        return JsonBuilder::Success($data);
    }


    public function rooms(SchoolRequest $request)
    {
        $dao                            = new RoomDao($request->user());
        $this->dataForView['rooms']     = $dao->getRoomsPaginate([['school_id', '=', session('school.id')]]);
        $this->dataForView['pageTitle'] = '物业管理';
        return view('school_manager.school.rooms', $this->dataForView);
    }

    /**
     * 加载学校的组织机构
     * @param SchoolRequest $request
     * @return Factory|View
     */
    public function organization(SchoolRequest $request)
    {
        $this->dataForView['pageTitle'] = '组织架构';
        $dao                            = new OrganizationDao();
        $this->dataForView['root']      = $dao->getRoot($request->getSchoolId());
        $this->dataForView['level']     = $dao->getTotalLevel($request->getSchoolId());
        return view('school_manager.school.organization', $this->dataForView);
    }

    /**
     * @param SchoolRequest $request
     * @return string
     */
    public function load_parent(SchoolRequest $request){
        $level = intval($request->get('level')) - 1;
        $orgs = [];
        if($level > 0){
          $dao = new OrganizationDao();
          $orgs = $dao->loadByLevel($level, $request->getSchoolId());
        }
        // 获取职务数据
        $organizationLevels = (new Handler())->OrganizationLevels();
        return JsonBuilder::Success(['parents'=>$orgs,'organizationLevels'=>$organizationLevels]);
    }

    public function load_by_orgs(SchoolRequest $request) {
        $schoolId = $request->get('school_id');
        $orgArr = $request->get('orgs');
        $dao = new OrganizationDao();
        $parents = $dao->loadByLevel(1, $schoolId);
        $return = $parents;
        $nowNode = $return;
        foreach ($orgArr as $orgid) {
            foreach ($parents as $pkey => $parent) {
                if ($parent->id == $orgid) {
                    $nowNode[$pkey]->children = $dao->getByParentId($schoolId, $orgid);
                    $parents = $nowNode[$pkey]->children;
                    $nowNode = $nowNode[$pkey]->children;
                    break;
                }
            }
        }
        return JsonBuilder::Success($return);
    }

    /**
     * 前端要求返回所有节点的树形结构
     * @param SchoolRequest $request
     * @return string
     */
    public function load_all(SchoolRequest $request) {
        $schoolId = $request->get('school_id');
        $dao = new OrganizationDao();
        $parents = $dao->loadByLevel(1, $schoolId);
        $return = [];
        foreach ($parents as $parent) {
            $return[] = $dao->outputOnlyData($parent);
        }
        return JsonBuilder::Success($return);
    }

    /**
     * 获取某个级别或者指定的父级单位的下级单位集合
     * @param SchoolRequest $request
     * @return string
     */
    public function load_children(SchoolRequest $request){
        $level = intval($request->get('level'));
        $parentId = $request->get('parent_id', null);
        $dao = new OrganizationDao();
        $orgs = [];
        if($parentId){
            $orgs = $dao->getById($parentId)->branch;
        }
        else{
            $orgs = $dao->loadByLevel($level, $request->getSchoolId());
        }
        return JsonBuilder::Success(['orgs'=>$orgs]);
    }

    /**
     * 保存组织结构
     * @param SchoolRequest $request
     * @return string
     */
    public function save_organization(SchoolRequest $request){
        $form = $request->get('form');
        $form['school_id'] = $request->getSchoolId();
        $dao = new OrganizationDao();
        if(isset($form['id']) && !empty($form['id'])){
            $id = $form['id'];
            unset($form['id']);
            if(isset($form['members'])) {
                unset($form['members']);
            }
            if(isset($form['updated_at'])) {
                unset($form['updated_at']);
            }
            $org = $dao->update($form, $id);
        }
        else{
            $org = $dao->create($form);
        }
        return JsonBuilder::Success(['org'=>$org]);
    }

    /**
     * 保存组织结构
     * @param SchoolRequest $request
     * @return string
     */
    public function load_organization(SchoolRequest $request){
        $id = $request->get('organization_id');
        $dao = new OrganizationDao();
        $org = $dao->getById($id);
        return JsonBuilder::Success(['organization'=>$org,'members'=>$org->members]);
    }


    /**
     * 删除组织结构及人员
     * @param SchoolRequest $request
     * @return string
     * @throws Exception
     */
    public function delete_organization(SchoolRequest $request){
        $id = $request->get('organization_id');
        $dao = new OrganizationDao();

        try {
            DB::beginTransaction();
            $dao->deleteOrganization($id);
            DB::commit();
            return JsonBuilder::Success();
        } catch (Exception $e) {
            DB::rollBack();
            $msg = $e->getMessage();
            return JsonBuilder::Error($msg);
        }

    }

    /**
     * 保存机构成员
     * @param SchoolRequest $request
     * @return string
     */
    public function add_member(SchoolRequest $request){
        $dao = new UserOrganizationDao();
        $member = $request->get('member');
        if(empty($member['id'])){
            $result = $dao->create($member);
            if($result->isSuccess()){
                return JsonBuilder::Success(['id'=>$result->getData()->id]);
            }
            else{
                return JsonBuilder::Error($result->getMessage());
            }
        }
        else{
            // 更新
            $id = $member['id'];
            unset($member['id']);
            $result = $dao->update($id, $member);
            if($result->isSuccess()){
                return JsonBuilder::Success(['id'=>$id]);
            }
            else{
                return JsonBuilder::Error($result->getMessage());
            }
        }
    }

    /**
     * @param SchoolRequest $request
     * @return string
     */
    public function remove_member(SchoolRequest $request){
        $dao = new UserOrganizationDao();
        if($dao->delete($request->get('id'))){
            return JsonBuilder::Success();
        }
        else{
            return JsonBuilder::Error();
        }
    }
}
