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
        return view('student.edit', $this->dataForView);
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
