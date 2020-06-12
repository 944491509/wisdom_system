<?php

namespace App\Console\Commands;

use App\BusinessLogic\ImportExcel\Factory;
use App\Dao\Importer\ImporterDao;
use App\Dao\Users\UserDao;
use App\Models\Importer\ImportTask;
use Illuminate\Console\Command;

class importStudent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importStudent';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '导入带专业班级的学生';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('任务开始');
        $dao = new ImporterDao;
        // 正在执行的导入任务
        $execution = $dao->getTasksByStatus(ImportTask::IMPORT_TASK_EXECUTION_TEXT);
        if (!is_null($execution)) {
            $this->info('已经有正在执行的导入任务了......');
            exit();
        }
        // 等待中的任务
        $waiting = $dao->getTasksByStatus(ImportTask::IMPORT_TASK_WAITING);
        if ($waiting) {
            $result = Factory::createAdapter('import_student');
            $result->handle();
        }
        $this->info('任务结束');
    }

}
