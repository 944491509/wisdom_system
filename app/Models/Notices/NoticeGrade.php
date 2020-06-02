<?php


namespace App\Models\Notices;


use Illuminate\Database\Eloquent\Model;

class NoticeGrade extends Model
{
    protected $fillable = ['notice_id', 'grade_id'];

    public $timestamps = false;

}
