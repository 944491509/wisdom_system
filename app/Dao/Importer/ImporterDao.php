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

    public function getTasks($schoolId)
    {
       return ImportTask::where('school_id', $schoolId)->get();
    }


    public function result($id)
    {
       return ImportLog::where('task_id', $id)->where('task_status', ImportLog::FAIL_STATUS)->get();
    }

    /**
     * 按顺序取出未处理的导入需求，每次只取一条处理，定时任务每小时执行一次，每天导入最多24条任务
     * @return mixed
     */
    public function getTasksForNewPlan()
    {
        return ImportTask::where('status', 1)->where('school_id', '>', 0)->orderBy('id', 'asc')->first();
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

}
