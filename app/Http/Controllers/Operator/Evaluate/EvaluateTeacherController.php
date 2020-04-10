<?php


namespace App\Http\Controllers\Operator\Evaluate;


use App\Dao\Users\GradeUserDao;
use App\Utils\FlashMessageBuilder;
use App\Http\Controllers\Controller;
use App\Dao\Timetable\TimetableItemDao;
use App\Dao\Evaluate\EvaluateStudentDao;
use App\Dao\Evaluate\EvaluateTeacherDao;
use App\Models\Evaluate\EvaluateTeacher;
use App\Http\Requests\Evaluate\EvaluateTeacherRequest;

class EvaluateTeacherController extends Controller
{
    /**
     * 评教老师列表
     * @param EvaluateTeacherRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function list(EvaluateTeacherRequest $request) {
        $schoolId = $request->getSchoolId();
        $dao = new EvaluateTeacherDao();
        $list = $dao->getEvaluateTeacherList($schoolId);
        $this->dataForView['list'] = $list;
        return view('school_manager.evaluate.evaluate_teacher.list',$this->dataForView);
    }

    /**
     * 创建评教 选择评教学生
     * @param EvaluateTeacherRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(EvaluateTeacherRequest $request) {
        $data = $request->getFormDate();
        // 请选择学生
        if(empty($data['user_id'])) {
            FlashMessageBuilder::Push($request,FlashMessageBuilder::DANGER,'请选择学生');
            return redirect()->route('school_manager.evaluate.student-list',['grade_id'=>$data['grade_id']]);
        }
        $schoolId = $request->getSchoolId();
        // 查询当前班级的代课老师
        $itemDao = new TimetableItemDao();
        $teachers = $itemDao->getItemByGradeId($data['grade_id'],$data['year'],$data['term']);
        if(count($teachers) == 0) {
            FlashMessageBuilder::Push($request,FlashMessageBuilder::DANGER,'当前班级没有代课老师');
            return redirect()->route('school_manager.evaluate-teacher.grade');
        }

        $dao = new EvaluateTeacherDao();
        $result = $dao->create($teachers, $data['year'], $data['term'], $schoolId, $data['user_id'], $data['grade_id']);
        $msg = $result->getMessage();
        // todo 给选中的学生推送评教信息
        if($result->isSuccess()) {
            FlashMessageBuilder::Push($request, FlashMessageBuilder::SUCCESS,$msg);
        } else {
            FlashMessageBuilder::Push($request, FlashMessageBuilder::DANGER,$msg);
        }
        return redirect()->route('school_manager.evaluate.teacher-list');
    }





    // 评教的班级列表
    public function evaluateGradeList(EvaluateTeacherRequest $request) {
        $evaluateTeacherId = $request->get('evaluate_teacher_id');
        $dao = new EvaluateStudentDao();
        $grades = $dao->getEvaluateGradeListByEvaluateTeacherId($evaluateTeacherId);

        $this->dataForView['grades'] = $grades;
        $this->dataForView['evaluate_teacher_id'] = $evaluateTeacherId;
        $this->dataForView['pageTitle'] = '班级列表';
        return view('school_manager.evaluate.evaluate_teacher.grades', $this->dataForView);
    }


    /**
     * 学生列表
     * @param EvaluateTeacherRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function evaluateStudentList(EvaluateTeacherRequest $request) {
        $gradeId = $request->getGradeId();
        $evaluateTeacherId = $request->get('evaluate_teacher_id');
        $dao = new EvaluateStudentDao();
        $students = $dao->getStudentByGradeIdAndEvaluateTeacherId($gradeId, $evaluateTeacherId);
        $this->dataForView['pageTitle'] = '评分学生列表';
        $this->dataForView['students'] = $students;
        return view('school_manager.evaluate.evaluate_teacher.student', $this->dataForView);
    }

}
