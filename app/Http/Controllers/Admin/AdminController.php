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


    public function create(MyStandardRequest $request) {
        if($request->isMethod('post')) {
            $all = $request->all();
            $dao = new UserDao();
            $result = $dao->addAdmin($all);
            $msg = $result->getMessage();
            if($result->isSuccess()) {
                FlashMessageBuilder::Push($request,FlashMessageBuilder::SUCCESS,$msg);
                return redirect()->route('admin.list.school-manager',['school_id'=>$data['school_id']]);
            } else {

            }
            dd($all);
        } else {
            $this->dataForView['pageTitle'] = '添加管理员';
            return view('admin.admin.create',$this->dataForView);
        }
    }

}