<?php
/**
 * User: justinwang
 * Date: 4/12/19
 * Time: 8:40 PM
 */
namespace App\Http\Controllers\Api\Pipeline;
use App\BusinessLogic\Pipeline\Flow\FlowLogicFactory;
use App\Dao\Pipeline\ActionDao;
use App\Dao\Pipeline\FlowDao;
use App\Dao\Pipeline\UserFlowDao;
use App\Events\Pipeline\Flow\FlowBusiness;
use App\Events\Pipeline\Flow\FlowProcessed;
use App\Events\Pipeline\Flow\FlowRejected;
use App\Events\Pipeline\Flow\FlowStarted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pipeline\FlowRequest;
use App\Models\NetworkDisk\Media;
use App\Models\Pipeline\Flow\Action;
use App\Models\Pipeline\Flow\ActionOption;
use App\Models\Pipeline\Flow\Flow;
use App\Models\Pipeline\Flow\Node;
use App\User;
use App\Utils\JsonBuilder;
use App\Utils\Pipeline\IAction;
use App\Utils\Pipeline\IFlow;
use App\Utils\Pipeline\IUserFlow;
use Psy\Util\Json;

class FlowsController extends Controller
{
    /**
     * @param FlowRequest $request
     * @return string
     */
    public function my(FlowRequest $request){
        $logic = FlowLogicFactory::GetInstance($request->user());
        $types = $logic->getMyFlows(true);
        return JsonBuilder::Success(
            [
                'types'=>$types,
                'url'=>[
                    'base'=>env('APP_URL'),
                    'extra'=>'',
                    'start_flow'=>'/h5/flow/user/start',
                    'view_flows_in_progress'=>'/h5/flow/user/in-progress',
                ],
            ]
        );
    }

    public function business_url(FlowRequest $request) {
        $business = $request->get('business');
        $param = $request->get('param');
        $user = $request->user();
        $dao = new FlowDao();
        $result =  $dao->getListByBusiness($user->getSchoolId(), $business);
        $retFlow = [];

        foreach ( $result as $flow) {
            if ($dao->checkPermissionByuser($flow, $user, 0)) {
                $retFlow = $flow;
                break;
            }
        }
        if (empty($retFlow)) {
            return JsonBuilder::Error('权限不足');
        }else {
            $param['flow_id'] = $retFlow->id;
            $param['api_token'] = $user->api_token;
            $url = route('h5.flow.user.start', $param);
            return JsonBuilder::Success(['url' => $url]);
        }
    }

    public function open(FlowRequest $request){
        $user = $request->user();
        $flowDao = new FlowDao();
        $flow = $flowDao->getById($request->get('flow_id'));

        if($user && $flow){
            $logic = FlowLogicFactory::GetInstance($user);
            $bag = $logic->open($flow);
            if($bag->isSuccess()){
                $flowInfo = $flow->getSimpleLinkedNodes();
                $handlers = [];
                foreach ($flowInfo['handler'] as $handler) {
                    $tmpHandlers = $flowDao->transTitlesToUser($handler->titles, $handler->organizations, $user);
                    $retHandlers = [];
                    foreach ($tmpHandlers as $tmpKey => $tmpHandler) {
                        foreach ($tmpHandler as $tmp) {
                            $retHandlers[$tmpKey][] = [
                                'id' => $tmp->id,
                                'name' => $tmp->name,
                                'avatar' => $tmp->profile->avatar ?? ''
                            ];
                        }
                    }
                    $handlers[] = $retHandlers;
                }
                if ($flow->business) {
                    $businessOptions = Flow::getBusiness($flow->business);
                    $businessDefault = [];
                    foreach ($businessOptions['options'] as $businessOption) {
                        if ($businessOption['readonly']) {
                            parse_str(parse_url($request->headers->get('referer'), PHP_URL_QUERY), $getParam);
                            $businessDefault[$businessOption['title']] = $getParam[$businessOption['uri']] ?? '';
                        }
                    }
                    $options = [];
                    foreach ($flowInfo['options'] as $option) {
                        if (isset($businessDefault[$option['title']])) {
                            $option['value'] = $businessDefault[$option['title']];
                            $option['default'] = true;
                        }
                        $options[] = $option;
                    }
                }else {
                    $options = $flowInfo['options'];
                }
                $return = [
                    'user' => $user,
                    'flow' => $flow,
                    'handlers' => $handlers,
                    'copys' => $flowInfo['copy'],
                    'options' => $options,
                    'api_token' => $request->get('api_token'),
                    'appName' => 'pipeline-flow-open-app'
                ];
                return JsonBuilder::Success($return);
            }
        }
        return JsonBuilder::Error('您没有权限执行此操作');;
    }

    /**
     * @param FlowRequest $request
     * @return string
     */
    public function started_by_me(FlowRequest $request){
        $logic = FlowLogicFactory::GetInstance($request->user());
        $position = $request->get('position', 0);
        $keyword = $request->get('keyword');
        $size = $request->get('size');
        $list = $logic->startedByMe($position, $keyword, $size);
        $retList = [];
        if ($list) {
            foreach ($list as $key => $value) {
                $retList[] = [
                    'id' => $value->id,
                    'avatar' => $value->user->profile->avatar ?? '',
                    'user_name' => $value->user_name,
                    'flow' => ['name' => $value->flow->name ?? ''],
                    'done' => $value->done,
                    'created_at' => $value->created_at->format('Y-m-d H:i:s')
                ];
            }
        }
        $list = $list->toArray();
        unset($list['data']);
        $list['flows'] = $retList;
        return JsonBuilder::Success($list);
    }

    /**
     * @param FlowRequest $request
     * @return string
     */
    public function waiting_for_me(FlowRequest $request){
        $logic = FlowLogicFactory::GetInstance($request->user());
        $position = $request->get('position', 0);
        $keyword = $request->get('keyword');
        $size = $request->get('size');
        $list = $logic->waitingForMe($position, $keyword, $size);
        $retList = [];
        if ($list) {
            foreach ($list as $key => $value) {
                $retList[] = [
                    'id' => $value->userFlow->id,
                    'avatar' => $value->userFlow->user->profile->avatar ?? '',
                    'user_name' => $value->userFlow->user_name,
                    'flow' => ['name' => $value->flow->name ?? ''],
                    'done' => $value->userFlow->done,
                    'created_at' => $value->userFlow->created_at->format('Y-m-d H:i:s')
                ];
            }
        }
        $list = $list->toArray();
        unset($list['data']);
        $list['flows'] = $retList;
        return JsonBuilder::Success($list);
    }

    /**
     * @param FlowRequest $request
     * @return string
     */
    public function copy_to_me(FlowRequest $request){
        $logic = FlowLogicFactory::GetInstance($request->user());
        $position = $request->get('position', 0);
        $keyword = $request->get('keyword');
        $size = $request->get('size');
        $list = $logic->copyToMe($position, $keyword, $size);
        $retList = [];
        if ($list) {
            foreach ($list as $key => $value) {
                $retList[] = [
                    'id' => $value->id,
                    'avatar' => $value->user->profile->avatar ?? '',
                    'user_name' => $value->user_name,
                    'flow' => ['name' => $value->flow->name ?? ''],
                    'done' => $value->done,
                    'created_at' => $value->created_at->format('Y-m-d H:i:s')
                ];
            }
        }
        $list = $list->toArray();
        unset($list['data']);
        $list['flows'] = $retList;
        return JsonBuilder::Success($list);
    }

    public function my_processed(FlowRequest $request){
        $logic = FlowLogicFactory::GetInstance($request->user());
        $position = $request->get('position', 0);
        $keyword = $request->get('keyword');
        $size = $request->get('size');
        $list = $logic->myProcessed($position, $keyword, $size);
        $retList = [];
        if ($list) {
            foreach ($list as $key => $value) {
                $retList[] = [
                    'id' => $value->id,
                    'avatar' => $value->user->profile->avatar ?? '',
                    'user_name' => $value->user_name,
                    'flow' => ['name' => $value->flow->name ?? ''],
                    'done' => $value->done,
                    'created_at' => $value->created_at->format('Y-m-d H:i:s')
                ];
            }
        }
        $list = $list->toArray();
        unset($list['data']);
        $list['flows'] = $retList;
        return JsonBuilder::Success($list);
    }

    /**
     * 根据一个动作的 id, 配合当前登陆的用户, 来查看整个流程的历史记录
     * @param FlowRequest $request
     * @return string
     */
    public function view_action(FlowRequest $request){
        $user = $request->user();
        $userFlowId = $request->get('user_flow_id', null);
        if($user && $userFlowId){
            $dao = new ActionDao();
            //发布者action
            $startUserAction = $dao->getFirstActionByUserFlow($userFlowId);
            //当前用户action
            $nowUserAction = $dao->getActionByUserFlowAndUserId($userFlowId, $user->id);
            $showActionEditForm = !empty($nowUserAction)
            && empty($request->get('readonly', '0'))
            && $nowUserAction->result == IAction::RESULT_PENDING
            && $startUserAction->userFlow->done == IUserFlow::IN_PROGRESS  ? true : false;

            $flowDao = new FlowDao();
            //流程信息
            $flow = $flowDao->getById($startUserAction->flow_id);
            $flowInfo = $flow->getSimpleLinkedNodes();
            $handlers = [];
            //审批结果
            $actionResult = $dao->getHistoryByUserFlow($startUserAction->userFlow->id, true);
            $actionReList = [];
            foreach ($actionResult as $actRet) {
                $actionReList[$actRet->node_id . '_' .$actRet->user_id] = $actRet;
            }
            $handlersIcon = [];
            //审批人与结果关联
            foreach ($flowInfo['handler'] as $handler) {
                $icon = '';
                $userList = $flowDao->transTitlesToUser($handler->titles, $handler->organizations, $startUserAction->userFlow->user);
                $retUserList = [];
                foreach ($userList as $key => $item) {
                    foreach ($item as $im) {
                        if (isset($actionReList[$handler->node_id.'_'.$im->id])) {
                            $im->result = $actionReList[$handler->node_id.'_'.$im->id];
                            //如果有人拒绝整个流程都是拒绝
                            if ($im->result->result == IAction::RESULT_TERMINATE) {
                                $icon = 'error';
                            }
                            if ($im->result->result == IAction::RESULT_REJECT) {
                                $icon = 'error';
                            }
                            //如果有人等待 整个流程都是等待
                            if (empty($icon) && $im->result->result == IAction::RESULT_PENDING) {
                                $icon = 'pending';
                            }
                        }else {
                            //如果还没轮到 整个流程都是等待
                            if (empty($icon)) {
                                $icon = 'wait';
                            }
                            $im->result = [];
                        }

                        $retUserList[$key][] = [
                            'avatar' => $im->profile->avatar ?? '',
                            'name' => $im->profile->name ?? '',
                            'result' => $im->result
                        ];
                    }
                }
                if (empty($icon)) {
                    $icon = 'success';
                }
                $handlers[] = ['icon' => $icon, 'list' => $retUserList];
            }

            //表单信息
            $optionReList = [];
            foreach ($flowInfo['options'] as $option) {
                if ($option['type'] == 'node') {
                    continue;
                }
                $optionRet = ActionOption::where('action_id', $startUserAction->id)->where('option_id', $option['id'])->value('value');
                $value = '';
                switch ($option['type']) {
                    case 'date-date':
                        if ($optionRet) {
                            $optionRet = explode('~', $optionRet);
                            $value = $optionRet[0];
                            if (!empty($optionRet[1])) {
                                $optionReList[] = [
                                    'type' => $option['type'],
                                    'name' => $option['name'],
                                    'title' => $option['title'],
                                    'value' => $value
                                ];

                                $option['title'] = $option['extra']['title2'];
                                $value = $optionRet[1];
                            }
                        }
                        break;
                    case 'radio':
                        if ($optionRet) {
                            $optionRet = json_decode($optionRet, true);
                            if (!empty($optionRet)) {
                                $value = $optionRet['itemText'];
                            }
                        }
                        break;
                    case 'checkbox':
                        if ($optionRet) {
                            $optionRet = json_decode($optionRet, true);
                            if (!empty($optionRet)) {
                                foreach ($optionRet as $ret) {
                                    $value .= ' ' . $ret['itemText'];
                                }
                            }
                        }
                        break;

                    case 'image':
                        if ($optionRet) {
                            $value = explode(',', $optionRet);
                        }
                        break;
                    case 'files':
                        if ($optionRet) {
                            $value = Media::whereIn('id', explode(',', $optionRet))->select(['file_name','url'])->get()->toArray();
                        }
                        break;
                    default:
                        $value = $optionRet;
                        break;
                }

                $optionReList[] = [
                    'type' => $option['type'],
                    'name' => $option['name'],
                    'title' => $option['title'],
                    'value' => $value
                ];
            }

            $baseInfo  = [];
            if ($startUserAction->userFlow->user->isStudent()) {
                $baseInfo[] = [
                    'name' => '姓名',
                    'value' => $startUserAction->userFlow->user->name,
                ];
                $baseInfo[] = [
                    'name' => '性别',
                    'value' => $startUserAction->userFlow->user->profile->gender == 1 ? '男' : '女',
                ];
                $baseInfo[] = [
                    'name' => '出生年月',
                    'value' => substr($startUserAction->userFlow->user->profile->birthday) ?? '',
                ];
                $baseInfo[] = [
                    'name' => '民族',
                    'value' => $startUserAction->userFlow->user->profile->nation_name ?? '',
                ];
                $baseInfo[] = [
                    'name' => '政治面貌',
                    'value' => $startUserAction->userFlow->user->profile->political_name ?? '',
                ];
                $baseInfo[] = [
                    'name' => '入学时间',
                    'value' => $startUserAction->userFlow->user->profile->year ?? '',
                ];
                $baseInfo[] = [
                    'name' => '身份证号',
                    'value' => $startUserAction->userFlow->user->profile->id_number ?? '',
                ];
                $baseInfo[] = [
                    'name' => '学院',
                    'value' => $startUserAction->userFlow->user->gradeUser->institute->name ?? '',
                ];
                $baseInfo[] = [
                    'name' => '专业',
                    'value' => $startUserAction->userFlow->user->gradeUser->major->name ?? '',
                ];
                $baseInfo[] = [
                    'name' => '班级',
                    'value' => $startUserAction->userFlow->user->gradeUser->grade->name ?? '',
                ];
            }
            $initData = [
                'school' => $user->getSchoolId(),
                'useruuid' => $user->uuid,
                'apitoken' => $user->api_token,
                'flowid' => $startUserAction->userFlow->id,
                'actionid' => $nowUserAction ? $nowUserAction->id : null,
                'theaction' => $nowUserAction
            ];
            $startInfo = [
                'avatar' => $startUserAction->userFlow->user->profile->avatar ?? '',
                'name' => $startUserAction->userFlow->user->name ?? '',
                'time' => substr($startUserAction->created_at, 0, 16),
            ];
            $cancelInfo = [];
            if ($startUserAction->userFlow->done == IUserFlow::REVOKE) {
                $cancelInfo = [
                    'avatar' => $startUserAction->userFlow->user->profile->avatar ?? '',
                    'name' => $startUserAction->userFlow->user->name ?? '',
                    'time' => substr($startUserAction->updated_at, 0, 16),
                ];
            }

            $return = [
                'baseInfo' => $baseInfo,
                'initData' => $initData,
                'options' => $optionReList,
                'copys' => $flowInfo['copy'],
                'showActionEditForm' => $showActionEditForm,
                'autoProcessed' => $flowInfo->auto_processed,
                'startInfo' => $startInfo,
                'cancelInfo' => $cancelInfo,
                'handlers' => $handlers,
            ];
            return JsonBuilder::Success($return);
        }
        else {
            return JsonBuilder::Error('您无权使用本功能');
        }
    }

    /**
     * 启动一个流程的接口
     * @param FlowRequest $request
     * @return string
     */
    public function start(FlowRequest $request){
        $actionData = $request->getStartFlowData();
        $actionData['options'] = $request->get('options');
        $user = $request->user();

        $flowDao = new FlowDao();
        $flow = $flowDao->getById($request->getStartFlowId());

        $logic = FlowLogicFactory::GetInstance($user);
        $bag = $logic->start(
            $flow,
            $actionData
        );

        if($bag->isSuccess()){
            /**
             * @var Action $action
             */
            $action = $bag->getData()['action'];
            /**
             * @var Node $node
             */
            $node = $bag->getData()['node'];

            // 发布流程启动成功事件
            event(new FlowStarted($request->user(),$action, $flow, $node));
            return JsonBuilder::Success(
                [
                    'id'=>$action->id,
                    'url'=>$request->isAppRequest()?route('h5.flow.user.view-history',['user_flow_id' => $action->transaction_id,'api_token'=>$request->user()->api_token, 'readonly' => 1]):null
                ]
            );
        }
        else{
            return JsonBuilder::Error($bag->getMessage());
        }
    }

    /**
     * 撤销我发起的流程
     * @param FlowRequest $request
     * @return string
     */
    public function cancel_action(FlowRequest $request){
        $user = $request->user();
        $actionId = $request->getUserFlowId();

        $dao = new ActionDao();
        $bag = $dao->cancelUserFlow($user, $actionId);

        return $bag->isSuccess() ? JsonBuilder::Success() : JsonBuilder::Error($bag->getMessage());
    }

    /**
     * 流程中某个步骤的处理: 通过/驳回
     * @param FlowRequest $request
     * @return string
     */
    public function process(FlowRequest $request){
        $actionFormData = $request->getActionFormData();
        $dao = new ActionDao();
        $action = $dao->getByActionIdAndUserId($actionFormData['id'], $request->user()->id);
        if($action && $action->result == IAction::RESULT_PENDING){
            $logic = FlowLogicFactory::GetInstance($request->user());
            switch ($actionFormData['result']){
                /*case IAction::RESULT_REJECT:
                    $bag = $logic->reject($action, $actionFormData); // 进入驳回流程的操作
                    break;*/
                case IAction::RESULT_TERMINATE:
                    $bag = $logic->terminate($action, $actionFormData); // 进入终止流程的操作
                    break;
                default:
                    $bag = $logic->process($action, $actionFormData); // 进入同意流程的操作, 默认
                    break;
            }

            $event = null;
            if ($bag->isSuccess()){
                switch ($actionFormData['result']){
                    /*case IAction::RESULT_REJECT:
                        // 驳回流程的事件
                        $event = new FlowRejected($request->user(),$action, $bag->getData()['prevNode'], $action->getFlow());
                        break;*/
                    case IAction::RESULT_PASS:
                        if ($dao->getCountWaitProcessUsers($action->getNode()->id, $action->transaction_id) < 1) {
                            //可能存在自动同意已经到了下一个action
                            $newAction = $dao->getActionByUserFlowAndUserId($action->transaction_id, $action->user_id);
                            $flow = $newAction->getFlow();
                            $event = new FlowProcessed($request->user(),$newAction, $newAction->getNode(), $flow);

                            //业务事件
                            if ($newAction->userFlow->isDone() && $flow->business) {
                                  event(new FlowBusiness($flow, $newAction->userFlow));
                            }
                        }
                        break;
                    case IAction::RESULT_TERMINATE:
                        $event = new FlowRejected($request->user(),$action, $action->getNode()->prev, $action->getFlow());

                        //Oa业务特殊事件
                        $newAction = $dao->getActionByUserFlowAndUserId($action->transaction_id, $action->user_id);
                        $flow = $newAction->getFlow();
                        if ($flow->business && $flow->business == IFlow::BUSINESS_TYPE_MEETING) {
                            event(new FlowBusiness($flow, $newAction->userFlow));
                        }
                        break;
                    default:
                        $event = null;
                }

                $event && event($event); // 发布事件
                return JsonBuilder::Success();
            }
            else{
                return JsonBuilder::Error($bag->getMessage());
            }
        }
        else{
            return JsonBuilder::Error('你无权进行此操作');
        }
    }
}
