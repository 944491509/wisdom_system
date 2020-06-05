<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 25/11/19
 * Time: 2:10 PM
 */

namespace App\Dao\OA;


use App\Models\Acl\Role;
use App\Utils\JsonBuilder;
use App\Models\OA\Project;
use App\Models\OA\ProjectTask;
use App\Models\Users\GradeUser;
use App\Models\OA\ProjectMember;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\OA\ProjectTaskMember;
use App\Utils\ReturnData\MessageBag;
use App\Utils\Misc\ConfigurationTool;
use App\Models\OA\ProjectTaskDiscussion;

class ProjectDao
{
    public function __construct()
    {
    }


    /**
     * 根据学校的 id 获取项目列表
     * @param $schoolId
     * @param null $keyword
     * @return mixed
     */
    public function getProjectsPaginateBySchool($schoolId, $keyword = null)
    {

        $result = Project::where('school_id', $schoolId)->orderBy('created_at', 'desc');
        if (!empty($keyword)) {
            $result = $result->where('title', 'like', '%' . $keyword . '%');
        }
        return $result->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);
    }


    /**
     * 获取项目列表
     * @param $schoolId
     * @return mixed
     */
    public function getProjects($schoolId) {
        return Project::where('school_id', $schoolId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * @param $id
     * @return Project
     */
    public function getProjectById($id)
    {
        return Project::find($id);
    }

    /**
     * @param $taskId
     * @return ProjectTask
     */
    public function getProjectTaskById($taskId)
    {
        return ProjectTask::find($taskId);
    }

    /**
     * 根据项目 ID 获取任务列表
     * @param $projectId
     * @return Collection
     */
    public function getTasksPaginateByProject($projectId)
    {
        if ($projectId) {
            return ProjectTask::where('project_id', $projectId)
                ->orderBy('id', 'desc')
                ->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);
        } else {
            return ProjectTask::orderBy('id', 'desc')
                ->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);
        }
    }

    /**
     * 通过title查询项目
     * @param $title
     * @param $schoolId
     * @return mixed
     */
    public function getProjectByTitle($title, $schoolId)
    {
        $map = ['title' => $title, 'school_id' => $schoolId];
        return Project::where($map)->first();
    }


    /**
     * 创建项目
     * @param array $project
     * @param null $member
     * @return MessageBag
     */
    public function createProject($project, $member = null)
    {
        $messageBag = new MessageBag(JsonBuilder::CODE_ERROR);
        $re = $this->getProjectByTitle($project['title'], $project['school_id']);
        if (!is_null($re)) {
            $messageBag->setMessage('该项目已存在,请重新更换');
            return $messageBag;
        }
        DB::beginTransaction();
        try {
            $s1 = Project::create($project);
            if (!empty($member)) {
                foreach ($member as $key => $val) {
                    $user = [
                        'user_id' => intval($val),
                        'project_id' => $s1->id
                    ];
                    ProjectMember::create($user);
                }
            }
            DB::commit();
            $messageBag->setData(['id' => $s1->id]);
            $messageBag->setCode(JsonBuilder::CODE_SUCCESS);
        } catch (\Exception $e) {
            DB::rollBack();
            $msg = $e->getMessage();
            $messageBag->setMessage($msg);
        }
        return $messageBag;
    }


    /**
     * 项目列表
     * @param $userId
     * @return mixed
     */
    public function getProjectByUserId($userId)
    {
        return Project::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);
    }


    /**
     * 通过任务ID获取评论
     * @param $taskId
     * @return mixed
     */
    public function getDiscussionByTaskId($taskId)
    {
        return ProjectTaskDiscussion::where('project_task_id', $taskId)->get();
    }


    /**
     * 编辑项目
     * @param $projectId
     * @param $project
     * @return MessageBag
     */
    public function updateProject($projectId, $project)
    {
        $messageBag = new MessageBag(JsonBuilder::CODE_ERROR);
        $map = [
            ['id', '<>', $projectId],
            ['title', '=', $project['title']],
            ['school_id', '=', $project['school_id']]
        ];
        $re = Project::where($map)->first();
        if (!empty($re)) {
            $messageBag->setMessage('该项目标题已存在,请重新更换');
            return $messageBag;
        }

        $result = Project::where('id', $projectId)->update($project);
        if ($result) {
            $messageBag->setCode(JsonBuilder::CODE_SUCCESS);
            $messageBag->setMessage('编辑成功');
            return $messageBag;
        } else {
            $messageBag->setMessage('编辑失败');
            return $messageBag;
        }
    }

    /**
     * 我创建的任务列表
     * @param $userId
     * @return mixed
     */
    public function myCreateTasks($userId)
    {
        // 修改阅读状态
        $map = ['create_user'=>$userId,'status'=>ProjectTask::STATUS_CLOSED];
        $save = ['read_status'=>ProjectTask::READ];
        ProjectTask::where($map)->update($save);

        $where = ['create_user' => $userId];
        return ProjectTask::where($where)
            ->orderBy('id', 'desc')
            ->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);
    }


    /**
     * 参加的任务
     * @param $userId
     * @param $type
     * @return mixed
     */
    public function attendTasks($userId, $type)
    {
        $map = ['user_id' => $userId, 'status' => $type];
        $this->readTask($userId,$type);
        return ProjectTaskMember::where($map)
            ->orderBy('created_at', 'desc')
            ->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);
    }


    /**
     * 阅读任务
     * @param $userId
     * @param $type
     * @return mixed
     */
    public function readTask($userId, $type) {
        $map = ['user_id'=>$userId, 'status'=>$type];
        // 未开始
        if($type == ProjectTaskMember::STATUS_UN_BEGIN) {
            $map['not_begin'] = 0;
            $save = ['not_begin'=>1];
        }
        // 进行中
        if($type == ProjectTaskMember::STATUS_IN_PROGRESS) {
            $map['underway'] = 0;
            $save = ['underway'=>1];
        }
        // 已结束
        if($type == ProjectTaskMember::STATUS_CLOSED) {
            $map['finish'] = 0;
            $save = ['finish'=>1];
        }

        return ProjectTaskMember::where($map)->update($save);
    }



    public function updateMembers($projectId, $member)
    {
        $messageBag = new MessageBag(JsonBuilder::CODE_ERROR);
        $s1 = $this->getProjectById($projectId);
        DB::beginTransaction();
        try {
            if (!empty($member)) {
                foreach ($member as $key => $val) {
                    $user = [
                        'user_id' => $val,
                        'project_id' => $s1->id
                    ];
                    ProjectMember::create($user);
                }
            }
            DB::commit();
            $messageBag->setData(['id' => $s1->id]);
            $messageBag->setCode(JsonBuilder::CODE_SUCCESS);
        } catch (\Exception $e) {
            DB::rollBack();
            $msg = $e->getMessage();
            $messageBag->setMessage($msg);
        }
    }

    public function getTeachers($name, $schoolId)
    {
        return GradeUser::select(DB::raw('user_id, name'))
            ->whereIn('user_type', [Role::TEACHER, Role::EMPLOYEE])
            ->where('school_id', $schoolId)
            ->where('name', 'like', $name . '%')->get();
    }
}
