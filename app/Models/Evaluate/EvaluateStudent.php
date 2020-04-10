<?php


namespace App\Models\Evaluate;


use App\Models\Schools\Grade;
use App\Models\Students\StudentProfile;
use App\User;
use Illuminate\Database\Eloquent\Model;

class EvaluateStudent extends Model
{
    protected $fillable = ['evaluate_teacher_id', 'user_id', 'grade_id', 'score'];


    public function evaluateTeacher() {
        return $this->belongsTo(EvaluateTeacher::class);
    }

    public function grade() {
        return $this->belongsTo(Grade::class);
    }

    public function profile() {
        return $this->belongsTo(StudentProfile::class ,'user_id', 'user_id');
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
