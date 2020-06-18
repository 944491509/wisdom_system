<?php

namespace App\Console\Commands;

use App\BusinessLogic\ImportExcel\Factory;
use App\Dao\Importer\ImporterDao;
use App\Models\Importer\ImportTask;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

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
    protected $description = '导入无专业班级用户';

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
        $result = Factory::createAdapter(['adapter'=> 'import_users' , 'data'=> $data]);
        $result->handle();
        $this->info('任务结束');

    }
}
