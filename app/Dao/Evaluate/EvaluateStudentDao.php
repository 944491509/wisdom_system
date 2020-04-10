<?php
/**
 * Created by PhpStorm.
 * User: liuyang
 * Date: 2020/4/10
 * Time: 上午10:21
 */

namespace App\Dao\Evaluate;


use App\Utils\Misc\ConfigurationTool;
use App\Models\Evaluate\EvaluateStudent;

class EvaluateStudentDao
{

    /**
     * 根据评教老师id查询班级
     * @param $evaluateTeacherId
     * @return mixed
     */
    public function getEvaluateGradeListByEvaluateTeacherId($evaluateTeacherId) {
        $map = ['evaluate_teacher_id'=>$evaluateTeacherId];
        return EvaluateStudent::where($map)
            ->select('grade_id')
            ->distinct('grade_id')
            ->get();
    }


    /**
     * 根据评教ID和班级ID查询评教学生
     * @param $gradeId
     * @param $evaluateTeacherId
     * @return mixed
     */
    public function getStudentByGradeIdAndEvaluateTeacherId($gradeId, $evaluateTeacherId) {
        $map = [
            'grade_id'=>$gradeId,
            'evaluate_teacher_id'=>$evaluateTeacherId
        ];
        return EvaluateStudent::where($map)
            ->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);
    }
}