<?php

namespace App\Console\Commands;

use App\Dao\Misc\SystemNotificationDao;
use App\Dao\Notice\NoticeDao;
use App\Events\SystemNotification\NoticeSendEvent;
use Illuminate\Console\Command;

class SendNotice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SendNotice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '通知公告的定时发送';

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
        //
        $noticeDao = new NoticeDao();
        $list = $noticeDao->getTimingSendNotice();
        $systemNoticeDao =  new SystemNotificationDao();
        foreach ($list as $key => $item) {
            $re = $systemNoticeDao->getNotificationByNoticeId($item->id);
            if(is_null($re)) {
                event(new NoticeSendEvent($item));
            }
        }
    }
}
