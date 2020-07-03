<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 7/11/19
 * Time: 7:18 PM
 */

namespace App\Dao\Misc;

use App\Models\Misc\SystemNotification;
use App\Models\Misc\SystemNotificationsGrades;
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
     * @param $organizationIdArray
     * @param $gradeIdArr
     * @return MessageBag
     */
    public function create($data, $organizationIdArray, $gradeIdArr){
        DB::beginTransaction();
        try{
            $result = SystemNotification::create($data);
            // 判断to的类型
            if($data['to'] == SystemNotification::PRIORITY_LOW) {
                // 学生和老师都创建
                $this->createTeacherNotice($organizationIdArray, $result->id);
                $this->createStudentNotice($gradeIdArr, $result->id);
            } elseif($data['to'] == SystemNotification::TO_TEACHER) {
                // 创建老师消息
                $this->createTeacherNotice($organizationIdArray, $result->id);
            } else {
                // 创建学生消息
                $this->createStudentNotice($gradeIdArr, $result->id);
            }

            DB::commit();
            return new MessageBag(JsonBuilder::CODE_SUCCESS,'创建成功', $result);
        }catch (\Exception $e) {
            DB::rollBack();
            return new MessageBag(JsonBuilder::CODE_ERROR, $e->getMessage());
        }
    }


    /**
     * 创建老师系统消息
     * @param $organizationIdArray
     * @param $systemNotificationsId
     */
    public function createTeacherNotice($organizationIdArray, $systemNotificationsId) {
        if (!empty($organizationIdArray)) {
            foreach ($organizationIdArray as $organizationId) {
                $insert = [
                    'system_notifications_id' => $systemNotificationsId,
                    'organization_id' => $organizationId
                ];
                SystemNotificationsOrganization::create($insert);
            }
        }else {
            //不限制组织填0
            $insert = [
                'system_notifications_id' => $systemNotificationsId,
                'organization_id' => 0
            ];
            SystemNotificationsOrganization::create($insert);
        }
    }


    /**
     * 创建学生消息
     * @param $gradeIdArr
     * @param $systemNotificationsId
     */
    public function createStudentNotice($gradeIdArr, $systemNotificationsId) {
        if(!empty($gradeIdArr)) {
            foreach ($gradeIdArr as $gradeId) {
                $insert = [
                    'system_notifications_id' => $systemNotificationsId,
                    'grade_id' => $gradeId
                ];
                SystemNotificationsGrades::create($insert);
            }
        } else {
            $insert = [
                'system_notifications_id' => $systemNotificationsId,
                'grade_id' => 0,
            ];
            SystemNotificationsGrades::create($insert);
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
        return $this->_build($schoolId, $user,0,$category)
//            ->whereIn('category', $category)
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
        return $this->_build($schoolId, $user, $readLogMaxId, $category)
            ->count();
    }



    /**
     * 设置消息为已读
     * @param $schoolId
     * @param $user
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
     * @param $category
     * @return mixed
     */
    private function _build($schoolId, $user, $readLogMaxId = 0, $category = []) {

        $organizationId = [];
        $gradeId = [];
        $toArr = [0];//广播的消息
        if ($user->isStudent()) {
            $toArr[] = SystemNotification::TO_STUDENT;
            $gradeId = $user->gradeUser->pluck('grade_id')->toArray();
            array_push($gradeId, 0);
        }
        if ($user->isTeacher()) {
            $toArr[] = SystemNotification::TO_TEACHER;
            $organizationId = $user->organizations->pluck('organization_id')->toArray();
            array_push($organizationId, 0);
        }
        return SystemNotification::where(function ($query) use ($organizationId, $gradeId, $user, $toArr, $readLogMaxId,$category) {
            // 1: 系统发出的消息, 此类消息 school_id 为 0, 表示任何学校的用户都可以接收
            $query->where('school_id', 0)
                ->where('created_at','>', $user->created_at)
                ->where('id', '>', $readLogMaxId)
                ->whereIn('to', $toArr);
            if($user->isTeacher()) {
                $query->whereHas('systemNotificationsOrganizations', function($q) use($organizationId) {
                    $q->whereIn('system_notifications_organizations.organization_id', $organizationId);
                });
            }
            if($user->isStudent()) {
                $query->whereHas('systemNotificationsGrades', function ($que) use ($gradeId) {
                    $que->whereIn('system_notifications_grades.grade_id', $gradeId);
                });
            }
            if(!empty($category)) {
                $query->whereIn('category', $category);
            }
        })->orWhere(function ($query) use($schoolId, $organizationId, $gradeId, $user, $toArr, $readLogMaxId, $category){
            // 2: 学校发出的消息, to 的值为 0, 表示该学校的所有的用户都可以收到
            $query->where('school_id', $schoolId)
                ->where('id', '>', $readLogMaxId)
                ->where('created_at','>', $user->created_at)
                ->whereIn('to', $toArr);
            if($user->isTeacher()) {
                $query->whereHas('systemNotificationsOrganizations', function($q) use($organizationId) {
                    $q->whereIn('system_notifications_organizations.organization_id', $organizationId);
                });
            }
            if($user->isStudent()) {
                $query->whereHas('systemNotificationsGrades', function ($que) use ($gradeId) {
                    $que->whereIn('system_notifications_grades.grade_id', $gradeId);
                });
            }
            if(!empty($category)) {
                $query->whereIn('category', $category);
            }
        })->orWhere(function ($query) use($user, $readLogMaxId, $category){
            // 3: to 的值为 user id, 表示发给自己的消息
            $query->where('to',$user->id)->where('id', '>', $readLogMaxId)->where('created_at','>', $user->created_at);
            if(!empty($category)) {
                $query->whereIn('category', $category);
            }
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


    /**
     * 根据通知公告id查询系统消息
     * @param $noticeId
     * @return mixed
     */
    public function getNotificationByNoticeId($noticeId) {
        $map = [
            'category' => SystemNotification::COMMON_CATEGORY_NOTICE_NOTIFY,
            'app_extra->param1' => $noticeId
        ];
        return SystemNotification::where($map)->first();
    }
}
