<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 7/11/19
 * Time: 7:18 PM
 */

namespace App\Dao\Misc;

use App\Models\Misc\SystemNotification;
use App\Models\Misc\SystemNotificationsOrganization;
use App\Models\Misc\SystemNotificationsReadLog;
use App\Utils\JsonBuilder;
use App\Utils\Misc\ConfigurationTool;
use App\Utils\ReturnData\MessageBag;
use Illuminate\Support\Facades\DB;

class SystemNotificationDao
{

    public function __construct()
    {
    }

    /**
     * 创建系统消息
     * @param $data
     * @return SystemNotification
     */
    public function create($data, $organizationIdArray){
        DB::beginTransaction();
        try{
            $result = SystemNotification::create($data);
            // 这里假定没有"全部"这个选项， 以后处理
            if (!empty($organizationIdArray)) {
                foreach ($organizationIdArray as $organizationId) {
                    $insert = [
                        'system_notifications_id' => $result->id,
                        'organization_id' => $organizationId
                    ];
                    SystemNotificationsOrganization::create($insert);
                }
            }else {
                //不限制组织填0
                $insert = [
                    'system_notifications_id' => $result->id,
                    'organization_id' => 0
                ];
                SystemNotificationsOrganization::create($insert);
            }
            DB::commit();
            return new MessageBag(JsonBuilder::CODE_SUCCESS,'创建成功', $result);
        }catch (\Exception $e) {
            DB::rollBack();
            return new MessageBag(JsonBuilder::CODE_ERROR, $e->getMessage());
        }
    }


  /**
   * Func 查询系统通知消息
   *
   * @param $school_id 学校id
   * @param $keywords 搜索关键词
   *
   * @return object
   */
  public function getNotificationList($param = [], $page = 1 )
  {
    $condition[] = ['uuid', '<>', ''];
    if ($param['school_id']) {
      $condition[] = ['school_id', '=', $param['school_id']];
    }
    if ($param['keywords']) {
      $condition[] = ['title', 'like', '%'.trim($param['keywords']).'%'];
    }
    return SystemNotification::where($condition)
      ->whereIn('to',$param['to'])
      ->orderBy('id', 'desc')
      ->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE, ['*'], 'page', $page);
  }

    /**
     * Func 查询系统通知详情
     *
     * @param $uuid uuid
     *
     * @return object
     */
    public function getNotificationOne($param = [])
    {
      $condition[] = ['uuid', '=', $param['uuid']];
      return SystemNotification::where($condition)->first();
    }

    /**
     * 根据学校ID 获取通知
     *
     * @param $schoolId
     * @param $user
     * @param int $pageSize
     * @return mixed
     */
    public function getNotificationByUser($schoolId, $user, $pageSize=ConfigurationTool::DEFAULT_PAGE_SIZE)
    {
       return  $this->_build($schoolId, $user)->orderBy('priority','desc')
        ->orderBy('id','desc')
        ->simplePaginate($pageSize);
    }


    /**
     * 教师pc端查看消息列表
     * @param $schoolId
     * @param $user
     * @param $category
     * @param int $pageSize
     * @return mixed
     */
    public function getNewsByUser($schoolId, $user, $category, $pageSize=ConfigurationTool::DEFAULT_PAGE_SIZE) {
        $field = ['id', 'content', 'category', 'created_at', 'title', 'content', 'app_extra'];
        return  $this->_build($schoolId, $user)->whereIn('category', $category)
            ->orderBy('priority','desc')
            ->orderBy('id','desc')
            ->select($field)
            ->paginate($pageSize);
    }

    /**
     * 查询消息未读数
     * @param $schoolId
     * @param $user
     * @param $category
     * @param $readLogMaxId
     * @return mixed
     */
    public function getNewsUnRead($schoolId, $user, $category, $readLogMaxId) {
        return $this->_build($schoolId, $user, $readLogMaxId)
            ->whereIn('category', $category)
            ->select('id')
            ->count();
    }



    /**
     * 设置消息为已读
     * @param $schoolId
     * @param $userId
     * @return bool
     */
    public function setNotificationHasRead($schoolId, $user){
        $maxNotificationId = $this->_build($schoolId, $user)->max('id');
        return $maxNotificationId && SystemNotificationsReadLog::updateOrCreate(['user_id' => $user->id], ['system_notifications_maxid' => $maxNotificationId]);
    }

    /**
     * 检查消息是否已读
     * @param $schoolId
     * @param $user
     * @return bool
     */
    public function checkNotificationHasRead($schoolId, $user) {
        $maxNotificationId = $this->_build($schoolId, $user)->max('id');
        $readLogMaxId = SystemNotificationsReadLog::where('user_id', $user->id)->value('system_notifications_maxid');
        return $readLogMaxId >= $maxNotificationId;
    }

    /**
     * 通用的查看自己消息的sql
     * @param $schoolId
     * @param $user
     * @param $readLogMaxId
     * @return mixed
     */
    private function _build($schoolId, $user, $readLogMaxId = 0) {
        $organizationId = $user->organizations->pluck('organization_id')->toArray();
        array_push($organizationId, 0);

        $toArr = [0];//广播的消息
        if ($user->isStudent()) {
            $toArr[] = SystemNotification::TO_STUDENT;
        }
        if ($user->isTeacher()) {
            $toArr[] = SystemNotification::TO_TEACHER;
        }
        return SystemNotification::where(function ($query) use ($organizationId, $toArr, $readLogMaxId) {
            // 1: 系统发出的消息, 此类消息 school_id 为 0, 表示任何学校的用户都可以接收
            $query->where('school_id', 0)->where('id', '>', $readLogMaxId)->whereIn('to', $toArr)
                ->whereHas('systemNotificationsOrganizations', function($q) use($organizationId) {
                $q->whereIn('system_notifications_organizations.organization_id', $organizationId);
            });
        })->orWhere(function ($query) use($schoolId, $organizationId, $toArr, $readLogMaxId){
            // 2: 学校发出的消息, to 的值为 0, 表示该学校的所有的用户都可以收到
            $query->where('school_id', $schoolId)->where('id', '>', $readLogMaxId)->whereIn('to', $toArr)
                ->whereHas('systemNotificationsOrganizations', function($q) use($organizationId) {
                $q->whereIn('system_notifications_organizations.organization_id', $organizationId);
            });
        })->orWhere(function ($query) use($user, $readLogMaxId){
            // 3: to 的值为 user id, 表示发给自己的消息
            $query->where('to',$user->id)->where('id', '>', $readLogMaxId);
        });
    }


    /**
     * 获取消息详情
     * @param $notificationId
     * @return mixed
     */
    public function getNotificationInfo($notificationId) {
        $map = ['id' => $notificationId];
        $field = ['id', 'content', 'category', 'created_at', 'title', 'content'];
        return SystemNotification::where($map)
            ->select($field)
            ->first();
    }
}
