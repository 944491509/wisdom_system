<?php
/**
 * Created by PhpStorm.
 * User: liuyang
 * Date: 2020/3/9
 * Time: 上午11:19
 */

namespace App\Http\Controllers\Api\Study;


use App\Utils\JsonBuilder;
use App\Dao\Courses\CourseDao;
use App\Http\Controllers\Controller;
use App\Dao\Courses\CourseTeacherDao;
use App\Dao\Timetable\TimetableItemDao;
use App\Dao\Courses\Lectures\LectureDao;
use App\Http\Requests\Course\MaterialRequest;

class MaterialController extends Controller
{


    /**
     * 我的课程
     * @param MaterialRequest $request
     * @return string
     */
    public function courses(MaterialRequest $request) {
        $user = $request->user();
        $schoolId = $user->getSchoolId();
        $courseTeacherDao = new CourseTeacherDao();
        $courseTeacher = $courseTeacherDao->getCoursesByTeacher($user->id);
        if(count($courseTeacher) == 0) {
            return JsonBuilder::Success('您没有课程');
        }
        $lectureDao = new LectureDao();



        $types = $lectureDao->getMaterialType($schoolId);
        $courses = [];
        foreach ($courseTeacher as $key => $item) {
            $course = $item->course;
            $type = [];
            foreach ($types as $k => $val) {
                $re = $lectureDao->getMaterialNumByUserAndType($user->id, $val->type_id, $course->id);
                $type[] = [
                    'type_id' => $val->type_id,
                    'name' => $val->name,
                    'num' => count($re),
                ];
            }

            $courses[] = [
                'course_id' => $course->id,
                'course_name' => $course->name,
                'duration' => $course->duration,
                'desc' => $course->desc,
                'types' => $type
            ];
        }

        return JsonBuilder::Success($courses);
    }


    /**
     * 类型课程资料
     * @param MaterialRequest $request
     * @return string
     */
    public function materialsByType(MaterialRequest $request) {
        $user = $request->user();
        $typeId = $request->getType();
        $courseId = $request->getCourseId();
        if(is_null($typeId) || is_null($courseId)) {
            return JsonBuilder::Error('缺少参数');
        }

        $lectureDao = new LectureDao();

        $return = $lectureDao->getMaterialByCourseId($courseId, $typeId, $user->id);

        $material = [];
        $grade = [];
        foreach ($return as $key => $item) {
            $material[$item->lecture_id] = $item;
            $grade[$item->lecture_id][] = [
                'grade_id' => $item->grade->id,
                'grade_name' => $item->grade->name,
            ];
        }


        $result = [];
        foreach ($material as $key => $item) {
            $idx = $item->lecture->idx;
            $result[] = [
                'material_id' => $item->id,
                'desc' => $item->description,
                'url' => $item->url,
                'lecture' => '第'.$idx.'节',
                'grade' => $grade[$item->lecture_id],
            ];
        }
        return JsonBuilder::Success($result);
    }


    /**
     * 课程详情
     * @param MaterialRequest $request
     * @return string
     */
    public function courseDetails(MaterialRequest $request) {
        $courseId = $request->getCourseId();
        if(is_null($courseId)) {
            return JsonBuilder::Error('缺少参数');
        }
        $dao = new CourseDao();
        $course = $dao->getCourseById($courseId);
        $result = [
            'course_id' => $course->id,
            'course_name' => $course->name,
            'duration' => $course->duration,
            'desc' => $course->desc,
        ];

        return JsonBuilder::Success($result);
    }


    /**
     * 编辑课程详情
     * @param MaterialRequest $request
     * @return string
     */
    public function saveCourse(MaterialRequest $request) {
        $courseId = $request->getCourseId();
        $desc = $request->get('desc');
        if(is_null($courseId) || is_null($desc)) {
            return JsonBuilder::Error('缺少参数');
        }
        $dao = new CourseDao();
        $result = $dao->updateCourseDescByCourseId($courseId, $desc);
        if($result) {
            return JsonBuilder::Success('编辑成功');
        } else {
            return JsonBuilder::Error('编辑失败');
        }

    }


    /**
     * 课程班级列表和课节列表
     * @param MaterialRequest $request
     * @return string
     */
    public function getCourseGradeList(MaterialRequest $request) {
        $user = $request->user();
        $courseId = $request->getCourseId();
        if(is_null($courseId)) {
            return JsonBuilder::Error('缺少参数');
        }
        $timeTableDao = new TimetableItemDao();
        $gradeIds = $timeTableDao->getGradeListByCourseIdAndTeacherId($courseId, $user->id);
        if(count($gradeIds) == 0) {
            return JsonBuilder::Success('当前课程没有代理班级');
        }

        $courseDao = new CourseDao();
        $course = $courseDao->getCourseById($courseId);

        $grades = [];
        foreach ($gradeIds as $key => $item) {
            $grade = $item->grade;
            $grades[] = [
                'grade_id' => $grade->id,
                'grade_name' => $grade->name,
            ];
        }

        $duration = [];
        for ($i=1; $i<=$course->duration; $i++) {
            $duration[] = [
                'idx' => $i,
                'name' => '第'.$i.'节',
            ];
        }

        $data = [
            'grades' => $grades,
            'durations' => $duration,
        ];

        return JsonBuilder::Success($data);

    }


    /**
     * 上传资料
     * @param MaterialRequest $request
     * @return string
     */
    public function addMaterial(MaterialRequest $request) {
        $user = $request->user();
        $all = $request->all();
        $all['user_id'] = $user->id;
        $dao = new LectureDao();
        if(!empty($all['lecture_id'])) {
            $result = $dao->updMaterial($all);
        } else {
            $result = $dao->addMaterial($all);
        }
        $msg = $result->getMessage();
        if($result->isSuccess()) {
            return JsonBuilder::Success($msg);
        } else {
            return JsonBuilder::Error($msg);
        }
    }


    /**
     * 教材列表
     * @param MaterialRequest $request
     * @return string
     */
    public function materials(MaterialRequest $request) {
        $user = $request->user();
        $dao = new LectureDao();

        $schoolId = $user->getSchoolId();
        $lectureDao = new LectureDao();
        $types = $lectureDao->getMaterialType($schoolId);
        $lecture = $dao->getMaterialByUser($user->id);

        $materials = [];
        foreach ($lecture as $key => $val) {
            $materials[] = $val->lectureMaterials;
        }

        $lectureMaterials = [];
        foreach ($materials as $key => $value) {
            foreach ($value as $k => $val) {
                $lectureMaterials[$val->type][$val->lecture_id] = $val;
            }
        }
        $result = [];
        foreach ($types as $key => $item) {
            $re = $lectureMaterials[$item->type_id] ?? [];
            $list = [];
            foreach ($re as $k => $val) {
                $lecture_materials = $dao->getLectureMaterials($val->lecture_id, $val->type);
                $grades = [];
                foreach ($lecture_materials as $v) {
                    $grades[] = [
                        'grade_id' => $v->grade_id,
                        'grade_name' => $v->grade->name,
                    ];
                }
                $idx = '第'. $val->lecture->idx .'节';
                $list[] = [
                    'lecture_id' =>$val->lecture_id,
                    'type_id' => $val->type,
                    'desc' => $val->description,
                    'url' => $val->url,
                    'idx' => $idx,
                    'grades' => $grades,
                ];
            }
            $result[] = [
                'name' => $item->name,
                'num' => count($list),
                'list' => array_merge($list),
            ];
        }

        return JsonBuilder::Success($result);
    }


    /**
     * 查看课节资料
     * @param MaterialRequest $request
     * @return string
     */
    public function getMaterials(MaterialRequest $request) {
        $courseId = $request->getCourseId();
        $idx = $request->get('idx');
        $userId = $request->user()->id;
        $dao = new LectureDao();
        $lecture = $dao->getMaterialByCourseAndIdx($userId, $courseId, $idx);

        if(is_null($lecture)) {
            return JsonBuilder::Success('当前课程的课节没有教学资料');
        }
        $materials = $lecture->lectureMaterials;
        $gradeId = array_merge(array_unique($materials->pluck('grade_id')->toArray()));

        $material = [];
        foreach ($materials as $key => $val) {
            $material[$val->type]['type_id'] = $val->type;
            $material[$val->type]['desc'] = $val->description;
            $material[$val->type]['list'][] = [
                'url' => $val->url,
                'media_id' => $val->media_id,
            ];
        }
        $data = [
            'lecture_id' => $lecture->id,
            'title' => $lecture->title,
            'idx' => $materials[0]->idx,
            'grade' => $gradeId,
            'material' => array_merge($material),
        ];
        return JsonBuilder::Success($data);
    }

}
