<?php
/**
 * 这个是系统内部发送消息的类
 */
namespace App\Jobs\Notifier;

use App\Dao\Misc\SystemNotificationDao;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;

class InternalMessage
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $schoolId;
    protected $from;
    protected $to;
    protected $type;
    protected $priority;
    protected $content;
    protected $nextMove;
    protected $title;
    protected $category;
    protected $appExtra;
    protected $organizationIdArray;
    protected $gradeIdArray;

    /**
     * InternalMessage constructor.
     * @param $schoolId
     * @param $from
     * @param $to
     * @param $type
     * @param $priority
     * @param $content
     * @param null $nextMove
     * @param string $title
     * @param int $category
     * @param string $appExtra
     * @param array $organizationIdArray
     * @param array $gradeIdArray
     */
    public function __construct(
        $schoolId,
        $from,
        $to,
        $type,
        $priority,
        $content,
        $nextMove = null,
        $title = '',
        $category = 0,
        $appExtra = '',
        $organizationIdArray = [],
        $gradeIdArray = []
    ){
        $this->schoolId = $schoolId;
        $this->from = $from;
        $this->to = $to;
        $this->type = $type;
        $this->priority = $priority;
        $this->content = $content;
        $this->nextMove = $nextMove;
        $this->title = $title;
        $this->category = $category;
        $this->appExtra = $appExtra;
        $this->organizationIdArray = $organizationIdArray;
        $this->gradeIdArray = $gradeIdArray;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $dao = new SystemNotificationDao();
            $dao->create([
                'sender'=>$this->from,
                'to'=>$this->to,
                'type'=>$this->type,
                'priority'=>$this->priority,
                'school_id'=>$this->schoolId,
                'title'=>$this->title,
                'content'=>$this->content,
                'next_move'=>$this->nextMove,
                'category' =>$this->category,
                'app_extra' =>$this->appExtra,
            ], $this->organizationIdArray, $this->gradeIdArray);
        }catch (\Exception $exception){
            Log::alert('创建系统消息失败',['msg'=>$exception->getMessage()]);
        }

        Push::dispatch($this->title, $this->content, $this->appExtra, $this->schoolId, $this->to, $this->organizationIdArray);
    }
}
