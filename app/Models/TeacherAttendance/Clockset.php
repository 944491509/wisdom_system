<?php

namespace App\Models\TeacherAttendance;

use Illuminate\Database\Eloquent\Model;

class Clockset extends Model
{
    //
    public $table = 'teacher_attendance_clocksets';
    protected $fillable = [
        'teacher_attendance_id','week','start','end','morning','morning_late','morning_end','afternoon_start','afternoon','afternoon_late','evening','is_weekday'
    ];
    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'teacher_attendance_id');
    }
}
