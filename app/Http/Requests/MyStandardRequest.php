<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 17/10/19
 * Time: 11:49 AM
 */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class MyStandardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::check();
    }

    /**
     * 获取系统允许的所有操作
     * @return \Illuminate\Config\Repository|mixed
     */
    public function getAllActions(){
        return config('acl.actions');
    }

    /**
     * 所有被禁止的权限的 slug
     * @return array
     */
    protected function slugOfDisallowAll(){
        $slug = [];
        $actions = $this->getAllActions();
        foreach ($actions as $action) {
            $slug[$action] = false;
        }
        return $slug;
    }

    /**
     * 获取当前操作的学校的 ID
     * @return mixed
     */
    public function getSchoolId(){
        return $this->session()->get('school.id',null);
    }

    /**
     * 获取参数中的 uuid
     * @return string
     */
    public function uuid(){
        return $this->get('uuid',null);
    }
}