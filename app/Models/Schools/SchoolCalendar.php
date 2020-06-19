<?php

namespace App\Models\Schools;

use Illuminate\Database\Eloquent\Model;

class SchoolCalendar extends Model
{
    protected $fillable = [
        'school_id', 'tag', 'content', 'event_time', 'week_idx', 'term', 'year', 'type',
    ];

    protected $casts = [
        'tag' => 'array',
        'type' => 'array',
        'event_time' => 'datetime:Y-m-d',
    ];


    // 事件类型
    const WEEKEND_REST = 0; // 周末休息
    const STATUTORY_HOLIDAY = 1; // 法定节假日
    const TEMPORARY_REST = 2; // 临时休息
    const SPORTS_MEET = 3; // 运动会
    const COMMONALITY_ACTIVITY = 4; // 公告活动
    const TERM_BEGINS = 5; // 开学
    const EXAMINATION = 6; // 考试

    const WEEKEND_REST_TEXT = '周末休息';
    const STATUTORY_HOLIDAY_TEXT = '法定节假日';
    const TEMPORARY_REST_TEXT = '临时休息';
    const COMMONALITY_ACTIVITY_TEXT = '公共活动';
    const SPORTS_MEET_TEXT = '运动会';
    const TERM_BEGINS_TEXT =  '开学';
    const EXAMINATION_TEXT =  '考试';


    public function getAllType() {
        return [
            self::WEEKEND_REST => self::WEEKEND_REST_TEXT,
            self::STATUTORY_HOLIDAY => self::STATUTORY_HOLIDAY_TEXT,
            self::TEMPORARY_REST => self::TEMPORARY_REST_TEXT,
            self::SPORTS_MEET => self::SPORTS_MEET_TEXT,
            self::COMMONALITY_ACTIVITY => self::COMMONALITY_ACTIVITY_TEXT,
            self::TERM_BEGINS => self::TERM_BEGINS_TEXT,
            self::EXAMINATION => self::EXAMINATION_TEXT,
        ];
    }

}
