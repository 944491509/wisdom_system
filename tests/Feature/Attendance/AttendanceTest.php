<?php
/**
 * Created by PhpStorm.
 * User: liuyang
 * Date: 2020/4/11
 * Time: 上午10:15
 */

namespace Tests\Feature\Attendance;


use App\Models\AttendanceSchedules\Attendance;
use Tests\Feature\BasicPageTestCase;
use App\Models\AttendanceSchedules\AttendancesDetail;

class AttendanceTest extends BasicPageTestCase
{

    // 同步grade_id数据
    public function testUpdateDetailsGradeId() {
        $details = AttendancesDetail::where('grade_id',0)->get();
        foreach ($details as $key => $item) {
            $attendance = Attendance::where('id', $item->attendance_id)->first();
            $item->update(['grade_id'=>$attendance->grade_id]);
        }

    }
}