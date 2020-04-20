<?php

namespace App\Http\Controllers\Admin;

use App\Dao\Users\GradeUserDao;
use App\Dao\Users\UserDao;
use App\Http\Requests\SchoolRequest;
use App\Http\Controllers\Controller;
use App\Dao\Schools\SchoolDao;
use App\Models\Acl\Role;
use App\Models\School;
use App\Models\Schools\SchoolResource;
use App\User;
use App\Utils\FlashMessageBuilder;

class SchoolsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * 加载用户表的编辑页面
     * @param SchoolRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add(SchoolRequest $request){
        $this->dataForView['school'] = new School();
        return view('admin.schools.add', $this->dataForView);
    }

    /**
     * 加载用户表的编辑页面
     * @param SchoolRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(SchoolRequest $request){
        $dao = new SchoolDao($request->user());
        $this->dataForView['school'] = $dao->getSchoolByUuid($request->uuid());
        return view('admin.schools.edit', $this->dataForView);
    }

    /**
     * 加载用户表的编辑页面
     * @param SchoolRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function update(SchoolRequest $request){
        $dao = new SchoolDao($request->user());
        $schoolData = $request->get('school');

        if ($request->has('logo')) {
            $schoolLogo = $request->file('logo')->store(SchoolResource::DEFAULT_UPLOAD_PATH_PREFIX);
            if ($schoolLogo) {
                $schoolData['logo'] = SchoolResource::schoolResourceUploadPathToUrl($schoolLogo);
            }
        }

        if(isset($schoolData['uuid'])){
            $result = $dao->updateSchool($schoolData);
        }
        else{
            $result = $dao->createSchool($schoolData);
        }

        if($result){
            // 保存成功
            FlashMessageBuilder::Push($request,FlashMessageBuilder::SUCCESS, '学校"'.$schoolData['name'].'"信息保存成功!');
        }
        else{
            FlashMessageBuilder::Push($request,FlashMessageBuilder::DANGER, '学校"'.$schoolData['name'].'"信息保存失败!');
        }
        return redirect()->route('home');
    }


    /**
     * 创建管理员
     * @param SchoolRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function create_school_manager(SchoolRequest $request){
        if($request->isMethod('post')){
            $data = $request->all();
            $userData = $data['user'];
            $userDao = new UserDao();
            $result = $userDao->createSchoolManager(
                $data['school_id'],
                $userData['mobile'],
                $userData['password'],
                $userData['name'],
                $userData['type']
            );
            $msg = $result->getMessage();
            if($result->isSuccess()){
                FlashMessageBuilder::Push($request,FlashMessageBuilder::SUCCESS,'学校管理员创建成功');
                return redirect()->route('admin.list.school-manager',['school_id'=>$data['school_id']]);
            }
            else{
                FlashMessageBuilder::Push($request,FlashMessageBuilder::WARNING,$msg);
                return redirect()->route('admin.create.school-manager',['school_id' => $data['school_id']]);
            }
        } else {
            $schoolId = $request->getSchoolId();
            $this->dataForView['pageTitle'] = '创建学校管理员';
            $this->dataForView['school'] = (new SchoolDao())->getSchoolById($schoolId);
            $this->dataForView['type'] = [
                [
                    'id' => Role::SCHOOL_MANAGER,
                    'name' => '学校管理员',
                ],
                [
                    'id' => Role::TEACHER,
                    'name' => '教师'
                ]
            ];
            return view('admin.schools.add_school_manager',$this->dataForView);
        }
    }


    /**
     * 编辑管理员
     * @param SchoolRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit_school_manager(SchoolRequest $request) {
        if($request->isMethod('post')) {
            $data = $request->all();

            $userData = $data['user'];
            $userDao = new UserDao();
            $result = $userDao->updateSchoolManager(
                $userData['user_id'],
                $userData['mobile'],
                $userData['password'],
                $userData['name'],
                $userData['type']
            );
            $msg = $result->getMessage();
            if($result->isSuccess()) {
                FlashMessageBuilder::Push($request,FlashMessageBuilder::SUCCESS,$msg);
                return redirect()->route('admin.list.school-manager',['school_id'=>$data['school_id']]);
            } else {
                FlashMessageBuilder::Push($request,FlashMessageBuilder::WARNING,$msg);
                return redirect()->route('admin.edit.school-manager',['user_id' => $userData['user_id']]);
            }

        } else {
            $userId = $request->getUserId();
            $gradeDao = new GradeUserDao();
            $user = $gradeDao->getUserInfoByUserId($userId);
            $this->dataForView['pageTitle'] = '编辑学校管理员';
            $this->dataForView['user'] = $user;
            $this->dataForView['school'] = $user->school;
            $this->dataForView['type'] = [
                ['id' => Role::SCHOOL_MANAGER, 'name' => '学校管理员'],
                ['id' => Role::TEACHER, 'name' => '教师'],
            ];
            return view('admin.schools.edit_school_manager',$this->dataForView);
        }
    }


    /**
     * 管理员列表
     * @param SchoolRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list_school_manager(SchoolRequest $request) {
        $schoolId = $request->getSchoolId();
        $gradeUserDao = new GradeUserDao();
        $list = $gradeUserDao->getSchoolManagerBySchoolId($schoolId);
        $this->dataForView['pageTitle'] = '学校管理员列表';

        $this->dataForView['school_id'] = $schoolId;
        $this->dataForView['list'] = $list;
        return view('admin.schools.list_school_manager',$this->dataForView);
    }
}
