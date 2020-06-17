<?php


namespace App\Dao\Importer;


use App\Dao\BuildFillableData;
use App\Models\Importer\ImportLog;
use App\Models\Importer\ImportTask;
use App\Utils\JsonBuilder;
use App\Utils\ReturnData\MessageBag;
use Exception;
use Illuminate\Support\Facades\DB;

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

    public function update($data)
    {
        $id = $data['id'];
        unset($data['id']);
        $messageBag = new MessageBag(JsonBuilder::CODE_ERROR);
        DB::beginTransaction();
        try {
            $fillableData = $this->getFillableData(new ImportTask(), $data);
            $importTask   = ImportTask::where('id', $id)->update($fillableData);
            if ($importTask) {
                DB::commit();
                $messageBag->setCode(JsonBuilder::CODE_SUCCESS);
                $messageBag->setData(ImportTask::find($id));
            } else {
                DB::rollBack();
                $messageBag->setMessage('更新导入任务失败, 请联系管理员');
            }
        } catch (Exception $exception) {
            DB::rollBack();
            $messageBag->setMessage($exception->getMessage());
        }
        return $messageBag;
    }

    public function getTaskById($id, $field="*")
    {
        return ImportTask::where('id', $id)->select($field)->first();
    }

    public function getTasks($schoolId=null)
    {
        if(empty($schoolId)) {
            return ImportTask::all();
        } else {
            return ImportTask::where('school_id', $schoolId)->get();
        }
    }

    public function writeLog($data)
    {
        $messageBag = new MessageBag(JsonBuilder::CODE_ERROR);
        DB::beginTransaction();
        try {
            $fillableData = $this->getFillableData(new ImportLog(), $data);
            $importLog    = ImportLog::create($fillableData);
            if ($importLog) {
                DB::commit();
                $messageBag->setCode(JsonBuilder::CODE_SUCCESS);
                $messageBag->setData($importLog);
            } else {
                DB::rollBack();
                $messageBag->setMessage('日志写入失败');
            }
        } catch (Exception $exception) {
            DB::rollBack();
            $messageBag->setMessage($exception->getMessage());
        }
        return $messageBag;
    }

    public function updateLog($data)
    {
        $messageBag = new MessageBag(JsonBuilder::CODE_ERROR);
        DB::beginTransaction();
        try {
            $fillableData = $this->getFillableData(new ImportLog(), $data);
            $importLog    = ImportLog::where('only_flag', md5($data['only_flag']))->update($fillableData);
            if ($importLog) {
                DB::commit();
                $messageBag->setCode(JsonBuilder::CODE_SUCCESS);
            } else {
                DB::rollBack();
                $messageBag->setMessage('日志更新失败');
            }
        } catch (Exception $exception) {
            DB::rollBack();
            $messageBag->setMessage($exception->getMessage());
        }
        return $messageBag;
    }

    public function getLog($onlyFlag) {
        return ImportLog::where('only_flag', $onlyFlag)->first();
    }

    public function result($id, $schoolId=null)
    {
        if (empty($schoolId)) {
            return ImportLog::where('task_id', $id)->where('task_status', ImportLog::ADMIN_FAIL_STATUS)->get();
        } else {
            return ImportLog::where('task_id', $id)->where('school_id', $schoolId)->where('task_status', ImportLog::FAIL_STATUS)->get();
        }
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

}
