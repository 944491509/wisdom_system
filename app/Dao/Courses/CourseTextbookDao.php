<?php

namespace App\Dao\Courses;

use App\Utils\JsonBuilder;
use App\Utils\ReturnData\MessageBag;
use App\Models\Courses\CourseTextbook;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CourseTextbookDao
{
    /**
     * @param $textbookId
     * @return mixed
     */
    public function deleteByTextbook($textbookId){
        return CourseTextbook::where('textbook_id',$textbookId)->delete();
    }

    /**
     * 创建
     * @param $data
     * @return mixed
     */
    public function create($data) {
        return CourseTextbook::create($data);
    }

    /**
     * 添加课程教材关联
     * @param $courseId
     * @param $schoolId
     * @param $textbookIdArr
     * @return MessageBag
     */
    public function createCourseTextbook($courseId, $schoolId, $textbookIdArr) {
        $bag = new MessageBag();
        try{
            DB::beginTransaction();
            CourseTextbook::where('course_id',$courseId)->delete();
            foreach ($textbookIdArr as $key => $value) {
                $data = [
                    'course_id' => $courseId,
                    'school_id' => $schoolId,
                    'textbook_id' => $value,
                ];
                CourseTextbook::create($data);
            }
            DB::commit();
            $bag->setMessage('保存成功');
        } catch (\Exception $e) {
            DB::rollBack();
            $msg = $e->getMessage();
            $bag->setMessage($msg);
            $bag->setCode(JsonBuilder::CODE_ERROR);
        }
        return $bag;
    }

}
