<?php

namespace App\Console\Commands;

use App\BusinessLogic\ImportExcel\Factory;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UpdateTeacherPhone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:teacher_phone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '根据给定的exec文件, 根据教师名字修改手机号';

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
        $result = Factory::createAdapter(['importerName' => 'update_teacher_phone']);
        $result->handle();

        $this->info('任务结束');
    }


}
