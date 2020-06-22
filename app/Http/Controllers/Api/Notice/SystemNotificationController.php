<?php

namespace App\Http\Controllers\Api\Notice;

use App\Dao\Misc\SystemNotificationDao;
use App\Dao\Pipeline\UserFlowDao;
use App\Models\Misc\SystemNotification;
use App\Models\Misc\SystemNotificationsReadLog;
use App\Utils\JsonBuilder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SystemNotificationController extends Controller
{

    /**
     * 系统消息列表
     * @param Request $request
     * @return
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $dao = new SystemNotificationDao();
        $data = $dao->getNotificationByUser($user->getSchoolId(), $user)->toArray();
        foreach ($data['data'] as &$systemNotification) {
            $retExtra = [
                'type' => '',
                'param1' => '',
                'param2' => ''
            ];
            if (!empty($systemNotification['app_extra'])) {
                $extra = json_decode($systemNotification['app_extra'], true);
                $retExtra = [
                    'type' => $extra['type'],
                    'param1' => strval($extra['param1']),
                    'param2' => strval($extra['param2'])
                ];
            }
            $systemNotification['content'] = strip_tags($systemNotification['content']);
            $systemNotification['app_extra'] = $retExtra;
            $systemNotification['created_at'] = Carbon::parse($systemNotification['created_at'])->format('Y-m-d H:i');
        }
        //设置消息为已读
        $dao->setNotificationHasRead($user->getSchoolId(), $user);
        return JsonBuilder::Success($data);
    }


    /**
     * 教师pc端的消息中心
     * @param Request $request
     * @return string
     */
    public function newsList(Request $request) {
        $user = $request->user();
        $dao = new SystemNotificationDao();
        $category = SystemNotification::teacherPcNewsCategory();
        $schoolId = $user->getSchoolId();
        $result = $dao->getNewsByUser($schoolId, $user, $category, 10);
        // 查询用户的已读消息
        $readLogMaxId = 0;
        $readLog = SystemNotificationsReadLog::where('user_id', $user->id)->first();
        if(!is_null($readLog)) {
            $readLogMaxId = $readLog['system_notifications_maxid'];
        }
        $result = pageReturn($result);

        foreach ($result['list'] as $key => $item) {
            $userFlowId = $item->getUserFlowId();
            $item->flow_name = '';
            if(!is_null($userFlowId)) {
                $userFlowDao = new UserFlowDao();
                $userFlow = $userFlowDao->getById($userFlowId);
                $item->flow_name = $userFlow->flow->name;
            }

            $item->type = $item->getCategoryText();
            $item->url = $item->getCategoryUrl();
            if($item->id > $readLogMaxId) {
                $item->read = '未读';
            } else {
                $item->read = '已读';
            }
        }

        //设置消息为已读
        $dao->setNotificationHasRead($schoolId, $user);
        return JsonBuilder::Success($result);
    }


    /**
     * 消息详情
     * @param Request $request
     * @return string
     */
    public function getNoticeInfo(Request $request) {
        $noticeId = $request->get('notice_id');
        $dao = new SystemNotificationDao();
        $info = $dao->getNotificationInfo($noticeId);
        return JsonBuilder::Success($info);
    }


    /**
     * 未读消息数
     * @param Request $request
     * @return string
     */
    public function unreadNews(Request $request) {
        $user = $request->user();
        $schoolId = $user->getSchoolId();
        $dao = new SystemNotificationDao();
        $category = SystemNotification::teacherPcNewsCategory();
        $readLog = SystemNotificationsReadLog::where('user_id', $user->id)->first();
        $readLogMaxId = 0;
        if(!is_null($readLog)) {
            $readLogMaxId = $readLog['system_notifications_maxid'];
        }
        $unread = $dao->getNewsUnRead($schoolId, $user, $category, $readLogMaxId);
        return JsonBuilder::Success(['unread'=>$unread]);
    }
}
