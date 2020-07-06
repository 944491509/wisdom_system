<?php


namespace App\Models\Misc;


use Illuminate\Database\Eloquent\Model;

class SystemNotificationsGrades extends Model
{
    protected $fillable = [
        'system_notifications_id',
        'grade_id',
    ];
}
