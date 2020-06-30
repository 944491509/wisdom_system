<?php

namespace App\Events\SystemNotification;

use App\Events\CanSendSystemNotification;
use App\Models\Notices\Notice;
use App\Models\Misc\SystemNotification;
use App\Models\Notices\NoticeGrade;
use App\Models\Notices\NoticeOrganization;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class NoticeSendEvent implements CanSendSystemNotification
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private  $notice;

    /**
     * NoticeSendEvent constructor.
     * @param Notice $notice
     */
    public function __construct(Notice $notice)
    {
        $this->notice = $notice;
    }

    /**
     * 必须可以拿到接收学校
     * @return int
     */
    public function getSchoolId(): int
    {
        return $this->notice->school_id;
    }

    /**
     * 可以拿到接收者
     * @return int
     */
    public function getTo(): int
    {
        switch ($this->notice->range) {
            case Notice::RANGE_ALL: return SystemNotification::TO_ALL;break;
            case Notice::RANGE_TEACHER: return SystemNotification::TO_TEACHER;break;
            case Notice::RANGE_STUDENT:return SystemNotification::TO_STUDENT;break;
        }
    }

    /**
     * 必须可以拿到发送者
     * @return int
     */
    public function getSender(): int
    {
        return SystemNotification::FROM_SYSTEM;//系统
    }

    /**
     * 必须可以拿到消息类别
     * @return int
     */
    public function getType(): int
    {
        return SystemNotification::TYPE_NONE;
    }

    /**
     * 必须可以拿到消息级别
     * @return int
     */
    public function getPriority(): int
    {
        return SystemNotification::PRIORITY_LOW;
    }
    /**
     * 必须可以拿到发送标题
     * @return string
     */
    public function getTitle(): string
    {
        return '你有一条新的' . Notice::allType()[$this->notice->type]. '！';
    }

    /**
     * 必须可以拿到发送内容
     * @return string
     */
    public function getContent(): string
    {
        return $this->notice->title;
    }

    /**
     * 必须可以拿到消息分类
     * @return int
     */
    public function getCategory(): int
    {
        return SystemNotification::getNoticeTypeToCategory()[$this->notice->type];
    }

    /**
     * 必须可以拿到前端所需key
     * @return string
     */
    public function getAppExtra(): string
    {
        $extra = [
            'type' => 'notice-info',
            'param1' => $this->notice->id,
            'param2' => ''
        ];
        return json_encode($extra);
    }

    /**
     * 必须可以拿到下一步
     * @return string
     */
    public function getNextMove(): string
    {
        return '';
    }

    /**
     * 必须可以拿到组织id
     * @return array
     */
    public function getOrganizationIdArray(): array
    {
        return NoticeOrganization::where('notice_id', '=', $this->notice->id)->pluck('organization_id')->toArray();
    }


    /**
     * 必须可以拿到班级id
     * @return array
     */
    public function getGradeIdArray(): array
    {
        return NoticeGrade::where('notice_id', '=', $this->notice->id)->pluck('grade_id')->toArray();
    }


}
