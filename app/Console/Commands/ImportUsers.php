<?php

namespace App\Console\Commands;

use App\BusinessLogic\ImportExcel\Factory;
use App\Dao\Importer\ImporterDao;
use App\Models\Importer\ImportTask;
use Illuminate\Console\Command;

class ImportUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importer_users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '可用于导入无专业班级用户, 导入已认证用户, 导入学生住宿信息';

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
        $data = $dao->getTasksByStatus(ImportTask::IMPORT_TYPE_NO_IDENTITY);
        if (is_null($data)) {
            $this->info('未找到任务');die;
        }
        switch ($data['type'])
        {
            case ImportTask::IMPORT_TYPE_NO_IDENTITY:
                $adapter = 'import_users';
                break;
            case ImportTask::IMPORT_TYPE_CERTIFIED:
                $adapter = 'import_student';
                break;
            case ImportTask::IMPORT_TYPE_ADDITIONAL_INFORMATION:
                $adapter = 'importer_student_accommodation';
                break;
            default:
                $adapter = null;
                break;
        }
        $result = Factory::createAdapter(['adapter'=> $adapter , 'data'=> $data]);
        $result->handle();
        $this->info('任务结束');

    }
}
