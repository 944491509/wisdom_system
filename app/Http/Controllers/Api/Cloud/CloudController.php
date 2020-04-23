<?php


namespace App\Http\Controllers\Api\Cloud;

use App\Dao\AttendanceSchedules\AttendancesDao;
use App\Dao\AttendanceSchedules\AttendancesDetailsDao;
use App\Dao\FacilityManage\FacilityDao;
use App\Dao\Schools\GradeResourceDao;
use App\Dao\Students\StudentProfileDao;
use App\Dao\Timetable\TimeSlotDao;
use App\Dao\Timetable\TimetableItemDao;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cloud\CloudRequest;
use App\Models\AttendanceSchedules\AttendancesDetail;
use App\Models\School;
use App\Models\Schools\Facility;
use App\Models\Students\StudentProfile;
use App\Models\Users\UserCodeRecord;
use App\ThirdParty\CloudOpenApi;
use App\Utils\JsonBuilder;
use App\Utils\Time\GradeAndYearUtil;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Illuminate\Http\Request;

class CloudController extends Controller
{

    /**
     * 根据设备码获取学校信息
     * @param CloudRequest $request
     * @return string
     */
    public function getSchoolInfo(CloudRequest $request)
    {
        $code = $request->get('code');

        $dao      = new FacilityDao;
        $facility = $dao->getFacilityByNumber($code);
        if (empty($facility)) {
            return JsonBuilder::Error('设备码错误,或设备已关闭');
        }
        /**
         * @var Facility $facility
         */
        $school = $facility->school;
        $type = '';
        if (!is_null($school->video)) {
             $type = substr($school->video,-3);
        }
        $data   = [
            'school' => [
                'name'  => $school->name,
                'motto' => $school->motto,
                'logo'  => [
                    'path' => $school->logo,
                    'size' => '',
                    'type' => ''
                ],
                'area'  => [
                    'video' => $school->video,
                    'size'  => '',
                    'type'  => $type,
                ],
                'card' => [
                    'address' => $facility->room->building->name.'-'.$facility->room->name,
                ]
            ]
        ];

        return JsonBuilder::Success($data);
    }

    /**
     * 根据设备码获取班级信息
     * @param CloudRequest $request
     * @return string
     */
    public function getGradesInfo(CloudRequest $request)
    {
        $code = $request->get('code');

        $dao      = new FacilityDao;
        $facility = $dao->getFacilityByNumber($code);
        if (empty($facility)) {
            return JsonBuilder::Error('设备码错误,或设备已关闭');
        }

        /**
         * @var  Facility $facility
         */
        $room = $facility->room;

        $timeSlotDao = new TimeSlotDao;

        $item = $timeSlotDao->getItemByRoomForNow($room);

        if (empty($item)) {
            return JsonBuilder::Error('暂无课程');
        }


        $gradeUser = $item->grade->gradeUser;
        $userIds   = $gradeUser->pluck('user_id');

        $studentProfileDao = new  StudentProfileDao;
        $gradeRes = new GradeResourceDao;
        $man   = $studentProfileDao->getStudentGenderTotalByUserId($userIds, StudentProfile::GENDER_MAN);
        $woman = $studentProfileDao->getStudentGenderTotalByUserId($userIds, StudentProfile::GENDER_WOMAN);
        $gradeResource = $gradeRes->getResourceByGradeId($item->grade_id);

        $photo = [];
        foreach ($gradeResource as $key => $value) {
            $photo[$key]['path'] = $value->path;
            $photo[$key]['name'] = $value->name;
            $photo[$key]['type'] = $value->type;
            $photo[$key]['size'] = $value->size;
        }


        $data = [
            'grade'    => [
                'name' => $item->grade->name,
                'teacher' => $item->grade->gradeManager->adviser_name ?? '未设置班主任',
                'monitor' => $item->grade->gradeManager->monitor_name ?? '未设置班长',
            ],
            'number'  => [
                'total' => $man + $woman,
                'man'   => $man,
                'woman' => $woman
            ],
            'photo' => $photo
        ];

        return JsonBuilder::Success($data);
    }

    /**
     * 根据设备码获取课程信息
     * @param CloudRequest $request
     * @return string
     */
    public function getCourseInfo(CloudRequest $request)
    {

        $code     = $request->get('code');
        $dao      = new FacilityDao;
        $facility = $dao->getFacilityByNumber($code);

        if (empty($facility)) {
            return JsonBuilder::Error('设备码错误,或设备已关闭');
        }

        $timeSlotDao = new TimeSlotDao;
        /**
         * 公有班牌
         */
        if ($facility->card_type == Facility::CARD_TYPE_PUBLIC) {
            $room = $facility->room;
            $items = $timeSlotDao->getItemByRoomForNow($room, null,1);
        }
        /**
         *  私有班牌
         */
        elseif ($facility->card_type == Facility::CARD_TYPE_PRIVATE) {
            $grade = $facility->grade;
            $items =  $timeSlotDao->getTimeSlotByGrade($grade);
        }

        if (!$items) {
             return JsonBuilder::Error('现在是休息时间');
        }

        if ($items->isEmpty()) {
            return JsonBuilder::Error('暂无课程');
        }
        $data = [];
        foreach ($items as $key => $item) {
            $data[$key]['course_number']   = $item->timeslot->name;
            $data[$key]['course_time']     = $item->timeslot->from. ' - ' .$item->timeslot->to;
            $data[$key]['course_room']     = $item->room->building->name.' '.$item->room->name;
            $data[$key]['course_teacher'] = $item->teacher->name;
            $data[$key]['course_name']   = $item->course->name;
        }

        return JsonBuilder::Success($data);
    }


    /**
     * 生成签到二维码
     * @param CloudRequest $request
     * @return string
     * @throws \Endroid\QrCode\Exception\InvalidPathException
     */
    public function getQrCode(CloudRequest $request)
    {

        $code     = $request->get('code');
        $dao      = new FacilityDao;
        $facility = $dao->getFacilityByNumber($code);
        if (empty($facility)) {
            return JsonBuilder::Error('设备码错误,或设备已关闭');
        }
        /**
         * @var  Facility $facility
         */
        $room = $facility->room;
        $timeSlotDao = new TimeSlotDao;

        $item = $timeSlotDao->getItemByRoomForNow($room);
        if (empty($item)) {
            return JsonBuilder::Error('暂无课程');
        }

        // 二维码生成规则 二维码标识, 学校ID, 班级ID, 教师ID ....
        $codeStr = base64_encode(json_encode([
            'app' => UserCodeRecord::IDENTIFICATION_CLOUD,
            'school_id' => $item->school_id,
            'grade_id' => $item->grade_id,
            'teacher_id' => $item->teacher_id,
            'timetable_id' => $item->id,
            'course_id' => $item->course_id,
            'term' => $item->term,
            'time' => time()
        ]));
        $qrCode = new QrCode($codeStr);
        $qrCode->setSize(400);
        $qrCode->setLogoPath(public_path('assets/img/logo.png'));
        $qrCode->setLogoSize(60, 60);
        $code = 'data:image/png;base64,' . base64_encode($qrCode->writeString());

        return JsonBuilder::Success(['code' => $code, 'status' => true],'签到二维码');
    }

    /**
     * 签到统计
     * @param CloudRequest $request
     * @return string
     * @throws \Exception
     */
    public function getAttendanceStatistic(CloudRequest $request)
    {
        $code     = $request->get('code');
        $dao      = new FacilityDao;
        $facility = $dao->getFacilityByNumber($code);
        if (empty($facility)) {
            return JsonBuilder::Error('设备码错误,或设备已关闭');
        }
        /**
         * @var  Facility $facility
         */
        $room = $facility->room;

        $timeSlotDao = new TimeSlotDao;

        $item = $timeSlotDao->getItemByRoomForNow($room);
        if (empty($item)) {
            return JsonBuilder::Error('暂无课程');
        }

        /**
         * @var School $school
         */
        $configuration = $item->school->configuration;
        $now = Carbon::now(GradeAndYearUtil::TIMEZONE_CN);
        $month = Carbon::parse($now)->month;
        $term = $configuration->guessTerm($month);
        $weeks = $configuration->getScheduleWeek($now, null, $term);
        if (is_null($weeks)) {
              return JsonBuilder::Error('暂无课程');
        }

        $week = $weeks->getScheduleWeekIndex();

        $dao = new AttendancesDao;
        $attendanceInfo = $dao->isAttendanceByTimetableAndWeek($item, $week);

        $data = [
            'sign'    => $attendanceInfo->actual_number,
            'no_sign' => $attendanceInfo->missing_number,
            'leave'   => $attendanceInfo->leave_number
        ];

        return JsonBuilder::Success($data);
    }

    /**
     * 接收华三考勤数据
     * @param CloudRequest $request
     * @return string
     * @throws \Exception
     */
    public function  distinguish(CloudRequest $request)
    {
        $faceCode = $request->get('face_code');
        $dao = new  StudentProfileDao;

        $student = $dao->getStudentInfoByUserFaceCode($faceCode);
        if (empty($student)) {
            return JsonBuilder::Error('未找到学生');
        }

        $timetableItemDao = new TimetableItemDao;
        $item = $timetableItemDao->getCurrentItemByUser($student->user);
        if (empty($item)) {
            return JsonBuilder::Error('未找到该同学目前上的课程');
        }

        if ($item->grade_id != $student->user->gradeUser->grade_id) {
            return JsonBuilder::Error('该学生不应该上这个课程');
        }

        $attendancesDetailsDao = new AttendancesDetailsDao;
        $attendancesDetail = $attendancesDetailsDao->getDetailByTimeTableIdAndStudentId($item, $student->user);
        if ($attendancesDetail) {
            return JsonBuilder::Error('学生已经'. $attendancesDetail->typeText() .'了');
        }

        $dao = new AttendancesDao;
        $attendanceInfo = $dao->arrive($item, $student->user, AttendancesDetail::TYPE_INTELLIGENCE);
        if($attendanceInfo) {
            return  JsonBuilder::Success('签到成功');
        } else {
            return  JsonBuilder::Error('服务器错误, 签到失败');
        }
    }


    /**
     * 上传云班牌人脸识别照片
     * @param Request $request
     * @return string
     */
    public function uploadFaceImage(Request $request)
    {
        $user = $request->user();

        $file = $request->file('face_image');

        $openApi = new CloudOpenApi;
        $result = $openApi->makePostUploadFaceImg($user->profile->uuid, $file);
        if ($result['code'] != CloudOpenApi::ERROR_CODE_OPEN_API_OK) {
            return JsonBuilder::Error('服务器出错了');
        }

        $studentProfileDao  = new StudentProfileDao;
        $update = $studentProfileDao->updateStudentProfile($user->id, ['face_code' => $result['data']['face_code']]);

        if ($update) {
            return  JsonBuilder::Success('上传成功');
        } else {
            return  JsonBuilder::Error('上传失败');
        }
    }

    /**
     * 手动扫云班牌码签到
     * @param Request $request
     * @return string
     * @throws \Exception
     */
    public function manual(Request $request)
    {
        $user = $request->user();
        $timetableItemDao = new TimetableItemDao;
        $item = $timetableItemDao->getCurrentItemByUser($user);

        if (empty($item)) {
            return JsonBuilder::Error('未找到该同学目前上的课程');
        }
        if ($item->grade_id != $user->gradeUser->grade_id) {
            return JsonBuilder::Error('该学生不应该上这个课程');
        }

        $attendancesDetailsDao = new AttendancesDetailsDao;
        $attendancesDetail = $attendancesDetailsDao->getDetailByTimeTableIdAndStudentId($item, $user);
        if ($attendancesDetail) {
            return JsonBuilder::Error('学生已经'. $attendancesDetail->typeText() .'了');
        }

        $dao = new AttendancesDao;
        $attendanceInfo = $dao->arrive($item, $user, AttendancesDetail::TYPE_SWEEP_CODE);
        if($attendanceInfo) {
            return  JsonBuilder::Success('签到成功');
        } else {
            return  JsonBuilder::Error('服务器错误, 签到失败');
        }
    }

}
