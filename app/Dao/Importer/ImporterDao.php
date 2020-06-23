<?php

namespace App\Dao\Importer;

use App\Dao\BuildFillableData;
use App\Models\Importer\ImportLog;
use App\Models\Importer\ImportTask;

class ImporterDao
{
    use BuildFillableData;

    /**
     * 添加
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        return ImportTask::create($data);
    }


    public function getTaskById($id, $field="*")
    {
        return ImportTask::where('id', $id)->select($field)->first();
    }

    public function getTasks($schoolId, array $type)
    {
       return ImportTask::where('school_id', $schoolId)->whereIn('type', $type)->get();
    }


    public function result($id)
    {
       return ImportLog::where('task_id', $id)->get();
    }

    /**
     * 根据 状态 获取导入任务
     * @param $status
     * @return mixed
     */
    public function getTasksByStatus($status)
    {
        return ImportTask::where('status', $status)->orderBy('id', 'asc')->first();
    }

    /**
     * 写入错误日志
     * @param $data
     * @return mixed
     */
    public function createErrorLog($data)
    {
        return ImportLog::create($data);
    }

    /**
     * 修改
     * @param $id
     * @param $data
     * @return mixed
     */
    public function update($id ,$data)
    {
        return ImportTask::where('id', $id)->update($data);
    }

    public function increment($id)
    {
        return ImportTask::where('id', $id)->increment('total');
    }

}
