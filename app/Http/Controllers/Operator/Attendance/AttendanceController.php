<?php
/**
 * Created by PhpStorm.
 * User: liuyang
 * Date: 2020/4/10
 * Time: 下午5:29
 */

namespace App\Http\Controllers\Operator\Attendance;


use App\Dao\AttendanceSchedules\AttendancesDao;
use App\Dao\Schools\SchoolDao;
use App\Http\Controllers\Controller;
use App\Http\Requests\MyStandardRequest;

class AttendanceController extends Controller
{

    public function gradeList(MyStandardRequest $request) {
        $schoolId = $request->getSchoolId();
        $schoolYear = $request->get('year');
        $term = $request->get('term');
        $schoolDao = new SchoolDao();
        $school = $schoolDao->getSchoolById($schoolId);
        $configuration = $school->configuration;

        if(is_null($schoolYear)) {
            $schoolYear = $configuration->getSchoolYear();
        }
        if(is_null($term)) {
            $term = $configuration->guessTerm();
        }

        $allTerm = $configuration->getAllTerm();
        $data = [];
        for ($i = 0; $i<=1; $i++) {
            $year = $schoolYear - $i;
            $next = $year + 1;

            for ($j = 1; $j<=2; $j++) {
                $isCurrent = false;
                if($year == $schoolYear && $term == $j) {
                    $isCurrent = true;
                }

                $data[] = [
                    'name' => $year.'-' .$next .'学年'.$allTerm[$j],
                    'year' => $year,
                    'term' => $j,
                    'is_current' => $isCurrent,
                ];
            }

        }



        $attendDao = new AttendancesDao();
        $gradeList = $attendDao->gradeListByYearAndTerm($schoolId, $schoolYear, $term);


        $this->dataForView['year'] = $schoolYear;
        $this->dataForView['term_text'] = $allTerm[$term];
        $this->dataForView['term'] = $term;
        $this->dataForView['school_year'] = $data;
        $this->dataForView['pageTitle'] = '班级列表';
        $this->dataForView['list'] = $gradeList;
        return view('school_manager.attend.grade_list',$this->dataForView);
    }
}