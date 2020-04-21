<?php

namespace App\Http\Controllers\Api\ElectiveCourse;

use App\Dao\ElectiveCourses\TeacherApplyElectiveCourseDao;
use App\Dao\Schools\MajorDao;
use App\Dao\Schools\SchoolDao;
use App\Dao\Timetable\TimeSlotDao;
use App\Http\Requests\School\OaElectiveCourseRequest;
use App\Models\ElectiveCourses\TeacherApplyElectiveCourse;
use App\Models\Schools\SchoolConfiguration;
use App\Utils\JsonBuilder;
use App\Utils\Time\GradeAndYearUtil;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

/*
 * 教师端APP选修课相关接口
 */
class OaElectiveCourseController extends Controller
{
    /**
     * 申请选修课的一些配置项
     * @param OaElectiveCourseRequest $request
     * @return string
     */
    public function conf(OaElectiveCourseRequest $request)
    {
        $user = $request->user();
        $schoolId = $user->getSchoolId();
        $majorDao = new MajorDao($user);
        $majors = $majorDao->getMajorsBySchool($schoolId);
        $schoolDao = new SchoolDao();
        $school = $schoolDao->getSchoolById($schoolId);
        $weeks = $school->configuration->study_weeks_per_term;
        $campuses = [];
        foreach ($school->campuses as $item) {
            $campuses[] = [
                'id' => $item->id,
                'name' => $item->name
            ];
        }
        $yearArr = $school->configuration->yearText();

        return JsonBuilder::Success(['majors' => $majors, 'weeks' => $weeks, 'years' => $yearArr, 'times' => [], 'campuses' => $campuses]);
    }

    public function times(OaElectiveCourseRequest $request) {
        $user = $request->user();
        $schoolId = $user->getSchoolId();
        $year = $request->get('year');
        $timeSlotDao = new TimeSlotDao();
        $times = $timeSlotDao->getAllStudyTimeSlots($schoolId, $year, true);
        return JsonBuilder::Success(['times' => $times]);
    }

    /**
     * APP内申请选修课
     * @param OaElectiveCourseRequest $request
     * @return string
     */
    public function apply(OaElectiveCourseRequest $request)
    {
        $user = $request->user();
        $schoolId = $user->getSchoolId();
        $dao = new TeacherApplyElectiveCourseDao();

        $nowYearAndTerm = GradeAndYearUtil::GetYearAndTerm(Carbon::now());

        $applyData = $request->getInputData();
        $applyData['course']['school_id'] = $schoolId;
        $applyData['course']['teacher_id'] = $user->id;
        $applyData['course']['teacher_name'] = $user->name ?? 'n.a';
        //@TODO UI缺少的参数 随便写的默认值 可能UI会调整也可能后台会调整
        $applyData['course']['code'] = 'auto';
        $applyData['course']['scores'] = 1;
        $applyData['course']['term'] = $nowYearAndTerm['term'];
        $applyData['course']['max_num'] = $applyData['course']['open_num'];
        $applyData['course']['start_year'] = $nowYearAndTerm['year'];
        $applyData['course']['status'] = TeacherApplyElectiveCourse::STATUS_WAITING_FOR_VERIFIED;

        if ($dao->checkTimeConflictByTeacherId($applyData['schedule'], $applyData['course']['start_year'], $applyData['course']['term'], $user->id)){
            return JsonBuilder::Error('授课时间冲突');
        }
        $result = $dao->createTeacherApplyElectiveCourse($applyData);

        return $result->isSuccess() ?
            JsonBuilder::Success('申请成功')
            : JsonBuilder::Error($result->getMessage());
    }

    public function lists(OaElectiveCourseRequest $request)
    {
        $user = $request->user();
        $schoolId = $user->getSchoolId();
        $type = $request->getInputType();
        $dao = new TeacherApplyElectiveCourseDao();
        $result = $dao->getApplicationsByTeacher($user->id, $schoolId, $type, $request->getInputPage());
        return JsonBuilder::Success($result);
    }

    public function applylists(OaElectiveCourseRequest $request)
    {
        $user = $request->user();
        $schoolId = $user->getSchoolId();
        $dao = new TeacherApplyElectiveCourseDao();
        $result = $dao->getApplications2ByTeacher($user->id, $schoolId);
        return JsonBuilder::Success($result);
    }

    public function info(OaElectiveCourseRequest $request)
    {
        $user = $request->user();
        $schoolId = $user->getSchoolId();
        $applyDao = new TeacherApplyElectiveCourseDao();
        $app  = $applyDao->getApplicationByTeacherById($request->getInputApplyid(), $schoolId);
        $schoolDao = new SchoolDao();
        $school = $schoolDao->getSchoolById($schoolId);
        $yearArr = $school->configuration->yearText();
        if (!empty($app['year'])) {
            foreach ($yearArr as $year) {
                if ($year['year'] == $app['year']) {
                    $app['year'] = $app['text'];
                }
            }
        }

        return $app ?
            JsonBuilder::Success($app)
            : JsonBuilder::Error();
    }

    public function applyinfo(OaElectiveCourseRequest $request)
    {
        $applyDao = new TeacherApplyElectiveCourseDao();
        $app  = $applyDao->getApplication2ByTeacherById($request->getInputApplyid());

        $user = $request->user();
        $schoolId = $user->getSchoolId();
        $schoolDao = new SchoolDao();
        $school = $schoolDao->getSchoolById($schoolId);
        $yearArr = $school->configuration->yearText();
        if (!empty($app['year'])) {
            foreach ($yearArr as $year) {
                if ($year['year'] == $app['year']) {
                    $app['year'] = $app['text'];
                }
            }
        }

        return $app ?
            JsonBuilder::Success($app)
            : JsonBuilder::Error();
    }
}
