<?php


namespace App\Dao\AttendanceSchedules;


use App\Dao\BuildFillableData;
use App\Models\AttendanceSchedules\AttendancePerson;
use App\Models\AttendanceSchedules\AttendanceSchedule;
use App\Models\AttendanceSchedules\AttendanceTask;
use App\Models\AttendanceSchedules\AttendanceTimeSlot;
use App\Utils\JsonBuilder;
use App\Utils\ReturnData\MessageBag;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceSchedulesDao
{
    use BuildFillableData;
    public function __construct()
    {
    }

    public function createTask($data)
    {
        if (!isset($data['id']) || empty($data['id'])) {
            unset($data['id']);
        }
        $messageBag = new MessageBag(JsonBuilder::CODE_ERROR);
        DB::beginTransaction();
        try {
            $fillableData = $this->getFillableData(new AttendanceTask(), $data);
            $task = AttendanceTask::create($fillableData);
            if ($task) {
                DB::commit();
                $messageBag->setCode(JsonBuilder::CODE_SUCCESS);
                $messageBag->setData($task);
            } else {
                DB::rollBack();
                $messageBag->setMessage('保存值周信息失败, 请联系管理员');
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $messageBag->setMessage($exception->getMessage());
        }
        return $messageBag;

    }
    public function updateTask($data)
    {
        $id = $data['id'];
        unset($data['id']);
        $messageBag = new MessageBag(JsonBuilder::CODE_ERROR);
        DB::beginTransaction();
        try {
            $fillableData = $this->getFillableData(new AttendanceTask(), $data);
            $task = AttendanceTask::where('id', $id)->update($fillableData);
            if ($task) {
                DB::commit();
                $messageBag->setCode(JsonBuilder::CODE_SUCCESS);
                $messageBag->setData($task);
            } else {
                DB::rollBack();
                $messageBag->setMessage('更新值周信息失败, 请联系管理员');
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $messageBag->setMessage($exception->getMessage());
        }
        return $messageBag;
    }

    /**
     * 创建一组默认的时间槽
     * @param $taskId
     * @return bool
     */
    public function addDefaultTimeSlotsForTask($taskId)
    {
        $data = [
            ['attendance_id' => $taskId, 'title' => '上午', 'start_time' => '08:00:00', 'end_time' => '12:00:00'],
            ['attendance_id' => $taskId, 'title' => '中午', 'start_time' => '12:00:00', 'end_time' => '14:00:00'],
            ['attendance_id' => $taskId, 'title' => '下午', 'start_time' => '14:00:00', 'end_time' => '18:00:00'],
            ['attendance_id' => $taskId, 'title' => '晚上', 'start_time' => '18:00:00', 'end_time' => '22:00:00'],
        ];
        DB::beginTransaction();
        try {
            foreach ($data as $item) {
                self::addTimeSlotsForTask($item);
            }
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }

    }

    /**
     * @param $taskId
     * @param $data
     * @return bool
     */
    public function addTimeSlotsForTask($data)
    {
        DB::beginTransaction();
        try {
            $fillableData = $this->getFillableData(new AttendanceTimeSlot(), $data);
            AttendanceTimeSlot::create($fillableData);

            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }
    public function updateTimeSlotsForTask($data)
    {
        $id = $data['id'];
        unset($data['id']);
        DB::beginTransaction();
        try {
            $fillableData = $this->getFillableData(new AttendanceTimeSlot(), $data);
            AttendanceTimeSlot::where('id', $id)->update($fillableData);
            DB::commit();
            return true;
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }
    public function deleteTimeSlots($schoolId, $timeSlotId)
    {
        return AttendanceTimeSlot::where('id', $timeSlotId)
            ->where('school_id', $schoolId)->delete();
    }

    /**
     * 添加值周人员信息
     * @param $data
     * @return bool
     */
    public function addPerson($data)
    {
        DB::beginTransaction();
        try {
            $fillableData = $this->getFillableData(new AttendancePerson(), $data);
            $person = AttendancePerson::create($fillableData);
            if ($person)
            {
                DB::commit();
                return true;
            } else {
                DB::rollBack();
                return false;
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            return false;
        }
    }
    public function delPerson($schoolId, $personId)
    {
        return AttendancePerson::where('id', $personId)
            ->where('school_id', $schoolId)->delete();
    }

    /**
     * 添加值周计划
     * @param $data
     * @return MessageBag
     */
    public function addSchedule($data)
    {
        if (!isset($data['id']) || empty($data['id'])) {
            unset($data['id']);
        }
        $messageBag = new MessageBag(JsonBuilder::CODE_ERROR);
        DB::beginTransaction();
        try {
            $fillableData = $this->getFillableData(new AttendanceSchedule(), $data);
            $schedule = AttendanceSchedule::create($fillableData);
            if ($schedule) {
                DB::commit();
                $messageBag->setCode(JsonBuilder::CODE_SUCCESS);
                $messageBag->setData($schedule);
            } else {
                DB::rollBack();
                $messageBag->setMessage('保存值周计划信息失败, 请联系管理员');
            }
        } catch (\Exception $exception) {
            DB::rollBack();
            $messageBag->setMessage($exception->getMessage());
        }
        return $messageBag;
    }

    /**
     * 获取某个学校的全部值周任务，按时间段获取，例如：
     * 当current==0 && cycle==week时表示获取本周数据
     * 当current==1 && cycle==week时表示获取下周数据
     * cycle可以为week和month
     * @param $schoolId
     * @param $type
     * @param string $cycle
     * @param int $current
     * @return mixed
     */
    public function getAllTaskForSchool($schoolId, $type, $cycle='week', $current=0)
    {
        $timeArr = self::getTimes($current, $cycle);

        $startTime = $timeArr[0];
        $endTime   = $timeArr[1];
        $result = AttendanceTask::where('start_time','>=', $startTime)
            ->where('end_time', '<=', $endTime)
            ->where('school_id', $schoolId)
            ->get();
        return $result;


    }

    /**
     * 获取某个值周任务在某段时间内的所有记录
     * @param $schoolId
     * @param $taskId
     * @param string $cycle
     * @param int $current
     * @return mixed
     */
    public function getSomeoneTaskScheduleForSchool($schoolId, $taskId, $cycle='week', $current=0)
    {
        $timeArr = self::getTimes($current, $cycle);

        $startTime = $timeArr[0];
        $endTime   = $timeArr[1];
        $result = AttendanceSchedule::where('start_time','>=', $startTime)
            ->where('end_time', '<=', $endTime)
            ->where('school_id', $schoolId)
            ->get();
        return $result;
    }

    /**
     *
     * @param $current
     * @param string $cycle
     * @return array|bool
     */
    public function getTimes($current, $cycle='week')
    {
        if ($cycle == 'week') {
            if ($current == 0) {
                $startStr = 'this week';
                $endStr = 'next week';
            } elseif ($current == 1) {
                $startStr = 'next week';
                $endStr = '+1 week Monday';
            } else {
                $num = $current - 1;
                $startStr = '+ ' . $num . ' week Monday';
                $endStr = '+ ' . $current . ' week Monday';
            }
        } elseif ($cycle == 'month') {
            if ($current == 0) {
                $startStr = 'first weeks of this month';
                $endStr = 'last day of this month';
            } else {
                $startStr = 'first day of +'.$current.' month';
                $endStr = 'last day of +'.++$current.' month';
            }
        } else {
            return false;
        }
        $startTime = date("Y-m-d", strtotime($startStr));
        $endTime = date("Y-m-d", strtotime($endStr));
        return [$startTime, $endTime];
    }

    /**
     * 删除一条schedule记录
     * @param $scheduleId
     * @param $schoolId
     * @return bool
     */
    public function delSchedule($scheduleId, $schoolId)
    {
        $num = AttendanceSchedule::where('school_id', $schoolId)
            ->where('id', $scheduleId)
            ->delete();
        return $num>0;
    }


}