<?php

namespace App\Jobs\Notifier;

use App\Models\Acl\Role;
use App\Models\Misc\SystemNotification;
use App\Models\Users\GradeUser;
use App\Models\Users\UserOrganization;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Utils\Misc\JPushFactory;

class Push implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $title;

    protected $content;

    protected $extra;

    protected $from;

    protected $to;

    protected $organizations;

    /**
     * Push constructor.
     * @param $data
     */
    public function __construct($title, $content, $extra, $from, $to, $organizationIdArray)
    {
        $this->title = $title;
        $this->content = mb_substr(strip_tags($content), 0, 40);
        if (empty($extra)) {
            $this->extra = [
                'type' => '',
                'param1' => '',
                'param2' => ''
            ];
        }else {
            $extra = json_decode($extra, true);
            $this->extra = [
                'type' => $extra['type'],
                'param1' => strval($extra['param1']),
                'param2' => strval($extra['param2'])
            ];
        }
        $this->from = $from;
        $this->to = $to;
        $this->organizations = $organizationIdArray;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if(env('APP_DEBUG', false)){
            Log::info('推消息',['msg'=> $this->title,'content'=>$this->content, 'extra' => $this->extra, 'from' => $this->from, 'to' => $this->to, 'organizations' => $this->organizations]);
        }
        else{
            if ($this->to == SystemNotification::TO_STUDENT) {
                $users = GradeUser::whereIn('user_type', [Role::VERIFIED_USER_STUDENT, Role::REGISTERED_USER]);
            }elseif ($this->to == SystemNotification::TO_TEACHER) {
                $users = GradeUser::where('user_type', '=', Role::TEACHER);
            }elseif ($this->to > 0) {
                $users = GradeUser::where('user_id', '=', $this->to);
            }else {
                if (!empty($this->organizations) && !(count($this->organizations) == 1 && $this->organizations[0] == 0)) {
                    $organizationUserId = UserOrganization::whereIn('organization_id', $this->organizations)->pluck('user_id')->toArray();
                    $users = GradeUser::whereIn('user_id', $organizationUserId);
                }else {
                    // 直接$users = GradeUser;报错
                    $users = GradeUser::where('id', '>', 0);
                }
            }
            if ($this->from != SystemNotification::SCHOOL_EMPTY) {
                $users->where('school_id', '=', $this->from);
            }
            $push = JPushFactory::GetInstance();
            $push->send($users->get(), $this->title, $this->content, $this->extra);
        }
    }
}
