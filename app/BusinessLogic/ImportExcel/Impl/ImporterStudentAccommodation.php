<?php


namespace App\BusinessLogic\ImportExcel\Impl;

use App\Dao\Importer\ImporterDao;
use App\Dao\Students\StudentAdditionInformationDao;
use App\Dao\Users\UserDao;
use App\Models\Importer\ImportLog;
use App\Models\Importer\ImportTask;
use Illuminate\Support\Facades\DB;

class ImporterStudentAccommodation extends AbstractImporter
{
    protected  $task;

    public function __construct($task)
    {
        $this->task = $task['data'];
        parent::__construct($task['data']['path']);
    }

    public function handle()
    {

        $userDao = new UserDao;
        $importDao = new ImporterDao;

        $additionDao = new StudentAdditionInformationDao;
        // 修改任务状态
        $importDao->update($this->task['id'], ['status' => ImportTask::IMPORT_TASK_EXECUTION]);

        $this->loadExcelFile();
        $sheetIndexArray = $this->getSheetIndexArray();

        // 再次验证文件格式
        $config = new ImporterConfig($this->fileRelativePath, $this->task['type']);
        $validation = $config->validation();
        if (!empty($validation)) {
            $error = [
                'task_id' => $this->task['id'],
                'error_log' => '文件格式错误'
            ];
            $this->errorLog($this->task['title'], $error);
            exit();
        }

        try {
            DB::beginTransaction();

            // 开始循环导入
            foreach ($sheetIndexArray as $sheetIndex) {
                echo '已拿到第' . ($sheetIndex + 1) . ' sheet的数据 开始循环.....' . PHP_EOL;
                $sheetData = $this->getSheetData($sheetIndex);
                unset($sheetData[0]); // 去掉文件头

                foreach ($sheetData as $key => $val) {
                    $errorArr = [
                        'task_id' => $this->task['id'],
                        'number' => $key+1,
                        'name' => $val[0],
                        'id_number' => '-',
                    ];
                    // 手机号不能为空
                    if (empty($val[1]) || strlen($val[1]) != 11) {
                        $errorArr['error_log'] = '手机号为空或者位数不对';
                        $this->errorLog($this->task['title'], $errorArr);
                        echo $val[0] . "手机号为空或者位数不对 跳过" . PHP_EOL;
                        continue;
                    }
                    // 根据手机号查找学生
                    $user = $userDao->getUserByMobile($val[1]);
                    if (empty($user)) {
                        $errorArr['error_log'] = '根据手机号未找到学生';
                        $this->errorLog($this->task['title'], $errorArr);
                        echo $val[0] . "根据手机号未找到学生 跳过" . PHP_EOL;
                        continue;
                    }
                    $info = $additionDao->getStudentAddInfoByUserId($user->id);
                    $infoData = [
                            'user_id' => $user->id,
                            'people' => $val[2],
                            'mobile' => $val[3],
                            'address' => $val[4],
                        ];
                    if (empty($info)) { // 没有数据 新增
                        $additionDao->create($infoData);
                        // 新增导入数量
                        $importDao->increment($this->task['id']);
                    } else { // 有的数据 更新
                        unset($infoData['user_id']);
                        $additionDao->update($user->id, $infoData);
                        // 新增导入数量
                        $importDao->increment($this->task['id']);
                    }
                }
            }

            // 统计未导入总数
            $count = ImportLog::where('task_id', $this->task['id'])->count();
            // 修改任务状态 和 未导入条数
            $importDao->update($this->task['id'], ['status' => ImportTask::IMPORT_TASK_COMPLETE, 'surplus' => $count]);
            DB::commit();
            echo $val[0] . '----------创建成功' . PHP_EOL;
        } catch (\Exception $exception) {
            DB::rollBack();
        }
    }

}
