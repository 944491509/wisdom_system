<?php


namespace App\Http\Controllers\Api\AttendanceSchedule;


use App\Utils\Time\GradeAndYearUtil;
use Carbon\Carbon;
use App\Utils\JsonBuilder;
use App\Dao\Schools\SchoolDao;
use App\Dao\Timetable\TimeSlotDao;
use App\Dao\Courses\CourseMajorDao;
use App\Http\Controllers\Controller;
use App\Dao\Timetable\TimetableItemDao;
use App\Http\Requests\MyStandardRequest;
use App\Models\AttendanceSchedules\Attendance;
use App\BusinessLogic\Attendances\Attendances;
use App\Dao\AttendanceSchedules\AttendancesDao;
use App\Models\AttendanceSchedules\AttendancesDetail;
use App\Dao\AttendanceSchedules\AttendancesDetailsDao;
use App\Http\Requests\AttendanceSchedule\AttendanceRequest;

class AttendanceController extends Controller
{
    /**
     * 课程签到列表
     * @param AttendanceRequest $request
     * @return string
     */
    public function signInRecord(AttendanceRequest $request)
    {

        $user = $request->user();
        $grade = $user->gradeUser->grade;
        $school = $user->gradeUser->school;
        $configuration = $school->configuration;
        // 学年
        $year = $request->get('year', $configuration->getSchoolYear());
        // 学期
        $term = $request->get('term', $configuration->guessTerm(Carbon::now()->month));

        $attendancesDetailsDao = new AttendancesDetailsDao();
        // 查询课程列表
        $timeTableDao = new TimetableItemDao();
        $courseIds = $timeTableDao->getCoursesByYearAndTermAndGradeId($year, $term, $grade->id);

        $courseList = [];
        foreach ($courseIds as $key => $val) {

            // 签到次数
            $signNum = $attendancesDetailsDao->getSignInCountByUser($user->id, $year, $term, $val->course_id);
            // 请假次数
            $leavesNum = $attendancesDetailsDao->getLeaveCountByUser($user->id, $year, $term, $val->course_id);
            // 旷课次数
            $truantNum = $attendancesDetailsDao->getTruantCountByUser($user->id, $year, $term, $val->course_id);
            $courseList[] = [
                'id' => $val->course_id,
                'name' => $val->course->name,
                'sign_num' => $signNum,
                'leaves_num' => $leavesNum,
                'truant_num' => $truantNum,
            ];
        }

        return JsonBuilder::Success($courseList);
    }


    /**
     * 签到详情列表
     * @param AttendanceRequest $request
     * @return string
     */
    public function signInDetails(AttendanceRequest $request)
    {

        $courseId = $request->get('course_id');
        if (empty($courseId)) {
            return JsonBuilder::Error('缺少参数');
        }

        $user = $request->user();
        $school = $user->gradeUser->school;
        $configuration = $school->configuration;
        $year = $request->get('year', $configuration->getSchoolYear());
        // 学期
        $term = $request->get('term', $configuration->guessTerm(Carbon::now()->month));

        $attendancesDetailsDao = new AttendancesDetailsDao();
        $signInList = $attendancesDetailsDao->signInList($year, $user->id, $courseId, $term);
        foreach ($signInList as $key => $val) {

            $signInList[$key]['time_slots'] = $val->timetable->timeSlot->name;
            $signInList[$key]['weekday_index'] = $val->timetable->weekday_index;
            $signInList[$key]['date'] = Carbon::parse($val->created_at)->format('Y-m-d');
            $signInList[$key]['time'] = '';

            if($val['mold'] == AttendancesDetail::MOLD_SIGN_IN) {
                $signInList[$key]['time'] = Carbon::parse($val->created_at)->format('H:i');

            }
            unset($val->status);
            unset($val->timetable);
            unset($val->timetable_id);
        }
        $data = array_merge($signInList->toArray());
        return JsonBuilder::Success($data);
    }


    /**
     * 添加旷课记录
     * @param AttendanceRequest $request
     * @return string
     */
    public function addTruantRecord(AttendanceRequest $request)
    {

        $truant = $request->getTruantData();
        $timeTableDao = new TimetableItemDao();
        $item = $timeTableDao->getItemById($truant['timetable_id']);
        $data = Carbon::parse($truant['date']);
        $week = $item->school->configuration->getScheduleWeek($data)->getScheduleWeekIndex();

        $attendanceDao = new AttendancesDao();
        $attendanceInfo = $attendanceDao->getAttendanceByTimeTableId($item->id, $week);
        if (is_null($attendanceInfo)) {
            return JsonBuilder::Error('该课程还没上');
        }
        $truant['attendance_id'] = $attendanceInfo->id;
        $truant['course_id'] = $item->course_id;
        $truant['year'] = $item->year;
        $truant['term'] = $item->term;
        $truant['week'] = $week;
        $truant['mold'] = AttendancesDetail::MOLD_TRUANT;
        $truant['weekday_index'] = $item->weekday_index;
        $dao = new AttendancesDetailsDao();
        $re = $dao->getDetailByUserId($truant['student_id'], $attendanceInfo->id);
        if (!empty($re)) {
            return JsonBuilder::Success('旷课已添加');
        }
        $result = $dao->add($truant);
        if ($result) {
            return JsonBuilder::Success('旷课添加成功');
        } else {
            return JsonBuilder::Error('旷课添加失败');
        }
    }


    /**
     * 开启补签
     * @param MyStandardRequest $request
     * @return string
     */
    public function startSupplement(MyStandardRequest $request)
    {
        $attendanceId = $request->get('attendance_id');
        $type = $request->get('type');

        $dao = new AttendancesDao;

        $result = $dao->update($attendanceId, ['supplement_sign' => $type]);
        if ($result) {
            return JsonBuilder::Success('修改成功');
        } else {
            return JsonBuilder::Error('修改失败');
        }
    }

    /**
     * 教师扫码云班牌
     * @param MyStandardRequest $request
     * @return string
     * @throws \Exception
     */
    public function teacherSweepQrCode(MyStandardRequest $request)
    {
        $user = $request->user();
        $code = json_decode($request->get('code'), true);

        if ($code['teacher_id'] !== $user->id) {
            return JsonBuilder::Error('本节课, 不是您要上的课');
        }

        $timeTableDao = new TimetableItemDao;
        // 同时上多个课程 只取第一个
        $items = $timeTableDao->getCurrentItemByUser($user);
        if (is_null($items) || $items->isEmpty()) {
            return JsonBuilder::Error('未找到您目前要上的课程');
        }
        $item = $items[0];
        $attendancesDao = new AttendancesDao;
        $schoolDao = new SchoolDao;
        $school = $schoolDao->getSchoolById($item->school_id);
        $configuration = $school->configuration;
        $now = Carbon::now(GradeAndYearUtil::TIMEZONE_CN);

        $month = Carbon::parse($now)->month;
        $term = $configuration->guessTerm($month);
        $weeks = $configuration->getScheduleWeek($now, null, $term);
        $week = $weeks->getScheduleWeekIndex();
        $arrive = $attendancesDao->isAttendanceByTimetableAndWeek($item, $week);

        if ($arrive->teacher_sign == Attendance::TEACHER_SIGN) {
            $isArrive = true;
        } else {
            $isArrive = false;
        }

        $data['timetable_id'] = $item->id;
        $data['time_slot_name'] = $item->timeSlot->name;
        $data['course_name'] = $item->course->name;
        $data['teacher'] = $item->teacher->name;
        $data['room'] = $item->room->name;
        $data['is_arrive'] = $isArrive;
        $data['arrive_time'] = $arrive->teacher_sign_time;

        return JsonBuilder::Success($data);
    }

    /**
     * 教师上课签到
     * @param MyStandardRequest $request
     * @return string
     * @throws \Exception
     */
    public function teacherSign(MyStandardRequest $request)
    {
        $user = $request->user();

        $timetableItemDao = new TimetableItemDao;
        $item = $timetableItemDao->getCurrentItemByUser($user);
        if (empty($item)) {
            return JsonBuilder::Error('未找到该老师目前上的课程');
        }
        $item = $item[0];

        $dao = new AttendancesDao;
        $schoolDao = new SchoolDao;
        $school = $schoolDao->getSchoolById($item->school_id);
        $configuration = $school->configuration;
        $now = Carbon::now(GradeAndYearUtil::TIMEZONE_CN);

        $month = Carbon::parse($now)->month;
        $term = $configuration->guessTerm($month);
        $weeks = $configuration->getScheduleWeek($now, null, $term);
        $week = $weeks->getScheduleWeekIndex();
        $result = $dao->isAttendanceByTimetableAndWeek($item, $week);
        if ($result->teacher_sign == Attendance::TEACHER_SIGN) {
             return JsonBuilder::Success('您已经签到了');
        }

        $courseTime = Carbon::parse($item->timeSlot->from);

        $date = Carbon::now();
        // 当前时间大于开课时间3分钟为迟到
        if($date > $courseTime && $date->diffInMinutes($courseTime) > 3) {
            $late = Attendance::TEACHER_LATE;
        } else {
            $late = Attendance::TEACHER_NO_LATE;
        }

        $data = $dao->updateTeacherSignByItem($result->id, $late);
        if ($data) {
            return JsonBuilder::Success('签到成功');
        } else {
            return JsonBuilder::Error('签到失败');
        }
    }


    /**
     * 学生扫码云班牌
     * @param MyStandardRequest $request
     * @return string
     * @throws \Exception
     */
    public function studentSweepQrCode(MyStandardRequest $request)
    {
        $code = json_decode($request->get('code'), true);

        $user = $request->user();
        $grade = $user->gradeUser;
        if ($grade->grade_id != $code['grade_id']) {
            return JsonBuilder::Error('当前课不上你要上的');
        }
        $timetableItemDao = new TimetableItemDao;
        $item = $timetableItemDao->getCurrentItemByUser($user);

        if (is_null($item)) {
            return JsonBuilder::Error('未找到当前学生要上的的课程');
        }

        $data = [
            'timetable_id' => $item->id,
            'time_slot_name' => $item->timeSlot->name,
            'course_name' => $item->course->name,
            'room' => $item->room->name,
            'is_arrive' => false,
        ];

        // 查询是否已签到
        $schoolId = $user->getSchoolId();
        $schoolDao = new SchoolDao();
        $school = $schoolDao->getSchoolById($schoolId);
        $configuration = $school->configuration;
        $weeks = $configuration->getScheduleWeek(Carbon::now(), null, $code['term']);
        $week = $weeks->getScheduleWeekIndex();
        $detailsDao = new AttendancesDetailsDao();
        $detail = $detailsDao->getDetailByTimeTableIdAndWeekAndStudentId($item->id, $week, $user->id);
        if(!empty($detail) && $detail->mold == AttendancesDetail::MOLD_SIGN_IN) {
            $data['arrive_time'] = $detail->signin_time;
            $data['arrive_type'] = $detail->typeText();
            $data['is_arrive'] = true;
        }

        return  JsonBuilder::Success($data);
    }

    /**
     * --------弃用-----------
     * 教师考勤 -获取当天所有课节
     * @param MyStandardRequest $request
     * @return string
     */
    public function getDayCourse(MyStandardRequest $request)
    {
        $user = $request->user();
        $time = $request->get('time');

        $timeSlotDao = new TimeSlotDao;
        $data =  $timeSlotDao->getAllStudyTimeSlots($user->getSchoolId());
        $result = [];
        if ($data) {
            foreach ($data as $key => $val) {
                $result[$key]['id'] = $val->id;
                $result[$key]['name'] = $val->name;
            }
            array_unshift($result,['id' => 0, 'name' => '全部']);
        }

        return JsonBuilder::Success($result);
    }


    /**
     * 教师考勤- 老师上课统计
     * @param MyStandardRequest $request
     * @return string
     */
    public function getTeacherCourseStatistics(MyStandardRequest $request)
    {

        $user = $request->user();
        $time = Carbon::parse($request->get('time'));
        $year = $request->get('year');

        $timeTableDao = new TimeSlotDao;
        $attendancesDao = new AttendancesDao;
        $timeSlots = $timeTableDao->getAllStudyTimeSlots($user->getSchoolId(), $year);


        $data = [];
        foreach ($timeSlots as $key => $slot) {
            $data[] = [
                'time_slot_id' => $slot->id,
                'name' => $slot->name,
                'sign'=> $attendancesDao->getTeacherSignByTime($slot->id, $time, Attendance::TEACHER_SIGN, Attendance::TEACHER_NO_LATE),
                'no_sign' => $attendancesDao->getTeacherSignByTime($slot->id, $time, Attendance::TEACHER_NO_SIGN),
                'late' => $attendancesDao->getTeacherSignByTime($slot->id, $time, Attendance::TEACHER_SIGN, Attendance::TEACHER_LATE),
            ];
        }

        return JsonBuilder::Success($data);
    }

    /**
     * 教师签到详情
     * @param MyStandardRequest $request
     * @return string
     */
     public function teacherSignDetails(MyStandardRequest $request)
    {
        $time = Carbon::parse($request->get('time'));
        $timeSlotId = $request->get('time_slot_id');
        $type = $request->get('type');

        $attendancesDao = new AttendancesDao;
        if ($type == 2) {
            // 已签到但是迟到
            $type = Attendance::TEACHER_SIGN;
            $data = $attendancesDao->getTeacherSignInfo($timeSlotId, $time, $type, Attendance::TEACHER_LATE);
        } else {
            // 已签到 未签到
            $data = $attendancesDao->getTeacherSignInfo($timeSlotId, $time, $type);
        }

        $result = [];
        foreach ($data as $key => $val) {
            $result[$key]['major'] = '';
            foreach ($val->teacher->organizations as $v) {
                $result[$key]['major'] .= $v->organization->name.' ';
            }
            $result[$key]['avatar'] = $val->teacher->profile->avatar;
            $result[$key]['name'] = $val->teacher->name;
            // 已签到 并且 未迟到
            if ($val->teacher_sign == Attendance::TEACHER_SIGN && $val->teacher_late == Attendance::TEACHER_NO_LATE) {
                $result[$key]['sign_status'] = '正常';
                //已签到 并且 迟到
            }elseif ($val->teacher_sign == Attendance::TEACHER_SIGN && $val->teacher_late == Attendance::TEACHER_LATE) {
                $result[$key]['sign_status'] = '迟到';
            }else {
                $result[$key]['sign_status'] = '未签到';
            }
            $result[$key]['sign_time'] = $val->teacher_sign_time;
        }
        return JsonBuilder::Success($result);
    }




}
