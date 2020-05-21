<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 25/10/19
 * Time: 2:58 PM
 */

namespace App\Dao\Timetable;

use App\User;
use App\Utils\JsonBuilder;
use App\Utils\ReturnData\MessageBag;
use Carbon\Carbon;
use App\Dao\Schools\SchoolDao;
use App\Utils\Time\CalendarWeek;
use Illuminate\Support\Collection;
use App\Models\Timetable\TimeSlot;
use App\Utils\Time\GradeAndYearUtil;
use App\Models\Timetable\TimetableItem;
use Illuminate\Support\Facades\DB;

class TimetableItemDao
{
    /**
     * @var array 当前关联的时间段记录
     */
    protected $timeSlots;
    public function __construct($timeSlots = [])
    {
        $this->timeSlots = $timeSlots;
    }

    /**
     * @param $teacherId
     * @param $year
     * @param $term
     * @return Collection
     */
    public function getItemsByTeacherForApp($teacherId, $year, $term){
        return TimetableItem::select('grade_id')->where('year',$year)
            ->where('term',$term)
            ->where('teacher_id',$teacherId)
            ->get();
    }

    /**
     * @param $id
     * @param $withRelations
     * @return TimetableItem
     */
    public function getItemById($id, $withRelations = false){
        if($withRelations){
            return TimetableItem::where('id',$id)
                ->with('building')
                ->with('room')
                ->with('timeSlot')
                ->first();
        }
        return TimetableItem::find($id);
    }

    /**
     * @param $item
     * @return TimetableItem|bool
     */
    public function cloneItem($item){
        $origin = $this->getItemById($item['id']);
        if($origin){
            $fillable = $origin->getFillable();
            $data = [];
            foreach ($fillable as $fieldName) {
                $data[$fieldName] = $origin->$fieldName;
            }
            $data['weekday_index'] = $item['weekday_index'];
            $data['time_slot_id'] = $item['time_slot_id'];
            return $this->createTimetableItem($data);
        }
        return false;
    }

    /**
     * @param $specialCase
     * @param TimetableItem $origin
     * @param User $doer
     * @return null|TimetableItem
     */
    public function createSpecialCase($specialCase, $origin, $doer){
        $fillable = $origin->getFillable();
        $data = [];
        foreach ($fillable as $fieldName) {
            $data[$fieldName] = $origin->$fieldName;
        }
        foreach ($specialCase as $name=>$fieldValue) {
            if($name === 'at_special_datetime' || $name === 'to_special_datetime'){
                $carbon = GradeAndYearUtil::ConvertJsTimeToCarbon($fieldValue);
                if($carbon){
                    $fieldValue = $carbon->format('Y-m-d');
                    $data[$name] = $fieldValue;
                }
            }
            else{
                $data[$name] = $fieldValue;
            }
        }
        $data['last_updated_by'] = $doer->id;
        return $this->createTimetableItem($data);
    }

    /**
     * 添加新的 Item
     * @param $data
     * @return TimetableItem
     */
    public function createTimetableItem($data){
        return TimetableItem::create($data);
    }

    /**
     * 检查给定的数据, 是否已经被别人项占用
     * @param $data
     * @return bool|TimetableItem
     */
    public function hasAnyOneTakenThePlace($data){
        // 情况 1: 如果课程表项目, 它的重复周期是每周有效, 那么插入前要检查, 只要该时段有任意类型的项目, 则都不可插入
        if(intval($data['repeat_unit']) === GradeAndYearUtil::TYPE_EVERY_WEEK){
            $where = [
                ['school_id','=',$data['school_id']],
                ['weekday_index','=',$data['weekday_index']],
                ['time_slot_id','=',$data['time_slot_id']],
                ['year','=',$data['year']],
                ['term','=',$data['term']],
                ['building_id','=',$data['building_id']],
                ['room_id','=',$data['room_id']],
            ];
        }
        elseif(intval($data['repeat_unit']) === GradeAndYearUtil::TYPE_EVERY_EVEN_WEEK){
            $where = [
                ['school_id','=',$data['school_id']],
                ['weekday_index','=',$data['weekday_index']],
                ['time_slot_id','=',$data['time_slot_id']],
                ['year','=',$data['year']],
                ['term','=',$data['term']],
                ['building_id','=',$data['building_id']],
                ['room_id','=',$data['room_id']],
                ['repeat_unit','<>',GradeAndYearUtil::TYPE_EVERY_ODD_WEEK], // 想插入双周, 那么相同时间地点, 不能有双周或者每周的
            ];
        }
        elseif(intval($data['repeat_unit']) === GradeAndYearUtil::TYPE_EVERY_ODD_WEEK){
            $where = [
                ['school_id','=',$data['school_id']],
                ['weekday_index','=',$data['weekday_index']],
                ['time_slot_id','=',$data['time_slot_id']],
                ['year','=',$data['year']],
                ['term','=',$data['term']],
                ['building_id','=',$data['building_id']],
                ['room_id','=',$data['room_id']],
                ['repeat_unit','<>',GradeAndYearUtil::TYPE_EVERY_EVEN_WEEK], // 想插入单周, 那么相同时间地点, 不能有单周或者每周的
            ];
        }
        elseif(intval($data['repeat_unit']) === GradeAndYearUtil::TYPE_ONLY_AVAILABLE_WEEKS){
            // 只在指定区间有效是最高级别的, 所以可以取代任何其他单双周的
            return false;
        }
        else{
            return true; // 错误的数据, 直接 reject
        }

        $found = TimetableItem::where($where)->first();
        return $found??false;
    }

    /**
     * 删除
     * @param $id
     * @param User|null $doer
     * @return bool|null
     */
    public function deleteItem($id, $doer = null){
        $item = $this->getItemById($id);
        if($item){
            if($doer){
                // 记录下是谁删除的
                $item->last_updated_by = $doer->id;
                $item->save();
            }
        }
        return TimetableItem::where('id',$id)->delete();
    }

    /**
     * @param $id
     * @param User|null $doer
     * @return bool
     */
    public function publishItem($id, $doer=null){
        $item = $this->getItemById($id);
        if($item){
            if($doer){
                // 记录下是谁删除的
                $item->last_updated_by = $doer->id;
            }
            $item->published = true;
            return $item->save();
        }
        return false;
    }

    /**
     * 更新课程表项
     * @param $data
     * @return bool
     */
    public function updateTimetableItem($data){
        if(isset($data['id']) && $data['id']){
            $id = $data['id'];
            unset($data['id']);

            $data['available_only'] = json_encode($data['available_only']);

            return TimetableItem::where('id',$id)->update($data);

        }
        return false;
    }

    /**
     * 根据给定的条件加载某个班的某一天的课程表项列表
     * @param $weekDayIndex
     * @param $year
     * @param $weekType
     * @param $term
     * @param $gradeId
     * @return array
     */
    public function getItemsByWeekDayIndex($weekDayIndex, $year, $term, $weekType, $gradeId){
        $where = $this->_getItemsByWeekDayIndexBy($weekDayIndex, $year, $term, $weekType, ['grade_id'=>$gradeId]);
        /**
         * @var TimetableItem[] $rows
         */
        $field = ['timetable_items.*', 'time_slots.*', 'timetable_items.id as id' , 'timetable_items.year as year'];
        $rows = TimetableItem::where($where)
            ->join('time_slots', 'timetable_items.time_slot_id', '=', 'time_slots.id')
            ->orderBy('time_slots.from','asc')
            ->select($field)
            ->get();
        $result = [];

        foreach ($this->timeSlots as $timeSlot) {
            $result[$timeSlot->id] = '';
        }

        foreach ($rows as $row) {
            // 要判断一下, 是否为调课的记录
            if($row->course && $row->teacher){
                $result[$row->time_slot_id] = [
                    'course' => $row->course->name,
                    'course_id' => $row->course->id,
                    'teacher'=> $row->teacher->name,
                    'teacher_id'=> $row->teacher_id,
                    'building'=>$row->building->name??null,
                    'room'=>$row->room->name??null,
                    'room_id'=>$row->room_id,
                    'grade_id'=>$row->grade_id,
                    'id'=>$row->id,
                    'published'=>$row->published,
                    'repeat_unit'=>$row->repeat_unit,
                    'optional'=>$row->course->optional,
                    'weekday_index'=>$row->weekday_index,
                    'time_slot_id'=>$row->time_slot_id,
                    'specials'=>'',
                ];
            }
        }
        return $result;
    }

    public function getItemsByWeekDayIndexForApp($weekDayIndex, $year, $term, $weekType, $gradeId){
        $result = $this->getItemsByWeekDayIndex($weekDayIndex, $year, $term, $weekType,$gradeId);
        return array_values($result);
    }

    /**
     * 根据给定的条件加载某个课程的课程表项列表
     * @param $weekDayIndex
     * @param $year
     * @param $weekType
     * @param $term
     * @param $courseId
     * @return array
     */
    public function getItemsByWeekDayIndexForCourseView($weekDayIndex, $year, $term, $weekType, $courseId){
        $where = $this->_getItemsByWeekDayIndexBy($weekDayIndex, $year, $term, $weekType, ['course_id' => $courseId]);
        /**
         * @var TimetableItem[] $rows
         */
        $rows = TimetableItem::where($where)->orderBy('time_slot_id','asc')->get();

        $result = [];

        foreach ($this->timeSlots as $timeSlot) {
            $result[$timeSlot->id] = '';
        }

        foreach ($rows as $row) {
            // 要判断一下, 是否为调课的记录
            $result[$row->time_slot_id] = [
                'grade_name' => $row->grade->name,
                'teacher'=> $row->teacher->name,
                'teacher_id'=> $row->teacher_id,
                'building'=>$row->building->name,
                'room'=>$row->room->name,
                'room_id'=>$row->room_id,
                'id'=>$row->id,
                'published'=>$row->published,
                'repeat_unit'=>$row->repeat_unit,
                'optional'=>$row->course->optional,
                'weekday_index'=>$row->weekday_index,
                'time_slot_id'=>$row->time_slot_id,
                'specials'=>'',
            ];
        }
        return $result;
    }

    /**
     * 根据给定的条件加载 某个授课老师的排课
     * @param $weekDayIndex
     * @param $year
     * @param $weekType
     * @param $term
     * @param $teacherId
     * @return array
    */
    public function getItemsByWeekDayIndexForTeacherView($weekDayIndex, $year, $term, $weekType, $teacherId){
        $where = $this->_getItemsByWeekDayIndexBy($weekDayIndex, $year, $term, $weekType, ['teacher_id' => $teacherId]);
        /**
         * @var TimetableItem[] $rows
         */

        $field = ['timetable_items.*', 'time_slots.*', 'timetable_items.id as id' , 'timetable_items.year as year'];
        $rows = TimetableItem::where($where)
            ->leftJoin('time_slots',function ($join) {
                $join->on('timetable_items.time_slot_id', '=', 'time_slots.id');
            })
            ->orderBy('time_slots.from','asc')
            ->select($field)
            ->get();

        $result = [];

        foreach ($this->timeSlots as $timeSlot) {
            $result[$timeSlot->id] = '';
        }

        foreach ($rows as $row) {
            // todo 要判断一下, 是否为调课的记录

            $result[$row->time_slot_id] = [
                'timetable_id' => $row->id,
                'grade_name' => $row->grade->name,
                'course' => $row->course->name,
                'course_id' => $row->course->id,
                'teacher'=> $row->teacher->name,
                'teacher_id'=> $row->teacher_id,
                'grade'=> $row->grade->name,
                'grade_id'=> $row->grade_id,
                'building'=>$row->building->name,
                'room'=>$row->room->description,
                'room_id'=>$row->room_id,
                'id'=>$row->id,
                'published'=>$row->published,
                'repeat_unit'=>$row->repeat_unit,
                'optional'=>$row->course->optional,
                'weekday_index'=>$row->weekday_index,
                'time_slot_id'=>$row->time_slot_id,
                'time_slot' => $row->timeSlot,
                'specials'=>'',
            ];
        }
        return $result;
    }

    /**
     * 根据给定的条件加载 某个教室的 排课
     * @param $weekDayIndex
     * @param $year
     * @param $weekType
     * @param $term
     * @param $roomId
     * @return array
     */
    public function getItemsByWeekDayIndexForRoomView($weekDayIndex, $year, $term, $weekType, $roomId){
        $where = $this->_getItemsByWeekDayIndexBy($weekDayIndex, $year, $term, $weekType, ['room_id' => $roomId]);
        /**
         * @var TimetableItem[] $rows
         */
        $rows = TimetableItem::where($where)->orderBy('time_slot_id','asc')->get();

        $result = [];

        foreach ($this->timeSlots as $timeSlot) {
            $result[$timeSlot->id] = '';
        }

        foreach ($rows as $row) {
            // 要判断一下, 是否为调课的记录
            $result[$row->time_slot_id] = [
                'grade_name' => $row->grade->name,
                'course' => $row->course->name,
                'teacher'=>$row->teacher->name,
                'teacher_id'=> $row->teacher_id,
                'building'=>$row->building->name,
                'room'=>'',
                'room_id'=>$row->room_id,
                'id'=>$row->id,
                'published'=>$row->published,
                'repeat_unit'=>$row->repeat_unit,
                'optional'=>$row->course->optional,
                'weekday_index'=>$row->weekday_index,
                'time_slot_id'=>$row->time_slot_id,
                'specials'=>'',
            ];
        }
        return $result;
    }

    /**
     * 根据给定的条件加载课程表项列表
     * @param $weekDayIndex
     * @param $year
     * @param $term
     * @param $weekType
     * @param $by : 查询的关键字段数组 field=>value 键值对
     * @return array
     */
    private function _getItemsByWeekDayIndexBy($weekDayIndex, $year, $term, $weekType, $by){
        $where = [
            ['timetable_items.year','=',$year],
            ['timetable_items.term','=',$term],
            ['timetable_items.weekday_index','=',$weekDayIndex],
            ['timetable_items.to_replace','=',0], // 不需要调课记录
        ];

        foreach ($by as $k=>$v) {
            $where[] = [$k,'=',$v];
        }

        if($weekType === GradeAndYearUtil::WEEK_ODD){
            // 单周课程表, 那么就加载 每周 + 单周
            $where[] = [
                'timetable_items.repeat_unit','<>',GradeAndYearUtil::TYPE_EVERY_EVEN_WEEK
            ];
        }
        else{
            // 双周课程表, 那么就加载 每周 + 双周
            $where[] = [
                'timetable_items.repeat_unit','<>',GradeAndYearUtil::TYPE_EVERY_ODD_WEEK
            ];
        }
        return $where;
    }

    /**
     * 获取指定条件下的调课统计数据
     * 返回: [
     *      '原始的固定课表项 ID' => [调课项的 id 数组]
     * ]
     * @param $year
     * @param $term
     * @param $gradeId
     * @param $today
     * @return array
     */
    public function getSpecialsAfterToday($year, $term, $gradeId, $today){
        return $this->_getSpecialsAfterTodayBy($year, $term, $today, ['grade_id'=>$gradeId]);
    }

    /**
     * 获取指定条件下的调课统计数据: 从课程的角度出发
     * 返回: [
     *      '原始的固定课表项 ID' => [调课项的 id 数组]
     * ]
     * @param $year
     * @param $term
     * @param $courseId
     * @param $today
     * @return array
     */
    public function getSpecialsAfterTodayForCourseView($year, $term, $courseId, $today){
        return $this->_getSpecialsAfterTodayBy($year, $term, $today, ['course_id'=>$courseId]);
    }

    /**
     * 获取指定条件下的调课统计数据: 从课程的授课教师角度出发
     * 返回: [
     *      '原始的固定课表项 ID' => [调课项的 id 数组]
     * ]
     * @param $year
     * @param $term
     * @param $teacherId
     * @param $today
     * @return array
     */
    public function getSpecialsAfterTodayForTeacherView($year, $term, $teacherId, $today){
        return $this->_getSpecialsAfterTodayBy($year, $term, $today, ['teacher_id'=>$teacherId]);
    }

    /**
     * 获取指定条件下的调课统计数据: 从教室角度出发
     * 返回: [
     *      '原始的固定课表项 ID' => [调课项的 id 数组]
     * ]
     * @param $year
     * @param $term
     * @param $roomId
     * @param $today
     * @return array
     */
    public function getSpecialsAfterTodayForRoomView($year, $term, $roomId, $today){
        return $this->_getSpecialsAfterTodayBy($year, $term, $today, ['room_id'=>$roomId]);
    }

    public function _getSpecialsAfterTodayBy($year, $term, $today, $by){
        $where = [
            ['year','=',$year],
            ['term','=',$term],
            ['to_replace','>',0], // 只加载调课记录
            ['to_special_datetime','>=',$today->format('Y-m-d').' 00:00:00'], // 今天或者今天以后的
        ];

        foreach ($by as $k=>$v) {
            $where[] = [$k,'=',$v];
        }

        /**
         * @var TimetableItem[] $rows
         */
        $specialRows = TimetableItem::select(['id','to_replace'])
            ->where($where)->orderBy('time_slot_id','asc')->get();

        $specialCases = [];

        foreach ($specialRows as $specialRow) {
            if(isset($specialCases[$specialRow->to_replace])){
                $specialCases[$specialRow->to_replace][] = $specialRow->id;
            }
            else{
                $specialCases[$specialRow->to_replace] = [$specialRow->id];
            }
        }
        return $specialCases;
    }

    /**
     * @param $year
     * @param $term
     * @param $weekdayIndex
     * @param $timeSlotId
     * @param $buildingId
     * @param $published: 标识是否只查找已经发布的
     * @return array
     */
    public function getBookedRoomsId($year, $term, $weekdayIndex, $timeSlotId, $buildingId, $published = null){
        if($published){
            return TimetableItem::select('room_id')->where('year',$year)
                ->where('term',$term)
                ->where('weekday_index',$weekdayIndex)
                ->where('time_slot_id',$timeSlotId)
                ->where('building_id',$buildingId)
                ->where('published',$published)
                ->get()->toArray();
        }
        else{
            return TimetableItem::select('room_id')->where('year',$year)
                ->where('term',$term)
                ->where('weekday_index',$weekdayIndex)
                ->where('time_slot_id',$timeSlotId)
                ->where('building_id',$buildingId)
                ->get()->toArray();
        }
    }

    /**
     * 根据传入的 id 的数组, 加载全部列表
     * @param $ids
     * @return Collection
     */
    public function getItemsByIdArray($ids){
        return TimetableItem::whereIn('id',$ids)->get();
    }

    /**
     * 根据给定的用户获取 当前时间的 课程表项
     * @param User $user
     * @param Carbon $now
     * @return null
     */
    public function getCurrentItemByUser(User $user, $now = null){
        if(is_null($now)) {
            $now = Carbon::now(GradeAndYearUtil::TIMEZONE_CN);
        }
        $school = (new SchoolDao())->getSchoolById($user->getSchoolId());
        $weekdayIndex = $now->dayOfWeekIso;
        $year = $school->configuration->getSchoolYear();

        $term = $school->configuration->guessTerm($now->month);

        if($user->isStudent()) {
            $grade = $user->gradeUser->grade;

            $currentTimeSlot = GradeAndYearUtil::GetTimeSlot($grade->gradeYear(),$now, $school->id);
            if(is_null( $currentTimeSlot)) {
                return null;
            }
            $where = [
                ['school_id','=',$school->id],
                ['year','=',$year],
                ['term','=',$term],
                ['time_slot_id','=',$currentTimeSlot->id],
                ['grade_id','=',$grade->id],
                ['weekday_index','=',$weekdayIndex],
            ];

            return TimetableItem::where($where)->first();

        } elseif ($user->isTeacher()) {
            $where = [
                ['school_id', '=', $school->id],
                ['year', '=', $year],
                ['term', '=', $term],
                ['teacher_id', '=', $user->id],
                ['weekday_index', '=', $weekdayIndex],
            ];
            // 一个老师可以同时给多个班级上课
            $timeTables = TimetableItem::where($where)->get();
            if(count($timeTables) == 0) {
                return null;
            }

            foreach ($timeTables as $key => $item) {
                $timeSlot = $item->timeSlot;
                $timeSlotDao = new TimeSlotDao();
                if ($timeSlotDao->isCurrent($timeSlot)) {
                    // todo  这块应该返回二维数据处理
                    return collect([$item]);
                }

            }
            return null;
        }
    }



    /**
     * 查询学期课程的总结束包含调课
     * @param int $gradeId 班级ID
     * @param int $courseId 课程ID
     * @param int $year 学年
     * @param int $term 学期
     * @param CalendarWeek $weeks 学期周
     * @return float|int
     */
    public function getCourseCountByCourseId($gradeId, $courseId, $year, $term, $weeks) {
        $week = $weeks->count();
        $map = ['grade_id'=>$gradeId, 'course_id'=>$courseId, 'year'=>$year, 'term'=>$term];
        $list = TimetableItem::where($map)->get();
        $num = [];
        $minusNum = [];
        foreach ($list as $key => $val) {
            // 判断正常课程
            if(empty($val->at_special_datetime) && empty($val->to_special_datetime)) {
                $num[$key] = $this->getCourseCountByRepeatUnit($val->repeat_unit, $week);
                // 减去调课
                $where = ['grade_id'=>$gradeId, 'time_slot_id'=>$val->time_slot_id, 'year'=>$year, 'term'=>$term];
                $minusList = TimetableItem::where($where)->whereNotNull(['at_special_datetime', 'to_special_datetime'])->get();
                if(count($minusList) >0) {
                    foreach ($minusList as $k => $v) {
                        $minusCount[$k] = $this->getAdjustCourseCount($v->at_special_datetime, $v->to_special_datetime, $weeks, $v->weekday_index);
                    }
                    $minusNum[$key] = array_sum($minusCount);
                }
            } else{
                // 增加的调课
                $count = $this->getAdjustCourseCount($val->at_special_datetime, $val->to_special_datetime, $weeks, $val->weekday_index);
                $num[$key] = $count;
            }
        }

        return array_sum($num) - array_sum($minusNum);

    }


    /**
     * 根据单双周获取上课的次数
     * @param $repeatUnit
     * @param $week
     * @return float|int
     */
    public function getCourseCountByRepeatUnit($repeatUnit, $week) {
        switch ($repeatUnit) {
            case GradeAndYearUtil::TYPE_EVERY_WEEK :  // 每周都有课
                return $week; break;
            case GradeAndYearUtil::TYPE_EVERY_ODD_WEEK : // 表示每单周都有课
                return ceil($week/2); break;
            case GradeAndYearUtil::TYPE_EVERY_EVEN_WEEK: // 表示每双周都有课
                return floor($week/2); break;
            default:return 0;
        }
    }


    /**
     * 获取调节课程的次数
     * @param Carbon $atSpecialDatetime  调课的开始时间
     * @param Carbon $toSpecialDatetime  调课的结束时间
     * @param CalendarWeek $weeks 学期周
     * @param int $weekDayIndex  课程当周第几天
     * @return int
     */
    public function getAdjustCourseCount($atSpecialDatetime, $toSpecialDatetime, $weeks, $weekDayIndex) {
        foreach ($weeks as $key => $val) {
            if($val->includes($atSpecialDatetime)) {
                /**
                 *  @var CalendarWeek $val
                 */
                // 学期第多少周
                $startWeekIndex = $val->getScheduleWeekIndex();
                // 当前周的第几天
                $startWeekday = $atSpecialDatetime->dayOfWeekIso;
                // 判断这周开始时间的天大于该课的当周天
                if($startWeekday > $weekDayIndex) {
                    $startWeekIndex = $startWeekIndex + 1;
                }
            }
            if($val->includes($toSpecialDatetime)) {
                $endWeekIndex = $val->getScheduleWeekIndex();
                $endWeekDay = $toSpecialDatetime->dayOfWeekIso;
                // 判断这周结束时间的天小于该课的当周天
                if($endWeekDay < $weekDayIndex) {
                    $endWeekIndex = $endWeekIndex - 1;
                }
            }
        }

        return $endWeekIndex - $startWeekIndex + 1;
    }


    /**
     * 获取教师教的班级
     * @param $teacherId
     * @return mixed
     */
    public function getTeacherTeachingGrade($teacherId)
    {
      return  TimetableItem::where('teacher_id', $teacherId)->get();
    }

    /**
     * 根据班级ID查询代课老师
     * @param $gradeId
     * @param $year
     * @param $term
     * @return mixed
     */
    public function getItemByGradeId($gradeId, $year, $term) {
        return TimetableItem::select('teacher_id')
            ->where('grade_id',$gradeId)
            ->where('year',$year)
            ->where('term',$term)
            ->distinct('teacher_id')
            ->get();
    }

    /**
     * @param $courseId
     * @param $teacherId
     * @param $year
     * @param $term
     * @return Collection
     */
    public function getItemByGradeAndTeacherAndYear($courseId, $teacherId, $year, $term){
        return TimetableItem::where('year',$year)
            ->where('teacher_id',$teacherId)
            ->where('course_id',$courseId)
            ->where('term',$term)
            ->distinct('teacher_id')
            ->get();
    }

  /**
   * 上课3分钟内需要发送没有老师打卡的记录给教务处，需要一个总列表来比对
   * 获取当前时间应该上的所有课程
   * @param $schoolId
   * @param $timeSlotId
   * @return array
   */
    public function getCourseListByCurrentTime($schoolId, $timeSlotId = null)
    {
        $date = Carbon::now();
        $schoolDao = new SchoolDao();
        $timeSlotDao = new TimeSlotDao();
        $school = $schoolDao->getSchoolById($schoolId);
        $schoolConfiguration = $school->configuration;
        // 根据当前时间, 获取所在的学期, 年, 单双周, 第几节课
        $startDate = $schoolConfiguration->getTermStartDate();
        $year = $startDate->year;
        $term = $schoolConfiguration->guessTerm($date->month);

        $timeSlots = $timeSlotDao->getAllStudyTimeSlots($schoolId);

        $currentTimeSlot = null;
        foreach ($timeSlots as $timeSlot) {

            /**
             * @var TimeSlot $timeSlot
             */
            if($timeSlot->current){
                $currentTimeSlot = $timeSlot;
            }
        }

        if (empty($currentTimeSlot->id)) {
            return [];
        }

        $result =  TimetableItem::where('year', $year)
            ->where('term', $term)
            ->where('weekday_index',$date->dayOfWeekIso)
            ->where('time_slot_id', $currentTimeSlot->id)
            ->where('published', 1)
            ->with('timeslot');
        if (!is_null($timeSlotId)){
          $result = $result->where('time_slot_id', $timeSlotId);
        }
        return  $result->get();
    }


    /**
     * 获取当前时间今天已上的课
     * @param $schoolId
     * @param $year  int 学年
     * @param $term  int 学期
     * @param $teacherId  int 老师id
     * @param $gradeId  int 班级ID
     * @param $weekDayIndex int  周几
     * @return mixed
     */
    public function getTimetableItemByTime($schoolId, $year, $term, $teacherId, $gradeId, $weekDayIndex) {
        $field = ['timetable_items.*','time_slots.id as time_slot_id','time_slots.name'];
        $map = [
                ['time_slots.school_id', '=', $schoolId],
                ['timetable_items.year','=', $year],
                ['term', '=', $term],
                ['teacher_id', '=', $teacherId],
                ['grade_id', '=', $gradeId],
                ['weekday_index', '=', $weekDayIndex]
            ];

        return TimetableItem::join('time_slots',function ($join) use ($map) {
            $join->on('timetable_items.time_slot_id', '=', 'time_slots.id')
                ->where($map);
        })->select($field)->get();
    }

    /**
     * 根据给定的时间, 课节 获取当天所有的课程
     * @param $user
     * @param $time
     * @param $timeSlots
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTimetableItemByUserOrTime($user, $time, array $timeSlots)
    {
        $schoolDao = new SchoolDao;
        $school = $schoolDao->getSchoolById($user->getSchoolId());
        $configuration = $school->configuration;
        $date = Carbon::parse($time);
        // 根据给的时间 获取 学年, 学期,
        $year = $configuration->getSchoolYear($date);
        $month = Carbon::parse($date)->month;
        $term = $configuration->guessTerm($month);
        $weekDayIndex = Carbon::parse($date)->dayOfWeekIso;
        $map = [
            ['school_id','=', $user->getSchoolId()],
            ['year', '=', $year],
            ['term', '=', $term],
            ['weekday_index', '=', $weekDayIndex],
            ['published', '=', 1],
        ];
        return TimetableItem::where($map)->whereIn('time_slot_id', $timeSlots)->get();
    }

    /**
     * @param $courseId
     * @param $teacherId
     * @param null $date
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getGradesByCourseAndTeacher($courseId, $teacherId, $date = null){
        $yearAndTerm = GradeAndYearUtil::GetYearAndTerm($date ?? Carbon::now());
        return TimetableItem::select(['grade_id'])
            ->where('course_id',$courseId)
            ->where('teacher_id',$teacherId)
            ->where('year',$yearAndTerm['year'])
            ->where('term',$yearAndTerm['term'])
            ->distinct()
            ->with('grade')
            ->get();
    }

    /**
     * 获取老师
     * @param array $coursesId
     * @param int $gradeId
     * @param int $year
     * @param int $term
     * @return mixed
     */
    public function getGradeTeachersByCoursesId($coursesId, $gradeId, $year, $term) {
        $map = ['year'=>$year, 'grade_id'=>$gradeId, 'term'=>$term];
        return TimetableItem::whereIn('course_id', $coursesId)
            ->where($map)
            ->select([ 'teacher_id', 'course_id'])
            ->distinct('course_id')
            ->get();
    }
    /**
     * 根据班级和课程的id, 获取老师的信息
     * @param $courseId
     * @param $gradeId
     * @param null $date
     * @return Collection
     */
    public function getItemsByCourseAndGrade($courseId, $gradeId, $date = null) {
        $yearAndTerm = GradeAndYearUtil::GetYearAndTerm($date ?? Carbon::now());
        return TimetableItem::select(['teacher_id'])
            ->where('course_id',$courseId)
            ->where('grade_id',$gradeId)
            ->where('year',$yearAndTerm['year'])
            ->where('term',$yearAndTerm['term'])
            ->distinct()
            ->with('teacher')
            ->get();
    }


    /**
     * 获取当天未上的课程
     * @param User $user
     * @param $now
     * @return null
     */
    public function getUnEndCoursesByUser(User $user, $now) {
        if(is_null($now)) {
            $now = Carbon::now(GradeAndYearUtil::TIMEZONE_CN);
        }
        $grade = $user->gradeUser->grade;
        $school = (new SchoolDao())->getSchoolById($user->getSchoolId());
        $currentTimeSlot = GradeAndYearUtil::GetUnEndTimeSlot($grade->gradeYear(),$now, $school->id);
        if(!empty($currentTimeSlot) && $school){
            $weekdayIndex = $now->dayOfWeekIso;
            // 当前学年
            $year = $school->configuration->getSchoolYear();

            $term = $school->configuration->guessTerm($now->month);

            foreach ($currentTimeSlot as $key => $val) {
                $where = [
                    ['school_id','=',$school->id],
                    ['year','=',$year],
                    ['term','=',$term],
                    ['grade_id','=',$user->gradeUser->grade_id],
                    ['weekday_index','=',$weekdayIndex],
                    ['time_slot_id','=',$val->id],
                ];

                $result = TimetableItem::where($where)->orderBy('id', 'desc')->first();
                if(!is_null($result)) {
                    return $result; break;
                }
            }
        }
        return null;
    }


    /**
     * 查询老师所待课程的班级
     * @param $courseId
     * @param $teacherId
     * @return mixed
     */
    public function getGradeListByCourseIdAndTeacherId($courseId, $teacherId) {
        $map = ['course_id'=>$courseId, 'teacher_id'=>$teacherId];
        return TimetableItem::where($map)
            ->select('grade_id')
            ->distinct('grade_id')
            ->get();
    }

  /**
   * 获取同一时间上课的老师
   * @param $schoolId
   * @param $year
   * @param $term
   * @param $weekDayIndex
   * @param $timeSlotId
   * @return mixed
   */
    public function getSameTimePeopleNum($schoolId, $year, $term, $weekDayIndex, $timeSlotId)
    {
       $map = [
            ['school_id', '=', $schoolId],
            ['year', '=', $year],
            ['term', '=', $term],
            ['weekday_index', '=', $weekDayIndex],
            ['time_slot_id', '=', $timeSlotId],
       ];

       return TimetableItem::where($map)->count();
    }


    public function getTimeTableItemById($timeTableId) {
        return TimetableItem::where('id',$timeTableId)->first();
    }


    /**
     * 查询班级要上的课程
     * @param $year
     * @param $term
     * @param $gradeId
     * @return mixed
     */
    public function getCoursesByYearAndTermAndGradeId($year, $term, $gradeId) {
        $map = ['year'=>$year, 'term'=>$term, 'grade_id'=>$gradeId];
        return TimetableItem::where($map)
            ->select('course_id')
            ->distinct('course_id')
            ->get();
    }


    /**
     * 查询调课
     * @param $timeTableId
     * @param Carbon|null $currentTime
     * @return mixed
     */
    public function getTimeTableItemByToReplace($timeTableId, Carbon $currentTime = null) {
        if(is_null($currentTime)) {
            $currentTime = Carbon::now();
        }
        $time = $currentTime->toDateTimeString();
        $where = [
            ['to_replace', '=', $timeTableId],
            ['at_special_datetime', '<=', $time],
            ['to_special_datetime', '>=', $time],
        ];
        return TimetableItem::where($where)->first();
    }


    /**
     * 查询调课
     * @param $timeSlotId
     * @param $teacherId
     * @param $weekIndex
     * @param Carbon|null $currentTime
     * @return mixed
     */
    public function getTimeTableItemByTimeSlotId($timeSlotId, $teacherId,$weekIndex,Carbon $currentTime = null) {
        if(is_null($currentTime)) {
            $currentTime = Carbon::now();
        }
        $time = $currentTime->toDateTimeString();
        $where = [
            ['time_slot_id', '=', $timeSlotId],
            ['weekday_index', '=', $weekIndex],
            ['teacher_id', '=', $teacherId],
            ['at_special_datetime', '<=', $time],
            ['to_special_datetime', '>=', $time],
        ];
        return TimetableItem::where($where)->first();
    }


    /**
     * 调课验证
     * @param $data
     * @param $userId
     * @return MessageBag
     */
    public function switchingCheck($data, $userId) {
        switch ($data['type']) {
            case 1 : return $this->_restTeacher($data, $userId);
            case 2 : return $this->_timeslotChange($data, $userId);
            case 3 : return $this->_gradeTimeslotChange($data, $userId);
        }
    }


    /**
     * 确认调课
     * @param $data
     * @param $userId
     * @return MessageBag
     */
    public function affirmSave($data, $userId) {
        $timetableItem = $this->getItemById($data['timetable_id']);
        $timetableItemId = $timetableItem['id'];
        unset($timetableItem['id']);
        $timetableItem->building_id = $data['building_id'] ;
        $timetableItem['room_id'] = $data['room_id'] ;
        $timetableItem['teacher_id'] = $data['teacher_id'] ;
        $timetableItem['to_replace'] = $timetableItemId ;
        $timetableItem['at_special_datetime'] = $data['at_special_datetime'];
        $timetableItem['to_special_datetime'] = $data['to_special_datetime'];
        $timetableItem['last_updated_by'] = $userId;
        $re = TimetableItem::create($timetableItem->toArray());
        $bag = new MessageBag();
        if($re) {
            $bag->setMessage('调课成功');
        } else {
            $bag->setMessage('调课失败');
            $bag->setCode(JsonBuilder::CODE_ERROR);
        }
        return $bag;
    }


    /**
     * 调课给其他代课老师
     * @param $data
     * @param $userId
     * @return MessageBag
     */
    public function _restTeacher($data, $userId) {
        // 判断教师
        $bag = new MessageBag(JsonBuilder::CODE_ERROR);
        if(!isset($data['teacher_id']) || empty($data['teacher_id'])) {
            $bag->setMessage('teacher_id不能为空');
            return $bag;
        }
        if(!isset($data['building_id']) || empty($data['building_id'])) {
            $bag->setMessage('building_id不能为空');
            return $bag;
        }
        if(!isset($data['room_id']) || empty($data['room_id'])) {
            $bag->setMessage('room_id不能为空');
            return $bag;
        }
        // 查询当前课程
        $timetableItem = $this->getItemById($data['timetable_id']);
        if(is_null($timetableItem)) {
            $bag->setMessage('当前课程不存在');
            return $bag;
        }
        if($timetableItem->teacher_id == $data['teacher_id']) {
            $bag->setMessage('课程表教师与要调课的教师是同一个人');
            return $bag;
        }

        $map = [
            'time_slot_id' =>$timetableItem->time_slot_id,
            'room_id' => $data['room_id'],
            'year' => $timetableItem->year,
            'term' => $timetableItem->term,
            'weekday_index' => $timetableItem->weekday_index,
            'published' => true,
        ];
        // 判断当前教室是否被占用
        $room = TimetableItem::where($map)->where('grade_id','<>', $timetableItem->grade_id)
            ->where(function ($que) use ($data) {
                $que->where('to_replace', '=', 0) // 没有调课的
                ->orwhere(function ($que) use ($data) {
                    $que->where('to_replace', '>', 0)
                        ->where('at_special_datetime', '<=', $data['to_special_datetime'])
                        ->where('to_special_datetime', '>=', $data['at_special_datetime']);
                });
            })->first();
        if(!is_null($room)) {
            $bag->setMessage('当前教室已被'.$room->grade->name.'占用');
            $bag->setCode(1001);
            return $bag;
        }
        // 判断该老师当前时间是否有课
        unset($map['room_id']);
        $map['teacher_id'] = $data['teacher_id'];
        $teacher = TimetableItem::where($map)
            ->where(function ($que) use ($data) {
                $que->where('to_replace', '=', 0)  // 没有调课的
                ->orWhere(function ($que) use ($data) {
                    $que->where('to_replace', '>', 0)
                        ->where('at_special_datetime', '<=', $data['to_special_datetime'])
                        ->where('to_special_datetime', '>=', $data['at_special_datetime']);
                });
            })->first();
        if(!is_null($teacher)) {
            $bag->setMessage('当前老师在'.$teacher->grade->name.'有课程');
            $bag->setCode(1001);
            return $bag;
        }
        // 保存
        $timetableItemId = $timetableItem['id'];
        unset($timetableItem['id']);
        $timetableItem->building_id = $data['building_id'] ;
        $timetableItem['room_id'] = $data['room_id'] ;
        $timetableItem['teacher_id'] = $data['teacher_id'] ;
        $timetableItem['to_replace'] = $timetableItemId ;
        $timetableItem['at_special_datetime'] = $data['at_special_datetime'];
        $timetableItem['to_special_datetime'] = $data['to_special_datetime'];
        $timetableItem['last_updated_by'] = $userId;
        $re = TimetableItem::create($timetableItem->toArray());
        if($re) {
            $bag->setCode(JsonBuilder::CODE_SUCCESS);
            $bag->setMessage('调课成功');
        } else {
            $bag->setMessage('调课失败');
        }
        return $bag;
    }






    /**
     * 本班课节互调
     * @param $data
     * @param $userId
     * @return MessageBag
     */
    public function _timeslotChange($data, $userId) {
        $bag = new MessageBag(JsonBuilder::CODE_ERROR);
        if(!isset($data['weekday_index']) || empty($data['weekday_index'])) {
            $bag->setMessage('星期不能为空');
            return $bag;
        }
        if(!isset($data['time_slot_id']) || empty($data['time_slot_id'])) {
            $bag->setMessage('课节不能为空');
            return $bag;
        }
        // 查询当前课程
        $timetableItem = $this->getItemById($data['timetable_id']);
        if(is_null($timetableItem)) {
            $bag->setMessage('当前课程不存在');
            return $bag;
        }

        // 查询要调的课表
        $map = [
            'time_slot_id' => $data['time_slot_id'],
            'grade_id' => $timetableItem['grade_id'],
            'weekday_index' => $data['weekday_index'],
            'year' => $timetableItem['year'],
            'term' => $timetableItem['term'],
        ];
        $item = TimetableItem::where($map)->first();
        try{
            DB::beginTransaction();
            if(!is_null($item)) {
                $newItem = $item;
                $newItemId = $newItem['id'];
                unset($newItem['id']);
                $newItem['time_slot_id'] = $timetableItem['time_slot_id'];
                $newItem['weekday_index'] = $timetableItem['weekday_index'];
                $newItem['to_replace'] = $newItemId;
                $newItem['at_special_datetime'] = $data['at_special_datetime'];
                $newItem['to_special_datetime'] = $data['to_special_datetime'];
                $newItem['last_updated_by'] = $userId;

                TimetableItem::create($newItem->toArray());
            }
            $timetableItemId = $timetableItem['id'];
            unset($timetableItem['id']);
            $timetableItem['time_slot_id'] = $data['time_slot_id'];
            $timetableItem['weekday_index'] = $data['weekday_index'];
            $timetableItem['to_replace'] = $timetableItemId;
            $timetableItem['at_special_datetime'] = $data['at_special_datetime'];
            $timetableItem['to_special_datetime'] = $data['to_special_datetime'];
            $timetableItem['last_updated_by'] = $userId;

            TimetableItem::create($timetableItem->toArray());
            DB::commit();
            $bag->setMessage('调课成功');
            $bag->setCode(JsonBuilder::CODE_SUCCESS);
        } catch (\Exception $e) {
            DB::rollBack();
            $msg = $e->getMessage();
            $bag->setMessage($msg);
        }
        return $bag;
    }


    /**
     * 替他班级课节互调
     * @param $data
     * @param $userId
     * @return MessageBag
     */
    public function _gradeTimeslotChange($data, $userId) {
        $bag = new MessageBag(JsonBuilder::CODE_ERROR);
        if(!isset($data['grade_id']) || empty($data['grade_id'])) {
            $bag->setMessage('班级不能为空');
            return $bag;
        }
        if(!isset($data['weekday_index']) || empty($data['weekday_index'])) {
            $bag->setMessage('星期不能为空');
            return $bag;
        }
        if(!isset($data['time_slot_id']) || empty($data['time_slot_id'])) {
            $bag->setMessage('课节不能为空');
            return $bag;
        }
        // 查询当前课程
        $timetableItem = $this->getItemById($data['timetable_id']);
        if(is_null($timetableItem)) {
            $bag->setMessage('当前课程不存在');
            return $bag;
        }
        // 查询要调的课表
        $map = [
            'time_slot_id' => $data['time_slot_id'],
            'grade_id' => $data['grade_id'],
            'weekday_index' => $data['weekday_index'],
            'year' => $timetableItem['year'],
            'term' => $timetableItem['term'],
        ];

        $item = TimetableItem::where($map)->first();
        try{
            DB::beginTransaction();
            if(!is_null($item)) {
                $newItem = $item;
                $newItemId = $newItem['id'];
                unset($newItem['id']);
                $newItem['grade_id'] = $timetableItem['grade_id'];
                $newItem['time_slot_id'] = $timetableItem['time_slot_id'];
                $newItem['weekday_index'] = $timetableItem['weekday_index'];
                $newItem['to_replace'] = $newItemId;
                $newItem['at_special_datetime'] = $data['at_special_datetime'];
                $newItem['to_special_datetime'] = $data['to_special_datetime'];
                $newItem['last_updated_by'] = $userId;

                TimetableItem::create($newItem->toArray());
            }
            $timetableItemId = $timetableItem['id'];
            unset($timetableItem['id']);
            $timetableItem['grade_id'] = $data['grade_id'];
            $timetableItem['time_slot_id'] = $data['time_slot_id'];
            $timetableItem['weekday_index'] = $data['weekday_index'];
            $timetableItem['to_replace'] = $timetableItemId;
            $timetableItem['at_special_datetime'] = $data['at_special_datetime'];
            $timetableItem['to_special_datetime'] = $data['to_special_datetime'];
            $timetableItem['last_updated_by'] = $userId;

            TimetableItem::create($timetableItem->toArray());
            DB::commit();
            $bag->setMessage('调课成功');
            $bag->setCode(JsonBuilder::CODE_SUCCESS);
        } catch (\Exception $e) {
            DB::rollBack();
            $msg = $e->getMessage();
            $bag->setMessage($msg);
        }
        return $bag;

    }


    /**
     * 查询调课
     * @param $map
     * @return mixed
     */
    public function getTimetable($map) {
        return TimetableItem::where($map)->get();
    }

}
