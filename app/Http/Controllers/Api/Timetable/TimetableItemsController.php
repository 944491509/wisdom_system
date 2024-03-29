<?php

namespace App\Http\Controllers\Api\Timetable;

use App\BusinessLogic\TimetableLogic\SpecialItemsLoadLogic;
use App\BusinessLogic\TimetableLogic\TimetableItemBeforeCreate;
use App\BusinessLogic\TimetableLogic\TimetableItemBeforeUpdate;
use App\BusinessLogic\TimetableViewLogic\Factory;
use App\Dao\Timetable\TimetableItemDao;
use App\Dao\Users\UserDao;
use App\Utils\JsonBuilder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TimetableItemsController extends Controller
{
    protected $userDao;
    public function __construct()
    {
        $this->userDao = new UserDao();
    }

    /**
     * 检查提交的数据是否可以插入到数据中
     * @param Request $request
     * @return string
     */
    public function can_be_inserted(Request $request){
        $logic = new TimetableItemBeforeCreate($request);
        $logic->check();
        if($logic->checked){
            return JsonBuilder::Success();
        }
        return JsonBuilder::Error($logic->errorMessage);
    }

    /**
     * 保存课程表项目的接口
     * @param Request $request
     * @return string
     */
    public function save(Request $request){
        $logic = new TimetableItemBeforeCreate($request);
        $item = $logic->check()->create();
        if($item){
            return JsonBuilder::Success(['id'=>$item->id]);
        }
        // Todo: 创建失败需要指明原因
        return JsonBuilder::Error();
    }

    /**
     * 更新已经存在的课程表项目
     * @param Request $request
     * @return string
     */
    public function update(Request $request){
        $logic = new TimetableItemBeforeUpdate($request);
        $updated = $logic->check()->update();

        if($updated){
            return JsonBuilder::Success();
        }
        // Todo: 更新失败需要指明原因
        return JsonBuilder::Error();
    }

    /**
     * @param Request $request
     * @return string
     */
    public function delete(Request $request){
        $dao = new TimetableItemDao();
        $timeTableId = $request->get('id');
        $user = $request->user();
        $result = $dao->deleteItem($timeTableId, $user);
        $msg = $result->getMessage();
        if($result->isSuccess()) {
            return JsonBuilder::Success($msg);
        } else {
            return JsonBuilder::Error($msg);
        }
    }

    /**
     * @param Request $request
     * @return string
     */
    public function publish(Request $request){
        $dao = new TimetableItemDao();
        $user = $this->userDao->getUserByUuid($request->get('user'));
        $result = $dao->publishItem($request->get('id'), $user);
        return $result ? JsonBuilder::Success() : JsonBuilder::Error();
    }

    /**
     * 克隆项目
     * @param Request $request
     * @return string
     */
    public function clone_item(Request $request){
        $dao = new TimetableItemDao();
        $result = $dao->cloneItem($request->get('item'));
        return $result ? JsonBuilder::Success() : JsonBuilder::Error();
    }

    /**
     * 加载整个课程表
     * @param Request $request
     * @return string
     */
    public function load(Request $request){
        $logic = Factory::GetInstance($request);
        $timetable = $logic->build();
        $data = [];
        foreach ($timetable as $key => $value) {
            $data[] = array_merge($value);
        }
        return JsonBuilder::Success(['timetable'=>$data]);
    }

    /**
     * 加载单个课程表中的某项
     * @param Request $request
     * @return string
     */
    public function load_item(Request $request){
        $dao = new TimetableItemDao();
        $item = $dao->getItemById($request->get('id'));
        return JsonBuilder::Success(['timetableItem'=>$item??'']);
    }

    /**
     * 创建新的课程表调课项
     * @param Request $request
     * @return string
     */
    public function create_special_case(Request $request){
        $specialCase = $request->get('specialCase');
        $dao = new TimetableItemDao();
        $item = $dao->getItemById($specialCase['to_replace']);

        $user = $this->userDao->getUserByUuid($request->get('user'));

        if($user && $user->isSchoolAdminOrAbove()){
            $result = $dao->createSpecialCase($specialCase, $item, $user);
            return $result ? JsonBuilder::Success(['grade_id'=>$result->grade_id]) : JsonBuilder::Error();
        }
        else{
            return JsonBuilder::Error('没有权限进行此操作');
        }
    }

    public function load_special_cases(Request $request){
        $ids = $request->get('timetable_ids');

        $logic = new SpecialItemsLoadLogic($ids);

        return JsonBuilder::Success($logic->build());
    }
}
