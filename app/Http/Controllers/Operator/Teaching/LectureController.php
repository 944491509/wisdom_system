<?php


namespace App\Http\Controllers\Operator\Teaching;


use App\Dao\Courses\Lectures\LectureDao;
use App\Http\Controllers\Controller;
use App\Http\Requests\Course\MaterialRequest;

class LectureController extends Controller
{

    /**
     * @param MaterialRequest $request
     */
    public function index(MaterialRequest $request) {
        $schoolId = $request->getSchoolId();
        $dao = new LectureDao();
        $teacher = $dao->getLectureTeacherBySchoolId($schoolId);
        $teacherIds = array_column($teacher->toArray(), 'teacher_id');
        $result = [];
        foreach ($teacherIds as $key => $val) {
            // 根据教师ID获取教学资料
            $result[] = $dao->getLectureByTeacherId($val);
        }
//        dd($result);
        $this->dataForView['list'] = $result;
        return view('school_manager.teaching.lecture.list', $this->dataForView);
    }

}
