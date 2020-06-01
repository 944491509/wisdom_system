<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;

class UserSearchConfig extends Model
{

    const TYPE_0 = 0; // 民族
    const TYPE_1 = 1; // 政治面貌
    const TYPE_2 = 2; // 学历
    const TYPE_3 = 3; // 学位
    const TYPE_4 = 4; // 目前职称
    const TYPE_5 = 5; // 聘任状态
    const TYPE_6 = 6; // 聘任方式

    protected $fillable =['name', 'type'];
    public $timestamps = false;
}
