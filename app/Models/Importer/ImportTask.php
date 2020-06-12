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
    const IMPORT_TASK_COMPLETE = 1;
    const IMPORT_TASK_COMPLETE_TEXT = '已完成';
    const IMPORT_TASK_WITHDRAW = 1;
    const IMPORT_TASK_WITHDRAW_TEXT = '撤回'; // 撤回, 不可执行


    protected $table = 'import_task';
    protected $fillable = [
        'status', 'title', 'manager_id', 'file_path', 'config', 'file_info', 'school_id', 'type'
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
