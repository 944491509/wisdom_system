<?php
namespace App\Http\Controllers\Student;

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

class StudentsController extends Controller
{
    /**
     * @param StudentRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(StudentRequest $request){
        $dao = new UserDao();
        $student = $dao->getUserByUuid($request->uuid());
        $this->dataForView['gradeManager'] = GradeManager::where('monitor_id',$student->id)->first();
        $this->dataForView['student'] = $student;
        $grade = $student->gradeUser();
        $this->dataForView['is_show'] = $grade?1:0;
        $this->dataForView['pageTitle'] = '档案管理';
        $this->dataForView['addition'] = StudentAdditionInformation::where('user_id', $student['id'])->first();
        $this->dataForView['gradeUser'] = GradeUser::where('user_id', $student['id'])->first();
        return view('student.edit', $this->dataForView);
    }

    /**
     * @param StudentRequest $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function update(StudentRequest $request) {
        $data = $request->getFormData();
        $userId = $data['user']['id'];
        $uuid = $data['user']['uuid'];
        unset($data['user']['uuid']);
        unset($data['user']['id']);
        $dao = new StudentProfileDao;
        if ($data['user']['status'] != User::STATUS_VERIFIED) {
            $data['user']['type'] = Role::REGISTERED_USER;
            $data['grade_user']['user_type'] = Role::REGISTERED_USER;
        }
        $result = $dao->updateStudentInfoByUserId($userId, $data['user'], $data['profile'], $data['addition'], $data['grade_user']);

        if($result->isSuccess()){
            FlashMessageBuilder::Push($request, FlashMessageBuilder::SUCCESS,'编辑成功');
        }else{
            FlashMessageBuilder::Push($request, FlashMessageBuilder::DANGER,'编辑失败');
        }
        return redirect()->route('verified_student.profile.edit',['uuid'=>$uuid]);
    }

    /**
     * 通讯录页面
     * @param StudentRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function contacts_list(StudentRequest $request){
        $this->dataForView['pageTitle'] = '通讯录';
        $this->dataForView['schoolId'] = $request->getSchoolId();
        return view('student.contacts.list',$this->dataForView);
    }
}
