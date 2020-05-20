<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 23/10/19
 * Time: 10:17 AM
 */

namespace App\Dao\Timetable;

use App\Models\Schools\Grade;
use Carbon\Carbon;
use App\Models\School;
use App\Models\Schools\Room;
use App\Dao\Schools\SchoolDao;
use Illuminate\Support\Collection;
use App\Models\Timetable\TimeSlot;
use App\Utils\Time\GradeAndYearUtil;
use App\Models\Timetable\TimetableItem;
use App\Models\Schools\SchoolConfiguration;

class TimeSlotDao
{
    /**
     * @var School|null
     */
    private $currentSchool;
    public function __construct($school = null)
    {
        $this->currentSchool = $school;
    }

    public function getById($id){
        return TimeSlot::find($id);
    }

    public function update($id, $data){
        return TimeSlot::where('id',$id)->update($data);
    }

    /**
     * 获取系统设置的默认时间段
     * @param int $seasonType
     * @param bool $asJsonObject
     * @return array
     */
    public function getDefaultTimeFrame($seasonType = TimeSlot::SEASONS_SUMMER_AND_AUTUMN, $asJsonObject = false){
        $txt = file_get_contents(__DIR__.($seasonType === TimeSlot::SEASONS_SUMMER_AND_AUTUMN ? '/default_time_frames.json' : '/default_time_frames_summer.json'));
        return $asJsonObject ? $txt : json_decode($txt, true);
    }

    /**
     * 创建时间段
     * @param $data
     * @return TimeSlot
     */
    public function createTimeSlot($data){
        return TimeSlot::create($data);
    }

    /**
     * 获取所有用于学习的时间段: 上课 + 自习 + 自由活动
     * @param $schoolId
     * @param int  $gradeYear 年级
     * @param boolean $simple
     * @param boolean $noTime 不要时间
     * @return array|Collection
     */
    public function getAllStudyTimeSlots($schoolId, $gradeYear, $simple = false, $noTime = false){

        // 现在只使用一套作息时间 夏季作息时间
        $seasonType = TimeSlot::SEASONS_SUMMER_AND_AUTUMN;
        $slots = TimeSlot::where('school_id',$schoolId)
            ->where('season',$seasonType)
            ->where('year',$gradeYear)
            ->where('status', TimeSlot::STATUS_SHOW)  // 显示
            ->whereIn('type',[TimeSlot::TYPE_STUDYING, TimeSlot::TYPE_PRACTICE, TimeSlot::TYPE_FREE_TIME])
            ->orderBy('from','asc')
            ->get();

        if(!$simple){
            $data = [];
            foreach ($slots as $slot) {
                $slot->current = $this->isCurrent($slot);
                $data[] = $slot;
            }
            return $data;
        }
        $result = [];

        foreach ($slots as $slot) {
            $name = $slot->name;
            if(!$noTime){
                $name .= ' ('.substr($slot->from,0,5).' - '.substr($slot->to,0,5).')';
            }
            $result[] = [
                'id'=>$slot->id,
                'name'=>$name,
                'from'=>$slot->from,
                'to'=>$slot->to,
                'type'=>$slot->type,
                'current'=>$this->isCurrent($slot),
            ];
        }

        return $result;
    }

    /**
     * 根据当前的时间点, 判断是否给定的 time slot 是当前
     * @param $timeSlot
     * @return bool
     */
    public function isCurrent($timeSlot){
        $time = now(GradeAndYearUtil::TIMEZONE_CN)->format('H:i:s');
        return $timeSlot->from <= $time && $time < $timeSlot->to;
    }


    /**
     * 为云班牌提供当前班级的课程列表的方法.
     * 提供当前上课的班级, 返回 Timetable Item 集合
     * @param Grade $grade
     * @return TimetableItem[]
     */
    public function getTimeSlotByGrade(Grade $grade)
    {

        $date = Carbon::now();

        /**
         * @var School $school
         */
        $school = $grade->school;
        $schoolConfiguration = $school->configuration;

        // 根据当前时间, 获取所在的学期, 年, 单双周, 第几节课
        $startDate = $schoolConfiguration->getTermStartDate();
        $year = $startDate->year;
        $term = $schoolConfiguration->guessTerm($date->month);
        // 获取当前年级作息时间
        $hour = $date->format('H:i:s');

        $where = [
            ['grade_id', '=', $grade->id],
            ['timetable_items.year', '=', $year],
            ['term', '=', $term],
            ['weekday_index', '=', $date->dayOfWeekIso],
            ['time_slots.status', '=', TimeSlot::STATUS_SHOW]
        ];

        return TimetableItem::where($where)
            ->whereTime('time_slots.to', '>', $hour)
            ->join('time_slots', 'timetable_items.time_slot_id', '=', 'time_slots.id')
            ->orderBy('time_slots.from','asc')
            ->orderBy('timetable_items.id', 'desc')
            ->get();
    }

    /**
     * 根据房间号获取当前正在上课的的 Timetable Item
     * @param Room $room
     * @param null $date
     * @param int $type
     * @return TimetableItem|null
     */
    public function getItemByRoomForNow(Room $room, $date = null, $type = 0 ){
        if(!$date){
            $date = Carbon::now();
        }

        /**
         * @var School $school
         */
        $school = $room->school;
        $schoolConfiguration = $school->configuration;

        // 根据当前时间, 获取所在的学期, 年, 单双周, 第几节课
        $startDate = $schoolConfiguration->getTermStartDate();
        $year = $startDate->year;
        $term = $schoolConfiguration->guessTerm($date->month);
        // 先查询当前教室今天要上的所有的课
        $map = [
            'room_id' => $room->id,
            'timetable_items.year' => $year,
            'term' => $term,
            'weekday_index' => $date->dayOfWeekIso,
        ];

        $field = ['timetable_items.*', 'time_slots.*', 'timetable_items.id as id' , 'timetable_items.year as year'];
        $timeTableItems = TimetableItem::where($map)->leftJoin('time_slots',function ($join) {
                $join->on('timetable_items.time_slot_id', '=', 'time_slots.id')
                ->where('time_slots.status', '=', TimeSlot::STATUS_SHOW);
            })
            ->select($field)
            ->orderBy('time_slots.from','asc')
            ->orderBy('timetable_items.id', 'desc')
            ->get()
            ->groupBy('time_slot_id');

        /**
         * 由于添加课程有bug 课程去重
         */
        $newData = collect();
        foreach ($timeTableItems as $key => $value) {
            $newData->push($value[0]);
        }

        if ($type == 0) {
            foreach ($newData as $key => $item) {
                // 判断这节课是否是当前课
                $timeSlot = $item->timeSlot;
                if($this->isCurrent($timeSlot)) {
                    return $item;
                    break;
                }
            }
        }elseif ($type == 1) {

            foreach ($newData as $key => $val) {
                $timeSlot = $val->timeSlot;
                if ($date->format('H:i:s') > $timeSlot->to) {
                    unset($newData[$key]);
                }
            }
            return  $newData->merge([]);
        }
        return null;

    }

    /**
     * 获取当前时间的第几节课
     * @param $schoolId
     * @param null $time
     * @return mixed
     */
    public function getTimeSlotByCurrentTime($schoolId, $time = null) {
        if(is_null($time)) {
            $time = Carbon::now()->toTimeString();
        }
        // 现在作息时间只使用一套 默认夏季作息时间
        $season = TimeSlot::SEASONS_SUMMER_AND_AUTUMN;
        $map = [
            ['school_id', '=', $schoolId],
            ['from', '<', $time],
            ['to', '>', $time],
            ['season', '=', $season],
            ['status', '=', TimeSlot::STATUS_SHOW]
//            ['type', '=', TimeSlot::TYPE_STUDYING]
        ];
        return TimeSlot::where($map)->first();
    }
}
