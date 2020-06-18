<?php

namespace App\Models\Importer;

use Illuminate\Database\Eloquent\Model;

class ImportLog extends Model
{
    const FAIL_STATUS = 4; //学校管理员操作状态失败
    protected $table = 'import_log';
    protected $fillable = [
        'task_id', 'number', 'name', 'id_number', 'error_log'
    ];
}
