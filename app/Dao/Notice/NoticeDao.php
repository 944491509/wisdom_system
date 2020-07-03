<?php

namespace App\Dao\Notice;

use App\Models\Notices\NoticeGrade;
use App\Utils\JsonBuilder;
use App\Models\Notices\Notice;
use App\Dao\NetworkDisk\MediaDao;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Notices\NoticeMedia;
use App\Utils\ReturnData\MessageBag;
use App\Utils\Misc\ConfigurationTool;
use App\Models\Notices\NoticeReadLogs;
use App\Models\Notices\NoticeOrganization;

class NoticeDao
{

    /**
     * 根据学校ID 获取通知
     * @param $where
     * @return mixed
     */
    public function getNoticeBySchoolId($where)
    {
        return Notice::where($where)
            ->with('attachments')
            ->with('selectedOrganizations')
            ->orderBy('id','desc')
            ->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);
    }

    public function getNoticeById($id)
    {
        return Notice::where('id', $id)
//            ->with('attachments')
//            ->with('selectedOrganizations')
//            ->with('grades')
            ->first();
    }

    /**
     * 添加
     * @param $data
     * @param $selectedOrganizations
     * @param $gradeIds
     * @return MessageBag
     */
    public function add($data, $selectedOrganizations, $gradeIds)
    {
        DB::beginTransaction();
        try{
            if(!empty($selectedOrganizations) && empty($gradeIds)) {
                $data['range'] = Notice::RANGE_TEACHER;
            } elseif(empty($selectedOrganizations) && !empty($gradeIds)) {
                $data['range'] = Notice::RANGE_STUDENT;
            } else {
                $data['range'] = Notice::RANGE_ALL;
            }
            $result = Notice::create($data);
            // 附件
            foreach ($data['attachments'] as $key => $val) {
                $insert = [
                    'notice_id' => $result->id,
                    'media_id'  => $val['id'],
                    'file_name' => $val['file_name'],
                    'url'       => $val['url'],
                ];
                NoticeMedia::create($insert);
            }

            // 教师的部门
            foreach ($selectedOrganizations as $item) {
                $insert = [
                    'school_id'=>$data['school_id'],
                    'notice_id'=>$result->id,
                    'organization_id'=>$item
                ];
                NoticeOrganization::create($insert);
            }

            // 学生查看通知范围
            foreach ($gradeIds as $key => $item) {
                $grade = [
                    'notice_id' => $result->id,
                    'grade_id' => $item,
                ];
                NoticeGrade::create($grade);
            }

            DB::commit();
            return new MessageBag(JsonBuilder::CODE_SUCCESS,'创建成功', $result);
        }catch (\Exception $e) {
            DB::rollBack();
            return new MessageBag(JsonBuilder::CODE_ERROR, $e->getMessage());
        }
    }

    /**
     * 修改
     * @param $data
     * @param $selectedOrganizations
     * @param $gradeIds
     * @return MessageBag
     */
    public function update($data, $selectedOrganizations, $gradeIds)
    {
        DB::beginTransaction();
        try{
            if(!empty($selectedOrganizations) && empty($gradeIds)) {
                $data['range'] = Notice::RANGE_TEACHER;
            } elseif(empty($selectedOrganizations) && !empty($gradeIds)) {
                $data['range'] = Notice::RANGE_STUDENT;
            } else {
                $data['range'] = Notice::RANGE_ALL;
            }
            $attachments = $data['attachments'];
            unset($data['attachments']);

            Notice::where('id', $data['id'])->update($data);
            // 重置附件
            NoticeMedia::where('notice_id', $data['id'])->delete();
            foreach ($attachments as $key => $val) {
                $insert = [
                    'notice_id' => $data['id'],
                    'media_id'  => $val['id'],
                    'file_name' => $val['file_name'],
                    'url'       => $val['url'],
                ];
                NoticeMedia::create($insert);
            }

            // 重置所有的通知关联的部门机构
            NoticeOrganization::where('notice_id',$data['id'])->delete();

            // 部分人员可见
            foreach ($selectedOrganizations as $item) {
                $insert = [
                    'school_id'=>$data['school_id'],
                    'notice_id'=>$data['id'],
                    'organization_id'=>$item
                ];
                NoticeOrganization::create($insert);
            }
            // 重置学生范围
            NoticeGrade::where('notice_id', $data['id'])->delete();
            // 学生查看通知范围
            foreach ($gradeIds as $key => $item) {
                $grade = [
                    'notice_id' => $data['id'],
                    'grade_id' => $item,
                ];
                NoticeGrade::create($grade);
            }
            DB::commit();
            return new MessageBag(JsonBuilder::CODE_SUCCESS,'编辑成功', Notice::where('id', $data['id'])->first());//?update 后如何直接返回对象
        }catch (\Exception $e) {
            DB::rollBack();
            return new MessageBag(JsonBuilder::CODE_ERROR, $e->getMessage());
        }
    }


    /**
     * 教师通知公告列表
     * @param $type
     * @param $schoolId
     * @param $organizationId
     * @return mixed
     */
    public function teacherGetNotice($type, $schoolId, $organizationId) {
        $field = ['notices.id', 'title', 'content', 'type', 'created_at',
            'inspect_id', 'image','status','notice_organizations.notice_id'];

        $now = Carbon::now()->toDateTimeString();
        array_push($organizationId, 0);
        $map = [
            ['notice_organizations.school_id', '=', $schoolId],
            ['type', '=', $type],
            ['release_time', '<', $now],
            ['status', '<>', Notice::STATUS_DELETE]
        ];
        return NoticeOrganization::join('notices', function ($join) use ($map, $organizationId) {
            $join->on('notice_organizations.notice_id', '=', 'notices.id')
                ->where($map)->WhereIn('notice_organizations.organization_id', $organizationId);
        })->select($field)
            ->orderBy('created_at', 'desc')
            ->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);

    }


    /**
     * 学生查看通知列表
     * @param $type
     * @param $gradeId
     * @return mixed
     */
    public function studentGetNotice($type, $gradeId) {
        $field = ['notices.id', 'title', 'content', 'type', 'created_at',
            'inspect_id', 'image','status', 'notice_grades.notice_id'];
        $now = Carbon::now()->toDateTimeString();
        $map = [
            ['type', '=', $type],
            ['release_time', '<', $now],
            ['status', '<>', Notice::STATUS_DELETE]
        ];

        return NoticeGrade::join('notices',function ($join) use($map, $gradeId) {
            $join->on('notice_grades.notice_id', '=', 'notices.id')
                ->where($map)->whereIn('notice_grades.grade_id', [$gradeId,0]);
        })->select($field)
            ->orderBy('created_at', 'desc')
            ->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);

    }

    /**
     * @param $id
     * @return mixed
     */
    public function deleteNoticeMedia($id){
        return NoticeMedia::where('id',$id)->delete();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function delete($id){
        $upd = ['status'=>Notice::STATUS_DELETE];
        return Notice::where('id',$id)->update($upd);
    }


    /**
     * 添加阅读记录
     * @param $data
     * @return mixed
     */
    public function addReadLog($data) {
        return NoticeReadLogs::create($data);
    }


    /**
     * 发布通知
     * @param $data
     * @param $organizationIds
     * @param $gradeIds
     * @param $file
     * @return MessageBag
     */
    public function issueNotice($data, $organizationIds, $gradeIds, $file) {

        $messageBag = new MessageBag();
        // 查询该公告标题是否已存在
        $map = ['school_id'=>$data['school_id'], 'title'=>$data['title']];
        $notice = Notice::where($map)->first();
        if(!is_null($notice)) {
            $messageBag->setCode(JsonBuilder::CODE_ERROR);
            $messageBag->setMessage('该标题已存在，请更换');
            return $messageBag;
        }
        $data['status'] = Notice::STATUS_PUBLISH;  // 设置为发布
        if(!empty($organizationIds) && empty($gradeIds)) {
            $data['range'] = Notice::RANGE_TEACHER;
        } elseif(empty($organizationIds) && !empty($gradeIds)) {
            $data['range'] = Notice::RANGE_STUDENT;
        } else {
            $data['range'] = Notice::RANGE_ALL;
        }

        try{
            DB::beginTransaction();
            $notice = Notice::create($data);

            // 教师查看通知范围
            foreach ($organizationIds as $key => $item) {
                $organization = [
                    'school_id' => $data['school_id'],
                    'notice_id' => $notice->id,
                    'organization_id' => $item
                ];
                NoticeOrganization::create($organization);
            }

            // 学生查看通知范围
            foreach ($gradeIds as $key => $item) {
                $grade = [
                    'notice_id' => $notice->id,
                    'grade_id' => $item,
                ];
                NoticeGrade::create($grade);
            }

            if($data['range'] == Notice::RANGE_ALL && empty($organizationIds) && empty($gradeIds)) {
                $organization = [
                    'school_id' => $data['school_id'],
                    'notice_id' => $notice->id,
                    'organization_id' => 0, // 所有部门都能看
                ];
                NoticeOrganization::create($organization);
                $grade = [
                    'notice_id' => $notice->id,
                    'grade_id' => 0, // 所有班级都能看
                ];
                NoticeGrade::create($grade);
            }

            if(!is_null($file)) {
                foreach ($file as $key => $value) {
                    $attachments = [
                        'notice_id' => $notice->id,
                        'media_id' => $value['media_id'],
                        'file_name' => $value['file_name'],
                        'url' => $value['url'],
                    ];
                    NoticeMedia::create($attachments);
                }
            }

            DB::commit();
            $messageBag->setMessage('提交成功');
            $messageBag->setData(['id'=>$notice->id]);

        } catch (\Exception $e) {
            DB::rollBack();
            $messageBag->setCode(JsonBuilder::CODE_ERROR);
            $messageBag->setMessage('提交失败.'.$e->getMessage());
        }
        return $messageBag;
    }


    /**
     * 后台通知消息列表
     * @param $data
     * @return mixed
     */
    public function adminNoticeList($data) {
        $map = [['school_id', '=', $data['school_id']]];
        // 类型
        if(!empty($data['type'])) {
            $map[] = ['type', '=', $data['type']];
        }
        // 范围
        if(!empty($data['range'])) {
            $map[] = ['range', '=', $data['range']];
        }
        if(!empty($data['keyword'])) {
            $map[] = ['title', 'like', '%'.$data['keyword'].'%'];
        }
        // 开始时间
        if(!empty($data['start_time']) && empty($data['end_time'])) {
            $map[] = ['release_time','>=', $data['start_time']];
        }
        // 结束时间
        if(empty($data['start_time']) && !empty($data['end_time'])) {
            $map[] = ['release_time', '<=', $data['end_time'].' '.'23:59:59'];
        }

        $result = Notice::where($map)
            ->orderBy('id', 'desc');
        if(!empty($data['start_time']) && !empty($data['end_time'])) {
            $result = $result->whereBetween('release_time', [$data['start_time'], $data['end_time'].' '.'23:59:59']);
        }
        return $result->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);
    }


    /**
     * 定时发布通知
     * @return mixed
     */
    public function getTimingSendNotice() {
        $start = Carbon::parse('- 5 minute')->toDateTimeString();
        $end = Carbon::now()->toDateTimeString();
        return Notice::where('status', Notice::STATUS_UNPUBLISHED)
            ->whereBetween('release_time',[$start, $end])
            ->get();
    }


}
