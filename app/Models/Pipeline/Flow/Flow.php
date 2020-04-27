<?php

namespace App\Models\Pipeline\Flow;

use App\Dao\Pipeline\ActionDao;
use App\Dao\Pipeline\FlowDao;
use App\Dao\Schools\OrganizationDao;
use App\Models\Schools\Organization;
use App\Models\Teachers\Teacher;
use App\Models\Users\GradeUser;
use App\User;
use App\Utils\Misc\Contracts\Title;
use App\Utils\Pipeline\IFlow;
use App\Utils\Pipeline\INode;
use App\Utils\Pipeline\IUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Utils\Pipeline\IAction;

class Flow extends Model implements IFlow
{
    use SoftDeletes;
    public $table = 'pipeline_flows';
    public $timestamps = false;
    protected $fillable = [
        'school_id','name','type','icon','copy_uids','business','auto_processed'
    ];

    /**
     * 流程的所有分类
     */
    public static function Types(){
        return [
            IFlow::TYPE_1_01 => IFlow::TYPE_1_01_TXT,
            IFlow::TYPE_1_02 => IFlow::TYPE_1_02_TXT,
            IFlow::TYPE_1_03 => IFlow::TYPE_1_03_TXT,
            IFlow::TYPE_1_04 => IFlow::TYPE_1_04_TXT,
            IFlow::TYPE_1_05 => IFlow::TYPE_1_05_TXT,
            IFlow::TYPE_1_06 => IFlow::TYPE_1_06_TXT,

            IFlow::TYPE_2_01 => IFlow::TYPE_2_01_TXT,
            IFlow::TYPE_2_02 => IFlow::TYPE_2_02_TXT,
            IFlow::TYPE_2_03 => IFlow::TYPE_2_03_TXT,
            IFlow::TYPE_2_04 => IFlow::TYPE_2_04_TXT,

            IFlow::TYPE_3_01 => IFlow::TYPE_3_01_TXT,
            IFlow::TYPE_3_02 => IFlow::TYPE_3_02_TXT,
        ];
    }

    //根据分类获取位置
    public static function getPositionByType($type) {
        $position = null;
        switch ($type) {
            case IFlow::TYPE_1_01:
            case IFlow::TYPE_1_02:
            case IFlow::TYPE_1_03:
            case IFlow::TYPE_1_04:
            case IFlow::TYPE_1_05:
            case IFlow::TYPE_1_06:
                $position = IFlow::POSITION_1;
                break;
            case IFlow::TYPE_2_01:
            case IFlow::TYPE_2_02:
            case IFlow::TYPE_2_03:
            case IFlow::TYPE_2_04:
                $position = IFlow::POSITION_2;
                break;
            case IFlow::TYPE_3_01:
            case IFlow::TYPE_3_02:
                $position = IFlow::POSITION_3;
                break;
            default:
                break;
        }
        return $position;
    }
    //指定位置的分类
    public static function getTypesByPosition($position) {
        if ($position == IFlow::POSITION_1) {
            return [
                IFlow::TYPE_1_01 => IFlow::TYPE_1_01_TXT,
                IFlow::TYPE_1_02 => IFlow::TYPE_1_02_TXT,
                IFlow::TYPE_1_03 => IFlow::TYPE_1_03_TXT,
                IFlow::TYPE_1_04 => IFlow::TYPE_1_04_TXT,
                IFlow::TYPE_1_05 => IFlow::TYPE_1_05_TXT,
                IFlow::TYPE_1_06 => IFlow::TYPE_1_06_TXT,
            ];
        }
        if ($position == IFlow::POSITION_2) {
            return [
                IFlow::TYPE_2_01 => IFlow::TYPE_2_01_TXT,
                IFlow::TYPE_2_02 => IFlow::TYPE_2_02_TXT,
                IFlow::TYPE_2_03 => IFlow::TYPE_2_03_TXT,
                IFlow::TYPE_2_04 => IFlow::TYPE_2_04_TXT,
            ];
        }
        if ($position == IFlow::POSITION_3) {
            return [
                IFlow::TYPE_3_01 => IFlow::TYPE_3_01_TXT,
                IFlow::TYPE_3_02 => IFlow::TYPE_3_02_TXT,
            ];
        }
        return [];
    }
    public static function getTitlesByType($position, $type, $roleType = 1) {
        if ($position == 2) {
            //学生端
            if ($type == 1) {
                //组织
                if ($roleType == 1) {
                    //使用者
                    return [
                        Title::ALL_TXT, Title::ORGANIZATION_EMPLOYEE, Title::ORGANIZATION_DEPUTY, Title::ORGANIZATION_LEADER
                    ];
                }else {
                    //审批者
                    return [
                        Title::ORGANIZATION_EMPLOYEE, Title::ORGANIZATION_DEPUTY, Title::ORGANIZATION_LEADER
                    ];
                }

            }else {
                //职务
                if ($roleType == 1) {
                    //使用者
                    return [
                        Title::ALL_TXT, Title::CLASS_MONITOR, Title::CLASS_GROUP
                    ];
                }else {
                    //审批者
                    return [
                        Title::CLASS_ADVISER, Title::GRADE_ADVISER, Title::DEPARTMENT_LEADER
                    ];
                }
            }
        }else {
            //教师端
            if ($type == 1) {
                //组织
                if ($roleType == 1) {
                    //使用者
                    return [
                        Title::ALL_TXT, Title::ORGANIZATION_EMPLOYEE, Title::ORGANIZATION_DEPUTY, Title::ORGANIZATION_LEADER
                    ];
                }else {
                    //审批者
                    return [
                        Title::ORGANIZATION_EMPLOYEE, Title::ORGANIZATION_DEPUTY, Title::ORGANIZATION_LEADER
                    ];
                }
            }else {
                //职务
                if ($roleType == 1) {
                    //使用者
                    return [
                        Title::ALL_TXT, Title::CLASS_ADVISER, Title::GRADE_ADVISER, Title::DEPARTMENT_LEADER
                    ];
                }else {
                    //审批者
                    return [
                        Title::CLASS_ADVISER, Title::GRADE_ADVISER, Title::DEPARTMENT_LEADER
                    ];
                }
            }
        }
    }

    public static function getBusiness($businessid = null) {
        $list = [
            IFlow::BUSINESS_ATTENDANCE_CLOCKIN,
            IFlow::BUSINESS_ATTENDANCE_MACADDRESS,
            IFlow::BUSINESS_ATTENDANCE_LEAVE,
            IFlow::BUSINESS_ATTENDANCE_AWAY,
            IFlow::BUSINESS_ATTENDANCE_TRAVEL,
            IFlow::BUSINESS_OA_MEETING,
            IFlow::BUSINESS_STUDENT_LEAVE,
        ];
        if ($businessid) {
            foreach ($list as $item) {
                if ($businessid == $item['business']) {
                    return $item;
                }
            }
        }
        return $list;
    }

    public function nodes(){
        return $this->hasMany(Node::class);
    }

    private function getorganizationIdArrByName($schoolId, $name)
    {
        $ret = [];
        if (!empty($name)) {
            $nameArr = explode(';', trim($name, ';'));
            $organizationDao = new OrganizationDao();
            foreach ($nameArr as $value) {
                $organization = Organization::where(['school_id' => $schoolId, 'name' => $value])->first();
                $nowLevel = $organization->level;
                $return = [$organization->id];
                $parentid = $organization->parent_id;
                while ($nowLevel > 1) {
                    $parent = $organizationDao->getById($parentid);
                    array_unshift($return, $parent->id);
                    $parentid = $parent->parent_id;
                    $nowLevel = $parent->level;
                }
                $ret[] = $return;
            }
        }
        return $ret;
    }

    /**
     * 获取简单的流程的按顺序排列的步骤集合
     *
     * @return Collection
     */
    public function getSimpleLinkedNodes(){
        $result = ['head' => [], 'copy' => [], 'handler' => [], 'options' => []];
        $node = $this->getHeadNode();
        $node->handler->organization_ids = $this->getorganizationIdArrByName($this->school_id, $node->handler->organizations);
        $result['head'] = $node;//发起人
        if ($this->copy_uids) {
            //抄送人
            $uidArr = explode(';', $this->copy_uids);
            $copyUsers = User::whereIn('id', $uidArr)->get();
            if (!empty($copyUsers)) {
                foreach ($copyUsers as $user) {
                    $result['copy'][] = [
                        'user_id' => $user->id,
                        'name' => $user->name,
                        'avatar' => $user->profile->avatar ?? ''
                    ];
                }
            }
        }
        //表单
        if ($node->options) {
            foreach ($node->options as $option) {
                $option = $option->toArray();
                if (!empty($option['extra'])) {
                    $option['extra'] = json_decode($option['extra'], true);
                }
                $result['options'][] = $option;
            }
        }
        while ($node->next_node > 0){
            //获取审批人
            $next = Node::where('id',$node->next_node)
                ->with('handler')
                ->with('attachments')
                ->with('options')
                ->first();
            if (!empty($next->handler->titles) || !empty($next->handler->organizations)) {
                $next->handler->organization_ids = $this->getorganizationIdArrByName($this->school_id, $next->handler->organizations);
                $result['handler'][] = $next->handler;
            }
            $node = $next;
        }
        return $result;
    }

    /**
     * 获取流程的分类描述文字
     * @return string
     */
    public function getTypeText(){
        return self::Types()[$this->type];
    }

    public function getCurrentPendingAction(IUser $user): IAction
    {
        $actionDao = new ActionDao();
        return $actionDao->getByFlowAndResult(IAction::RESULT_PENDING, $this, $user);
    }

    public function setCurrentPendingNode(INode $node, IUser $user)
    {
        // TODO: Implement setCurrentPendingNode() method.
    }

    public function getHeadNode()
    {
        return Node::where('flow_id', $this->id)
            ->where('prev_node',0)
            ->with('handler')
            ->with('options')
            ->with('attachments')
            ->first();
    }

    public function getTailNode()
    {
        return Node::where('flow_id', $this->id)
            ->where('next_node',0)
            ->with('handler')
            ->with('attachments')
            ->with('options')
            ->first();
    }

    public function canBeStartBy(IUser $user): INode
    {
        $node = null;
        $dao = new FlowDao();
        if ($dao->checkPermissionByuser($this, $user, 0)){
            $node= $this->getHeadNode();
        }
        return $node;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getIconAttribute($value){
        return $value ? asset($value) : asset('assets/img/node-icon.png');
    }
}
