<?php

namespace App\Dao\Simpleacl;


use App\Models\Simpleacl\SimpleaclMenu;
use App\Models\Simpleacl\SimpleaclRole;
use App\Models\Simpleacl\SimpleaclRolePermission;
use App\Models\Simpleacl\SimpleaclRoleUser;
use App\Utils\JsonBuilder;
use App\Utils\Misc\ConfigurationTool;
use App\Utils\ReturnData\MessageBag;
use Illuminate\Support\Facades\DB;

class SimpleaclRoleDao
{
    public function getById($id)
    {
        return SimpleaclRole::find($id);
    }
    public function getPaginated()
    {
        return SimpleaclRole::with(['users', 'permissions'])->orderBy('created_at','desc')->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);
    }
    public function createRole($data)
    {
        try{
            $result = SimpleaclRole::create($data);
            return new MessageBag(JsonBuilder::CODE_SUCCESS,'创建成功', $result);
        }catch (\Exception $e) {
            return new MessageBag(JsonBuilder::CODE_ERROR, $e->getMessage());
        }
    }

    public function deleteRole($roleId)
    {
        DB::beginTransaction();
        try {
            if (SimpleaclRole::where('id', '=', $roleId)->delete()) {
                SimpleaclRoleUser::where('simpleacl_role_id', '=', $roleId)->delete();
                SimpleaclRolePermission::where('simpleacl_role_id', '=', $roleId)->delete();
            }
            DB::commit();
            return new MessageBag(JsonBuilder::CODE_SUCCESS,'删除成功');
        }catch (\Exception $e) {
            return new MessageBag(JsonBuilder::CODE_ERROR, $e->getMessage());
        }
    }

    public function addUsers($roleId, $users)
    {
        DB::beginTransaction();
        try {
            SimpleaclRoleUser::where('simpleacl_role_id', '=', $roleId)->delete();
            if (!empty($users)) {
                foreach ($users as $userid) {
                    SimpleaclRoleUser::create([
                        'simpleacl_role_id' => $roleId,
                        'user_id' => $userid
                    ]);
                }
            }
            DB::commit();
            return new MessageBag(JsonBuilder::CODE_SUCCESS,'绑定成功');
        }catch (\Exception $e) {
            return new MessageBag(JsonBuilder::CODE_ERROR, $e->getMessage());
        }
    }

    public function addPermissions($roleId, $permissions)
    {
        DB::beginTransaction();
        try {
            SimpleaclRolePermission::where('simpleacl_role_id', '=', $roleId)->delete();
            if (!empty($permissions)) {
                foreach ($permissions as $permissionid) {
                    SimpleaclRolePermission::create([
                        'simpleacl_role_id' => $roleId,
                        'simpleacl_permission_id' => $permissionid
                    ]);
                }
            }
            DB::commit();
            return new MessageBag(JsonBuilder::CODE_SUCCESS,'绑定成功');
        }catch (\Exception $e) {
            return new MessageBag(JsonBuilder::CODE_ERROR, $e->getMessage());
        }
    }

    public function getMenuByParent($type, $parent = 0)
    {
        return SimpleaclMenu::with(['children', 'permissions'])->where(['type' => $type, 'parent_id' => $parent])->orderBy('sort', 'desc')->get();
    }

    public function outputOnlyData(SimpleaclMenu $menu){
        $return = [
            'id' => $menu->id,
            'name' => $menu->name,
            'permissions' => $menu->permissions
        ];
        foreach ($menu->children as $child) {
            $return['children'][] = $this->outputOnlyData($child);
        }
        return $return;
    }
}
