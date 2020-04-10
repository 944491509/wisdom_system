<?php
/**
 * Created by PhpStorm.
 * User: liuyang
 * Date: 2020/4/10
 * Time: 下午2:31
 */

namespace App\Http\Controllers\Teacher;


use App\Http\Controllers\Controller;
use App\Http\Requests\MyStandardRequest;

class ManualController extends Controller
{

    public function index() {

        $manual = config('manual');
        $this->dataForView['manual'] = $manual;
        return view('teacher.manual.list',$this->dataForView);
    }


    /**
     * 下载手册
     * @param MyStandardRequest $request
     */
    public function download(MyStandardRequest $request) {
        $manualId = $request->get('manual_id');
        $manual = config('manual')[$manualId];
        $url = asset($manual['url']);

        ob_start();
        $filename=$url;
        header( "Content-type:  application/octet-stream ");
        header( "Accept-Ranges:  bytes ");
        header( "Content-Disposition:  attachment;  filename=".$manual['filename']);
        $size=readfile($filename);
        header( "Accept-Length: " .$size);die;

    }

}