<?php

namespace App\Models\Importer;

use App\Models\School;
use Illuminate\Database\Eloquent\Model;

class ImportTask extends Model
{
    const IMPORT_TASK_WAITING = 0;
    const IMPORT_TASK_WAITING_TEXT = '等待中'; // 正常, 可执行
    const IMPORT_TASK_EXECUTION = 1;
    const IMPORT_TASK_EXECUTION_TEXT = '导入中';
    const IMPORT_TASK_COMPLETE = 2;
    const IMPORT_TASK_COMPLETE_TEXT = '已完成';
    const IMPORT_TASK_WITHDRAW = 3;
    const IMPORT_TASK_WITHDRAW_TEXT = '撤回'; // 撤回, 不可执行

    const IMPORT_TYPE_NO_IDENTITY = 0;
    const IMPORT_TYPE_NO_IDENTITY_TEXT = '未认证';
    const IMPORT_TYPE_CERTIFIED = 1;
    const IMPORT_TYPE_CERTIFIED_TEXT = '已认证';

    protected $table = 'import_task';

    protected $fillable = [
        'status', 'title', 'manager_id', 'path', 'type', 'file_name', 'school_id', 'total', 'surplus'
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * 所有状态
     */
    public function allStatus()
    {
        return [
            self::IMPORT_TASK_WAITING   => self::IMPORT_TASK_WAITING_TEXT,
            self::IMPORT_TASK_EXECUTION => self::IMPORT_TASK_EXECUTION_TEXT,
            self::IMPORT_TASK_COMPLETE  => self::IMPORT_TASK_COMPLETE_TEXT,
            self::IMPORT_TASK_WITHDRAW  => self::IMPORT_TASK_WITHDRAW_TEXT,
        ];
    }

    /**
     * 当前状态
     */
    public function getStatus()
    {
        return $this->allStatus()[$this->status] ?? '';
    }
}
