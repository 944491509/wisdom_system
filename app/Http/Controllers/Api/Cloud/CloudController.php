<?php


namespace App\Http\Controllers\Api\Cloud;

use App\User;
use Exception;
use App\Models\Acl\Role;
use Endroid\QrCode\QrCode;
use App\Utils\JsonBuilder;
use Illuminate\Http\Request;
use App\Models\Schools\Facility;
use App\ThirdParty\CloudOpenApi;
use App\Dao\Timetable\TimeSlotDao;
use App\Http\Controllers\Controller;
use App\Models\Users\UserCodeRecord;
use Illuminate\Support\Facades\Redis;
use App\Dao\Schools\GradeResourceDao;
use App\Dao\FacilityManage\FacilityDao;
use App\Dao\Students\StudentProfileDao;
use App\Dao\Timetable\TimetableItemDao;
use App\Models\Students\StudentProfile;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Cloud\CloudRequest;
use App\Dao\AttendanceSchedules\AttendancesDao;
use Endroid\QrCode\Exception\InvalidPathException;
use App\Models\AttendanceSchedules\AttendancesDetail;
use App\Dao\AttendanceSchedules\AttendancesDetailsDao;

class CloudController extends Controller
{

    /**
     * 根据设备码获取学校信息
     * @param CloudRequest $request
     * @return string
     */
    public function getSchoolInfo(CloudRequest $request)
    {
        $code     = $request->get('code');
        $dao      = new FacilityDao;
        $facility = $dao->getFacilityByNumber($code);
        if (empty($facility)) {
            return JsonBuilder::Error('设备码错误,或设备已关闭');
        }
        $school = $facility->school;
        $res    = Redis::get('school:' . $school->id . ':info:' . $code);
        if (is_null($res)) {
            /**
             * @var Facility $facility
             */
            $type = '';
            if (!is_null($school->video)) {
                $type = substr($school->video, -3);
            }
            $data = [
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
                    'card'  => [
                        'address' => $facility->room->building->name . '-' . $facility->room->name,
                    ]
                ]
            ];
            // 生存时间 暂时60s
            Redis::setex('school:' . $school->id . ':info:' . $code, 60 * 10, json_encode($data));
        } else {
            $data = json_decode($res, true);
        }

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

        $res = Redis::get('grade:' . 'code_' . $code);
        if (is_null($res)) {
            $room        = $facility->room;
            $timeSlotDao = new TimeSlotDao;
            /**
             * 公有班牌
             */
            if ($facility->card_type == Facility::CARD_TYPE_PUBLIC) {
                $item = $timeSlotDao->getItemByRoomForNow($room);

                if (empty($item)) {
                    return JsonBuilder::Error('暂无课程');
                } else {
                    $grade = $item->grade;
                }
            } /**
             *  私有班牌
             */
            elseif ($facility->card_type == Facility::CARD_TYPE_PRIVATE) {
                $grade = $facility->grade;
            }

            $gradeUser = $grade->gradeUser->where('user_type', Role::VERIFIED_USER_STUDENT);
            $userIds   = $gradeUser->pluck('user_id');

            $studentProfileDao = new  StudentProfileDao;
            $gradeRes          = new GradeResourceDao;
            $man               = $studentProfileDao->getStudentGenderTotalByUserId($userIds, StudentProfile::GENDER_MAN);
            $woman             = $studentProfileDao->getStudentGenderTotalByUserId($userIds, StudentProfile::GENDER_WOMAN);
            $gradeResource     = $gradeRes->getResourceByGradeId($grade->id);

            $photo = [];
            foreach ($gradeResource as $key => $value) {
                $photo[$key]['path'] = $value->path;
                $photo[$key]['name'] = $value->name;
                $photo[$key]['type'] = $value->type;
                $photo[$key]['size'] = $value->size;
            }


            $data = [
                'grade'  => [
                    'name'    => $grade->name,
                    'teacher' => $grade->gradeManager->adviser_name ?? '未设置班主任',
                    'monitor' => $grade->gradeManager->monitor_name ?? '未设置班长',
                ],
                'number' => [
                    'total' => $man + $woman,
                    'man'   => $man,
                    'woman' => $woman
                ],
                'photo'  => $photo
            ];
            // 默认 60s
            Redis::setex('grade:' . 'code_' . $code, 60 * 10, json_encode($data));

        } else {
            $data = json_decode($res, true);
        }

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
        $res = Redis::get('course:code_' . $code);
        if (is_null($res)) {
            $timeSlotDao = new TimeSlotDao;
            /**
             * 公有班牌
             */
            if ($facility->card_type == Facility::CARD_TYPE_PUBLIC) {
                $room  = $facility->room;
                $items = $timeSlotDao->getItemByRoomForNow($room, null, 1);
            } /**
             *  私有班牌
             */
            elseif ($facility->card_type == Facility::CARD_TYPE_PRIVATE) {
                $grade = $facility->grade;
                $items = $timeSlotDao->getTimeSlotByGrade($grade);
            }

            if (!$items) {
                return JsonBuilder::Error('现在是休息时间');
            }

            if ($items->isEmpty()) {
                return JsonBuilder::Error('暂无课程');
            }
            $data = [];
            foreach ($items as $key => $item) {
                $data[$key]['course_number']  = $item->timeslot->name;
                $data[$key]['course_time']    = $item->timeslot->from . ' - ' . $item->timeslot->to;
                $data[$key]['course_room']    = $item->room->building->name . ' ' . $item->room->name;
                $data[$key]['course_teacher'] = $item->teacher->name;
                $data[$key]['course_name']    = $item->course->name ?? '';
            }

            // 默认 60s
            Redis::setex('course:code_' . $code, 60 * 10, json_encode($data));
        } else {
            $data = json_decode($res, true);
        }
        return JsonBuilder::Success($data);
    }


    /**
     * 生成签到二维码
     * @param CloudRequest $request
     * @return string
     * @throws InvalidPathException
     */
    public function getQrCode(CloudRequest $request)
    {

        $code     = $request->get('code');
        $dao      = new FacilityDao;
        $facility = $dao->getFacilityByNumber($code);
        if (empty($facility)) {
            return JsonBuilder::Error('设备码错误,或设备已关闭');
        }
        $res = Redis::get('qrcode:' . $code);
        if (is_null($res)) {
            /**
             * @var  Facility $facility
             */
            $room        = $facility->room;
            $timeSlotDao = new TimeSlotDao;

            $item = $timeSlotDao->getItemByRoomForNow($room);
            if (empty($item)) {
                return JsonBuilder::Error('暂无课程');
            }

            // 二维码生成规则 二维码标识, 学校ID, 班级ID, 教师ID ....
            $codeStr = base64_encode(json_encode([
                'app'          => UserCodeRecord::IDENTIFICATION_CLOUD,
                'school_id'    => $item->school_id,
                'grade_id'     => $item->grade_id,
                'teacher_id'   => $item->teacher_id,
                'timetable_id' => $item->id,
                'course_id'    => $item->course_id,
                'term'         => $item->term,
                'time'         => time()
            ]));
            $qrCode  = new QrCode($codeStr);
            $qrCode->setSize(400);
            $qrCode->setLogoPath(public_path('assets/img/logo.png'));
            $qrCode->setLogoSize(60, 60);
            $str  = 'data:image/png;base64,' . base64_encode($qrCode->writeString());
            $data = ['code' => $str, 'status' => true];
            // 默认 60s
            Redis::setex('qrcode:' . $code, 60 * 10, json_encode($data));
        } else {
            $data = json_decode($res, true);
        }
        return JsonBuilder::Success($data, '签到二维码');
    }

    /**
     * 签到统计
     * @param CloudRequest $request
     * @return string
     * @throws Exception
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
        $room        = $facility->room;
        $timeSlotDao = new TimeSlotDao;

        $item = $timeSlotDao->getItemByRoomForNow($room);
        if (empty($item)) {
            return JsonBuilder::Error('暂无课程');
        }

        $key  = 'school:' . $item->school_id . ':time_slot:' . $item->time_slot_id . ':grade:' . $item->grade_id;
        $sign = Redis::lrange($key . ':sign:', 0, -1);

        $no_sign = Redis::lrange($key . ':truant:', 0, -1);
        $leave   = Redis::lrange($key . ':leave:', 0, -1);

        $data = [
            'sign'    => count($sign),
            'no_sign' => count($no_sign),
            'leave'   => count($leave)
        ];

        return JsonBuilder::Success($data);
    }

    /**
     * 接收华三考勤数据
     * @param CloudRequest $request
     * @return string
     * @throws Exception
     */
    public function distinguish(CloudRequest $request)
    {
        $faceCode = $request->get('face_code');
        $dao      = new  StudentProfileDao;

        $student = $dao->getStudentInfoByUserFaceCode($faceCode);
        if (empty($student)) {
            return JsonBuilder::Error('未找到学生');
        }
        $schoolId = $student->user->getSchoolId();

        $Attendance = new AttendancesDao;
        $isRest = $Attendance->isWantSign($schoolId);
        if($isRest) {
            return JsonBuilder::Error('当天时间是休息时间,不需要签到');
        }

        $timetableItemDao = new TimetableItemDao;
        $item             = $timetableItemDao->getCurrentItemByUser($student->user);
        if (empty($item)) {
            return JsonBuilder::Error('未找到该同学目前上的课程');
        }

        if ($item->grade_id != $student->user->gradeUser->grade_id) {
            return JsonBuilder::Error('该学生不应该上这个课程');
        }

        $attendancesDetailsDao = new AttendancesDetailsDao;
        $attendancesDetail     = $attendancesDetailsDao->getDetailByTimeTableIdAndStudentId($item, $student->user);
        if ($attendancesDetail) {
            return JsonBuilder::Error('学生已经' . $attendancesDetail->typeText() . '了');
        }

        $dao            = new AttendancesDao;
        $attendanceInfo = $dao->arrive($item, $student->user, AttendancesDetail::TYPE_INTELLIGENCE);
        if ($attendanceInfo) {
            return JsonBuilder::Success('签到成功');
        } else {
            return JsonBuilder::Error('服务器错误, 签到失败');
        }
    }

    /**
     * 手动扫云班牌码签到
     * @param Request $request
     * @return string
     * @throws Exception
     */
    public function manual(Request $request)
    {
        $user = $request->user();
        $schoolId = $user->getSchoolId();
        $dao = new AttendancesDao;
        $isRest = $dao->isWantSign($schoolId);
        if($isRest) {
            return JsonBuilder::Error('当天时间是休息时间,不需要签到');
        }
        $timetableItemDao = new TimetableItemDao;
        $item             = $timetableItemDao->getCurrentItemByUser($user);

        if (empty($item)) {
            return JsonBuilder::Error('未找到该同学目前上的课程');
        }
        if ($item->grade_id != $user->gradeUser->grade_id) {
            return JsonBuilder::Error('该学生不应该上这个课程');
        }

        $attendancesDetailsDao = new AttendancesDetailsDao;
        $attendancesDetail     = $attendancesDetailsDao->getDetailByTimeTableIdAndStudentId($item, $user);
        if ($attendancesDetail) {
            return JsonBuilder::Error('学生已经' . $attendancesDetail->typeText() . '了');
        }


        $attendanceInfo = $dao->arrive($item, $user, AttendancesDetail::TYPE_SWEEP_CODE);
        if ($attendanceInfo) {
            return JsonBuilder::Success('签到成功');
        } else {
            return JsonBuilder::Error('服务器错误, 签到失败');
        }
    }


    /**
     * 上传云班牌人脸识别照片
     * @param Request $request
     * @return string
     */
    public function uploadFaceImage(Request $request)
    {
        $user = User::find($request->get('user_id'));
        $type = $request->get('type');

        $gradeName        = $user->gradeUser->grade->name;
        $gradePath        = storage_path('app/public/student_photo/' . $gradeName);
        $studentPhotoPath = $gradePath . '/' . $user->name . '.jpg';
        if ($type == CloudOpenApi::UPDATE_STUDENT_PHOTO) { // 更新
            if (!is_file($studentPhotoPath)) {
                return JsonBuilder::Error('更新 未找到之前的照片');
            } else {
                // 删除之前的照片
                Storage::disk('student_photo')->delete($gradeName . '/' . $user->name . '.jpg');
            }
        }

        $file = $request->file('face_image')->storeAs('public/student_photo/' . $gradeName, $user->name . '.jpg');

        if (!$file) {
            return JsonBuilder::Error('上传失败');
        }

        $openApi = new CloudOpenApi;
        if ($type == CloudOpenApi::UPDATE_STUDENT_PHOTO) {
            $result = $openApi->makePostUploadFaceImg($studentPhotoPath, $user->profile->face_code);
        } else {
            $result = $openApi->makePostUploadFaceImg($studentPhotoPath);
        }
        if ($result['code'] != CloudOpenApi::ERROR_CODE_OPEN_API_OK) {
            return JsonBuilder::Error('华三服务器出错了');
        }
        $studentProfileDao = new StudentProfileDao;
        $update            = $studentProfileDao->updateStudentProfile($user->id, ['face_code' => $result['data']['face_code']]);

        if ($update) {
            return JsonBuilder::Success('上传成功');
        } else {
            return JsonBuilder::Error('上传失败');
        }
    }
}
