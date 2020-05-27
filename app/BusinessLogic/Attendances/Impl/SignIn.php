<?php
/**
 * Created by PhpStorm.
 * User: liuyang
 * Date: 2020/3/31
 * Time: 上午11:25
 */

namespace App\BusinessLogic\Attendances\Impl;

use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use App\Models\AttendanceSchedules\AttendancesDetail;
use App\Models\AttendanceSchedules\Attendance as AttendanceModel;

class SignIn
{

    public function saveData(AttendanceModel $attendance, AttendancesDetail $attendancesDetail, $type) {
        // 当前状态不等于签到
        if($attendancesDetail->mold != AttendancesDetail::MOLD_SIGN_IN) {
            $key = 'school:'.$attendance->school_id.':time_slot:'.$attendance->time_slot_id.':grade:'.$attendance->grade_id;
            // 更新主表状态
            if($attendancesDetail->mold == AttendancesDetail::MOLD_LEAVE) {
                $key1 = $key.':leave:';
                $field = 'leave_number'; // 请假人数
            } else {
                $field = 'missing_number'; // 旷课人数
                $key1 = $key.':truant:';
            }

            $save = [
                'mold'=>AttendancesDetail::MOLD_SIGN_IN,
                'type'=>$type,
                'signin_time' => Carbon::now(),
                ];
            // 更新详情表状态
            $attendancesDetail->update($save);
            // 删除redis 相应的请假和旷课人数
            Redis::lrem($key1,0,$attendancesDetail->student_id);
            // 把当前用户存到签到文件夹
            $key2 = $key.':sign:';
            Redis::lpush($key2,$attendancesDetail->student_id);
            Redis::expire($key2, 60 * 60); // 过期时间1个小时
            $attendance->increment('actual_number'); //签到人数 +1
            $attendance->decrement($field); // 请假或旷课人数 —1
        }

    }
}
