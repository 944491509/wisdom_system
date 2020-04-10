<?php


namespace App\Http\Controllers\Operator\Evaluate;


use App\Dao\Evaluate\EvaluateTeacherRecordDao;
use App\Http\Controllers\Controller;
use App\Http\Requests\Evaluate\EvaluateTeacherRecordRequest;

class EvaluateRecordController extends Controller
{
    public function list(EvaluateTeacherRecordRequest $request){
        $evaluateStudentId = $request->get('evaluate_student_id');

        $dao = new EvaluateTeacherRecordDao();
        $list = $dao->getRecordByEvalStudentId($evaluateStudentId);
        $this->dataForView['list'] = $list;
        return view('school_manager.evaluate.record.list',$this->dataForView);
    }

}
