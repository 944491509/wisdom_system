<?php

namespace App\Console\Commands;

use App\BusinessLogic\ImportExcel\Factory;
use Illuminate\Console\Command;

class ImporterStudentAccommodation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'importer:student_accommodation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '根据根据学生姓名导入住宿信息';

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
        $result = Factory::createAdapter();
        $result->handle();
        $this->info('任务结束');
    }
}
