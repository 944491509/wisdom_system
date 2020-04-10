<?php
/**
 * Created by PhpStorm.
 * User: liuyang
 * Date: 2020/4/10
 * Time: 下午2:31
 */

namespace App\Http\Controllers\Teacher;


use App\Http\Controllers\Controller;

class ManualController extends Controller
{

    public function index() {

        $manual = config('manual');
        $this->dataForView['manual'] = $manual;
        return view('teacher.manual.list',$this->dataForView);
    }

}