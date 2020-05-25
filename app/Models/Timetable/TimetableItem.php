<?php

namespace App\Models\Timetable;

use App\Models\Course;
use App\Models\School;
use App\Models\Schools\Building;
use App\Models\Schools\Grade;
use App\Models\Schools\Room;
use App\Models\AttendanceSchedules\Attendance;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Utils\Time\CalendarDay;


class TimetableItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'year', 'term', 'course_id', 'time_slot_id', 'building_id', 'room_id',
        'teacher_id', 'grade_id', 'weekday_index', 'repeat_unit','to_replace',
        'at_special_datetime', 'to_special_datetime', 'last_updated_by',
        'school_id', 'published', 'available_only', 'type' , 'substitute_id'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at',
    ];

    const TYPE_SUPPLY = 1;    // 教师代课
    const TYPE_SUBSTITUTION = 2;  // 调课  本班课节互换
    const TYPE_SUBSTITUTION_NOTHING = 3;  // 调课 他班互换
    const TYPE_SUPPLY_TEXT = '教师代课';
    const TYPE_SUBSTITUTION_TEXT = '本班课程课节互换';
    const TYPE_SUBSTITUTION_NOTHING_TEXT = '本班课程课节互换';

    const PASSIVITY = 0; // 被动调课
    const INITIATIVE = 1; // 主动调课
    const PASSIVITY_TEXT = '被动调课';
    const INITIATIVE_TEXT = '主动调课';

    public function getAllType() {
        return [
            self::TYPE_SUPPLY => self::TYPE_SUPPLY_TEXT,
            self::TYPE_SUBSTITUTION => self::TYPE_SUBSTITUTION_TEXT,
            self::TYPE_SUBSTITUTION_NOTHING => self::TYPE_SUBSTITUTION_NOTHING_TEXT
        ];
    }


    public function getAllInitiative() {
        return [
            self::PASSIVITY => self::PASSIVITY_TEXT,
            self::INITIATIVE => self::INITIATIVE_TEXT,
        ];
    }


    /**
     * 获取当前调课类型
     * @return string
     */
    public function getTypeText() {
        $allType = $this->getAllType();
        return $allType[$this->type] ?? '';
    }


    /**
     * @return string
     */
    public function getInitiativeText() {
        $allInitiative = $this->getAllInitiative();
        return $allInitiative[$this->initiative] ?? '';
    }







    public $casts = [
        'available_only' => 'array', // 如果是特定区间的课程, 那么表示在哪些周
        'published' => 'boolean', // 是否本 item 是发布状态
        'at_special_datetime' => 'datetime', // 调课记录的开始事件
        'to_special_datetime' => 'datetime', // 调课记录的结束时间
    ];

    public function getPublishedTextAttribute()
    {
        return $this->published ? '正式' : '草稿(待确认)';
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class);
    }

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function replacement()
    {
        return $this->belongsTo(self::class, 'to_replace');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }

    public function describeItself()
    {
        return $txt = $this->grade->name . ' - ' . $this->course->name;
    }

    public function itemEnquiries()
    {
        return $this->hasMany(TimetableItemEnquiry::class);
    }

    public function timeSlot()
    {
        return $this->belongsTo(TimeSlot::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class,'timetable_id', 'id');
    }


    public function getWeekIndex() {
        return CalendarDay::GetWeekDayIndex($this->weekday_index);
    }


    /**
     * @param $val
     * @return string
     */
    public function getAtSpecialDatetimeAttribute($val) {
        return Carbon::parse($val)->format('Y-m-d');
    }


    /**
     * @param $val
     * @return string
     */
    public function getToSpecialDatetimeAttribute($val) {
        return Carbon::parse($val)->format('Y-m-d');
    }
}
