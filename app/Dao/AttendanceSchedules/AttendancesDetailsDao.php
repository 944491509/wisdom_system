<?php

namespace App\Dao\AttendanceSchedules;


use Carbon\Carbon;
use App\Utils\JsonBuilder;
use App\Dao\Schools\SchoolDao;
use Illuminate\Support\Facades\DB;
use App\Utils\ReturnData\MessageBag;
use App\Utils\Time\GradeAndYearUtil;
use App\Utils\Misc\ConfigurationTool;
use App\Models\AttendanceSchedules\Attendance;
use App\Models\AttendanceSchedules\AttendancesDetail;

class AttendancesDetailsDao
{
    /**
     * 统计签到次数
     * @param $userId
     * @param $year
     * @param $term
     * @param $courseId null
     * @return mixed
     */
    public function getSignInCountByUser($userId, $year, $term, $courseId = null) {
        $map = ['student_id'=>$userId, 'year'=>$year, 'term'=>$term, 'mold'=>AttendancesDetail::MOLD_SIGN_IN];
        if(!is_null($courseId)) {
            $map['course_id'] = $courseId;
        }
        return AttendancesDetail::where($map)->count();
    }


    /**
     * 统计请假次数
     * @param $userId
     * @param $year
     * @param $term
     * @param $courseId null
     * @return mixed
     */
    public function getLeaveCountByUser($userId, $year, $term, $courseId = null) {

        $map = ['student_id'=>$userId, 'year'=>$year, 'term'=>$term,
            'mold'=>AttendancesDetail::MOLD_LEAVE];
        if(!is_null($courseId)) {
            $map['course_id'] = $courseId;
        }
        return AttendancesDetail::where($map)->count();
    }

    /**
     * 统计旷课次数
     * @param $userId
     * @param $courseId
     * @param $year
     * @param $term
     * @return mixed
     */
    public function getTruantCountByUser($userId, $year, $term, $courseId = null) {
        $map = ['student_id'=>$userId, 'year'=>$year, 'term'=>$term,
            'mold'=>AttendancesDetail::MOLD_TRUANT];
        if(!is_null($courseId)) {
            $map['course_id'] = $courseId;
        }
        return AttendancesDetail::where($map)->count();
    }

    /**
     * 获取签到详情
     * @param $item
     * @param $user
     * @return AttendancesDetail
     */
    public function getDetailByTimeTableIdAndStudentId($item, $user)
    {
        $schoolDao = new SchoolDao;
        $school = $schoolDao->getSchoolById($user->getSchoolId());
        $configuration = $school->configuration;
        $now = Carbon::now(GradeAndYearUtil::TIMEZONE_CN);

        $month = Carbon::parse($now)->month;
        $term = $configuration->guessTerm($month);
        $weeks = $configuration->getScheduleWeek($now, null, $term);
        $week = $weeks->getScheduleWeekIndex();
        $where = [
            ['timetable_id','=',$item->id],
            ['year','=', $item->year],
            ['term','=',$item->term],
            ['student_id','=',$user->id],
            ['weekday_index','=', $item->weekday_index],
            ['week' ,'=', $week],
            ['mold', '=', AttendancesDetail::MOLD_SIGN_IN]
        ];
        return AttendancesDetail::where($where)->first();
    }

    /**
     * 签到详情添加
     * @param $data
     * @return mixed
     */
    public function add($data)
    {
        return AttendancesDetail::create($data);
    }


    /**
     * 编辑签到详情
     * @param $detailId
     * @param $data
     * @return mixed
     */
    public function update($detailId, $data) {
        return AttendancesDetail::where('id', $detailId)->update($data);
    }

    /**
     * 课程签到列表
     * @param $year
     * @param $userId
     * @param $courseId
     * @param $term
     * @return mixed
     */
    public function signInList($year, $userId, $courseId, $term) {
        $field = ['timetable_id', 'mold', 'created_at'];
        $map = ['year'=>$year, 'student_id'=>$userId, 'course_id'=>$courseId, 'term'=>$term];
        return AttendancesDetail::where($map)
            ->orderBy('created_at')
            ->select($field)
            ->get();
    }

    /**
     * 查寻记录
     * @param $userId
     * @param $timetableId
     * @return mixed
     */
    public function getDetailByUserId($userId,$timetableId) {
        $map = ['student_id'=>$userId, 'attendance_id'=>$timetableId];
        return AttendancesDetail::where($map)->first();
    }

    /**
     * @param $attendanceId
     * @return mixed
     */
    public function getAttendDetailsByAttendanceId($attendanceId) {
        return AttendancesDetail::where('attendance_id', $attendanceId)->get();
    }


    /**
     * @param $attendanceId
     * @return mixed
     */
    public function getDetailsPageByAttendanceId($attendanceId) {
        return AttendancesDetail::where('attendance_id', $attendanceId)
            ->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);
    }


    /**
     * 保存签到详情
     * @param $attendanceId
     * @param $details
     * @return MessageBag
     */
    public function saveDetails($attendanceId, $details) {

        $messageBag = new MessageBag();
        $info = Attendance::find($attendanceId);
        try{
            DB::beginTransaction();
            // 先查询该学生的记录是否存在
            foreach ($details as $key => $item) {
                $map = ['attendance_id'=>$attendanceId, 'student_id'=>$item['user_id']];
                $re = AttendancesDetail::where($map)->first();
                if(is_null($re)) {
                    // 添加
                    $add = [
                        'attendance_id'=>$attendanceId,
                        'course_id'=>$info['course_id'],
                        'timetable_id'=>$info['timetable_id'],
                        'student_id'=>$item['user_id'],
                        'year'=>$info['year'],
                        'term'=>$info['term'],
                        'type'=>AttendancesDetail::TYPE_MANUAL,
                        'week'=>$info['week'],
                        'mold'=>$item['mold'],
                        'weekday_index'=>$info->timeTable->weekday_index,
                        'grade_id' => $info->grade_id
                        ];
                    if($item['mold'] == AttendancesDetail::MOLD_SIGN_IN) {
                        $add['signin_time'] = Carbon::now();
                    }
                    AttendancesDetail::create($add);

                } else {
                    // 编辑
                    $save = ['mold'=>$item['mold']];
                    if($item['mold'] == AttendancesDetail::MOLD_SIGN_IN) {
                        $save['signin_time'] = Carbon::now();
                    }
                    AttendancesDetail::where($map)->update($save);
                }
                // 修改主表

                $list = AttendancesDetail::where('attendance_id',$attendanceId)->get();
                $mold = $list->pluck('mold')->toArray();
                $count = array_count_values($mold);
                $signIn = $count[AttendancesDetail::MOLD_SIGN_IN] ?? 0;  // 签到人数
                $leave = $count[AttendancesDetail::MOLD_LEAVE] ?? 0;  // 请假人数
                $save = [
                    'actual_number'=> $signIn,
                    'leave_number'=> $leave,
                    'missing_number'=> $info['total_number'] - $signIn - $leave
                ];
                Attendance::where(['id'=>$attendanceId])->update($save);
            }
            DB::commit();
            $messageBag->setMessage('保存成功');

        } catch (\Exception $e) {
            DB::rollBack();
            $msg = $e->getMessage();
            $messageBag->setCode(JsonBuilder::CODE_ERROR);
            $messageBag->setMessage('保存失败'.$msg);
        }
        return $messageBag;
    }


    /**
     * 保存评分
     * @param $attendanceId
     * @param $score
     * @return MessageBag
     */
    public function saveScore($attendanceId, $score) {

        $messageBag = new MessageBag();
        $info = Attendance::find($attendanceId);
        if(empty($info)) {
            $messageBag->setCode(JsonBuilder::CODE_ERROR);
            $messageBag->setMessage('该签到信息不存在');
            return $messageBag;
        }
        try{
            DB::beginTransaction();
            foreach ($score as $key => $item) {
                $map = ['attendance_id'=>$attendanceId, 'student_id'=>$item['user_id']];
                $re = AttendancesDetail::where($map)->first();
                if(is_null($re)) {
                    // 添加
                    $add = [
                        'attendance_id' => $attendanceId,
                        'course_id' => $info['course_id'],
                        'timetable_id' => $info['timetable_id'],
                        'student_id' => $item['user_id'],
                        'year' => $info['year'],
                        'term' => $info['term'],
                        'type' => AttendancesDetail::TYPE_MANUAL,
                        'week' => $info['week'],
                        'mold' => AttendancesDetail::MOLD_TRUANT,
                        'score'=>$item['score'],
                        'weekday_index' => $info->timeTable->weekday_index,
                    ];
                    if(!empty($item['remark'])) {
                        $add['remark'] = $item['remark'];
                    }
                    AttendancesDetail::create($add);
                } else {
                    $save = ['score'=>$item['score']];
                    if(!empty($item['remark'])) {
                        $save['remark'] = $item['remark'];
                    }
                    AttendancesDetail::where($map)->update($save);
                }
                // 修改主表评分状态
                $update = ['status'=>Attendance::STATUS_EVALUATE];
                Attendance::where(['id'=>$attendanceId])->update($update);
            }

            DB::commit();

            $messageBag->setMessage('保存成功');

        } catch (\Exception $e) {
            DB::rollBack();
            $msg = $e->getMessage();
            $messageBag->setMessage($msg);
            $messageBag->setCode(JsonBuilder::CODE_ERROR);
        }
        return $messageBag;
    }


    /**
     * 根据学年学期查询用户的签到详情
     * @param $userId
     * @param $year
     * @param $term
     * @return mixed
     */
    public function getSignInByYearTerm($userId, $year, $term) {
        $map = ['student_id'=>$userId, 'year'=>$year, 'term'=>$term];
        return AttendancesDetail::where($map)->get();
    }


    /**
     * 获取备注
     * @param $userId
     * @param $courseId
     * @param $year
     * @param $term
     * @return mixed
     */
    public function getRemarkList($userId, $courseId, $year, $term) {
        $map = ['student_id'=>$userId, 'year'=>$year, 'term'=>$term, 'course_id'=>$courseId];
        return AttendancesDetail::where($map)
            ->whereNotNull('remark')
            ->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);
    }


    /**
     * 根须课程表ID、学周、用户查询签到详情
     * @param $timeTableId
     * @param $week
     * @param $studentId
     * @return mixed
     */
    public function getDetailByTimeTableIdAndWeekAndStudentId($timeTableId, $week, $studentId) {
        $map = [
            'timetable_id'=>$timeTableId,
            'week'=>$week,
            'student_id'=>$studentId
        ];

        return AttendancesDetail::where($map)->first();
    }


    /**
     * 获取用户的签到详情列表
     * @param $userId
     * @param $year
     * @param $term
     * @return mixed
     */
    public function getPageSignDetailByYearAndTerm($userId, $year, $term) {
        $map = [
            'student_id' => $userId,
            'year' => $year,
            'term' => $term
        ];
        return AttendancesDetail::where($map)
            ->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);
    }


}
