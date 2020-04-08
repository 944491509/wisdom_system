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
                $users = GradeUser::whereIn('user_type', [Role::VERIFIED_USER_STUDENT_SLUG, Role::VERIFIED_USER_CLASS_LEADER_SLUG, Role::VERIFIED_USER_CLASS_SECRETARY_SLUG]);
            }elseif ($this->to == SystemNotification::TO_TEACHER) {
                $users = GradeUser::where('user_type', '=', Role::TEACHER);
            }elseif ($this->to > 0) {
                $users = GradeUser::where('user_id', '=', $this->to);
            }else {
                $users = GradeUser;
                if (!empty($this->organizations)) {
                    $organizationUserId = UserOrganization::whereIn('id', $this->organizations)->pluck('id')->toArray();
                    $users->whereIn('user_id', $organizationUserId);
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
