<?php
/**
 * Created by PhpStorm.
 * User: liuyang
 * Date: 2020/4/22
 * Time: 下午3:12
 */

namespace App\Http\Controllers\Admin;


use App\Dao\Users\UserDao;
use App\Http\Controllers\Controller;
use App\Http\Requests\MyStandardRequest;
use App\Utils\FlashMessageBuilder;

class AdminController extends Controller
{
    public function list() {
        $dao = new UserDao();
        $list = $dao->getAdminPage();
        $this->dataForView['list'] = $list;
        return view('admin.admin.list',$this->dataForView);
    }


    /**
     * 创建平台管理员
     * @param MyStandardRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function create(MyStandardRequest $request) {
        if($request->isMethod('post')) {
            $all = $request->all();
            $dao = new UserDao();
            $result = $dao->addAdmin($all);
            $msg = $result->getMessage();
            if($result->isSuccess()) {
                FlashMessageBuilder::Push($request,FlashMessageBuilder::SUCCESS,$msg);
                return redirect()->route('admin.admin.list');
            } else {
                FlashMessageBuilder::Push($request,FlashMessageBuilder::WARNING,$msg);
                return redirect()->route('admin.admin.create');
            }
        } else {
            $this->dataForView['pageTitle'] = '添加管理员';
            return view('admin.admin.create',$this->dataForView);
        }
    }


    public function update(MyStandardRequest $request) {
        $dao = new UserDao();
        if($request->isMethod('post')) {
            $all = $request->all();
            $re = $dao->updateAdminByUserId($all['user_id'], $all['name'], $all['password']);
            if($re !== false) {
                FlashMessageBuilder::Push($request,FlashMessageBuilder::SUCCESS,'编辑成功');
                return redirect()->route('admin.admin.list');
            } else {
                FlashMessageBuilder::Push($request,FlashMessageBuilder::WARNING,'编辑失败');
                return redirect()->route('admin.admin.update',['user_id'=>$all['user_id']]);
            }

        } else {
            $userId = $request->get('user_id');
            $user = $dao->getUserById($userId);
            $this->dataForView['pageTitle'] = '编辑管理员';
            $this->dataForView['user'] = $user;
            return view('admin.admin.update',$this->dataForView);
        }
    }

}