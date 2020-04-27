<?php

namespace App\Http\Controllers\Api\School;

use App\Dao\Courses\CourseDao;
use App\Dao\ElectiveCourses\TeacherApplyElectiveCourseDao;
use App\Utils\JsonBuilder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CoursesController extends Controller
{
    public function save_course(Request $request){
        $courseData = $request->get('course');
        $courseData['school_id'] = $request->get('school');

        $dao = new CourseDao();
        if(empty($courseData['id'])){
            // 创建新课程
            $result = $dao->createCourse($courseData);
        }
        else{
            // 更新操作
            $result = $dao->updateCourse($courseData);
        }

        $course = $result->getData();



        return $result->isSuccess() ?
            JsonBuilder::Success(['id'=>$course->id ?? $courseData['id']])
            : JsonBuilder::Error($result->getMessage());
    }

    /**
     * @param Request $request
     * @return string
     */
    public function delete_course(Request $request){
        $courseUuid = $request->get('course');
        $dao = new CourseDao();
        $course = $dao->getCourseByUuid($courseUuid);
        if(count($course->timetableItems)) {
            return JsonBuilder::Error('该课程已经在课程表中安排了课时, 无法删除');
        }
        // 判断是选修课还是必修课
        if($course->OBLIGATORY_COURSE) {
            $result = $dao->deleteCourseByUuid($course);
        } else {
            // 解散课程
            $optionalCourseDao = new TeacherApplyElectiveCourseDao();
            $result = $optionalCourseDao->discolved($course->id);
        }
        return $result->isSuccess() ? JsonBuilder::Success() : JsonBuilder::Error($result->getMessage());
    }

    /**
     * @param Request $request
     * @return string
     */
    public function load_courses(Request $request){
        $schoolId = $request->get('school');
        $dao = new CourseDao();
        $courses = $dao->getCoursePageBySchoolId($schoolId);
        return JsonBuilder::Success($courses);
    }
}
