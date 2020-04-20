<?php
/**
 * 角色管理的控制器类
 */
namespace App\Http\Controllers\Admin;

use App\Dao\Simpleacl\SimpleaclRoleDao;
use App\Http\Controllers\Controller;
use App\Utils\JsonBuilder;
use Illuminate\Http\Request;

class SimpleaclController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request){
        $this->dataForView['pageTitle'] = '权限控制';
        return view('admin.simpleacl.list', $this->dataForView);
    }

    public function lists(Request $request) {
        $dao = new SimpleaclRoleDao();
        $result = $dao->getPaginated();
        if ($result) {
            foreach ($result as $val) {
                if (!empty($val->users)) {
                    $userArr = [];
                    foreach ($val->users as $user) {
                        $userArr[] = ['id' => $user->id, 'name' => $user->name];
                    }
                    unset($val->users);
                    $val->users = $userArr;
                }

                if (!empty($val->permissions)) {
                    $permisssionArr = [];
                    foreach ($val->permissions as $permission) {
                        $permisssionArr[] = $permission->id;
                    }
                    unset($val->permissions);
                    $val->permissions = $permisssionArr;
                }
            }
        }
        return JsonBuilder::Success($result);
    }

    public function delete(Request $request) {
        $dao = new SimpleaclRoleDao();
        $info = $dao->getById($request->get('id'));
        if (empty($info)) {
            return JsonBuilder::Error('id有误');
        }
        $result = $dao->deleteRole($info->id);
        return $result->isSuccess() ?
            JsonBuilder::Success('删除成功') :
            JsonBuilder::Error($result->getMessage());
    }

    public function add(Request $request) {
        $dao = new SimpleaclRoleDao();
        $input = $request->get('role');
        $result = $dao->createRole($input);
        return $result->isSuccess() ?
            JsonBuilder::Success('创建成功') :
            JsonBuilder::Error($result->getMessage());
    }

    public function add_role(Request $request) {
        $dao = new SimpleaclRoleDao();
        $info = $dao->getById($request->get('id'));
        if (empty($info)) {
            return JsonBuilder::Error('id有误');
        }
        $users = $request->get('users');
        $result = $dao->addUsers($info->id, $users);
        return $result->isSuccess() ?
            JsonBuilder::Success('创建成功') :
            JsonBuilder::Error($result->getMessage());
    }

    public function add_permission(Request $request) {
        $dao = new SimpleaclRoleDao();
        $info = $dao->getById($request->get('id'));
        if (empty($info)) {
            return JsonBuilder::Error('id有误');
        }
        $permissions = $request->get('permissions');
        $result = $dao->addPermissions($info->id, $permissions);
        return $result->isSuccess() ?
            JsonBuilder::Success('创建成功') :
            JsonBuilder::Error($result->getMessage());
    }

    public function menu_permission(Request $request) {
        $dao = new SimpleaclRoleDao();
        $info = $dao->getById($request->get('id'));
        if (empty($info)) {
            return JsonBuilder::Error('id有误');
        }

        $parents = $dao->getMenuByParent($info->type, 0);

        $return = [];
        foreach ($parents as $parent) {
            $return[] = $dao->outputOnlyData($parent);
        }
        return JsonBuilder::Success($return);
    }
}
