<?php

namespace App\Models\Timetable;

use App\Models\Course;
use App\Models\School;
use App\Models\Schools\Building;
use App\Models\Schools\Grade;
use App\Models\Schools\Room;
use App\Models\AttendanceSchedules\Attendance;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Utils\Time\CalendarDay;


class TimetableItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'year', 'term',
        'course_id', 'time_slot_id',
        'building_id', 'room_id',
        'teacher_id', 'grade_id',
        'weekday_index', 'repeat_unit',
        'at_special_datetime', 'to_special_datetime',
        'to_replace', 'last_updated_by',
        'school_id', 'published', 'available_only'
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at',
    ];

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
}
