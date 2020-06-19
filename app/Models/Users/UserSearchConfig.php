<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;

class UserSearchConfig extends Model
{
    // 教师
    const TYPE_0 = 0; // 民族
    const TYPE_1 = 1; // 政治面貌
    const TYPE_2 = 2; // 学历
    const TYPE_3 = 3; // 学位
    const TYPE_4 = 4; // 目前职称
    const TYPE_5 = 5; // 聘任状态
    const TYPE_6 = 6; // 聘任方式
    // 学生
    const TYPE_7 = 7; // 健康状况
    const TYPE_8 = 8; // 学生居住地类型
    const TYPE_9 = 9; // 入学方式
    const TYPE_10 = 10; // 分段培养方式
    const TYPE_11 = 11; // 关系
    const TYPE_12 = 12; // 困难程度
    const TYPE_13 = 13; // 学制
    const TYPE_14 = 14; // 学生类别
    const TYPE_15 = 15; // 招生对象
    const TYPE_16 = 16; // 联招合作类型
    const TYPE_17 = 17; // 招生方式

    protected $fillable = ['name', 'type'];
    public $timestamps = false;
}
