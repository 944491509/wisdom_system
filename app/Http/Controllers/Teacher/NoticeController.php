<?php


namespace App\Http\Controllers\Teacher;


use App\Http\Controllers\Controller;

class NoticeController extends Controller
{

    public function noticeInfo() {
        return view('teacher.notice.info',$this->dataForView);
    }
}
