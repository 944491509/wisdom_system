<?php
/**
 * Created by PhpStorm.
 * User: liuyang
 * Date: 2020/4/10
 * Time: 下午5:29
 */

namespace App\Http\Controllers\Operator\Attendance;


use App\Dao\Schools\SchoolDao;
use App\Dao\Users\GradeUserDao;
use App\Http\Controllers\Controller;
use App\Http\Requests\MyStandardRequest;
use App\Dao\AttendanceSchedules\AttendancesDao;
use App\Dao\AttendanceSchedules\AttendancesDetailsDao;

class AttendanceController extends Controller
{

    /**
     * 签到评分班级列表
     * @param MyStandardRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function gradeList(MyStandardRequest $request) {
        $schoolId = $request->getSchoolId();
        $year = $request->get('year');
        $term = $request->get('term');
        $schoolDao = new SchoolDao();
        $school = $schoolDao->getSchoolById($schoolId);
        $configuration = $school->configuration;
        $schoolYear = $configuration->getSchoolYear();

        if(is_null($year)) {
            $year = $schoolYear;
        }
        if(is_null($term)) {
            $term = $configuration->guessTerm();
        }

        $this->dataForView['year'] = $year;
        $attendDao = new AttendancesDao();
        $gradeList = $attendDao->gradeListByYearAndTerm($schoolId, $year, $term);

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



        $this->dataForView['term_text'] = $allTerm[$term];
        $this->dataForView['term'] = $term;
        $this->dataForView['school_year'] = $data;
        $this->dataForView['pageTitle'] = '班级列表';
        $this->dataForView['list'] = $gradeList;
        return view('school_manager.attend.grade_list',$this->dataForView);
    }


    /**
     * 学生签到统计
     * @param MyStandardRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function studentList(MyStandardRequest $request) {
        $year = $request->get('year');
        $term = $request->get('term');
        $gradeId = $request->getGradeId();
        $detailsDao = new AttendancesDetailsDao();
        $gradeUserDao = new GradeUserDao();
        $gradeUser = $gradeUserDao->getGradeUserByGradeId($gradeId);
        $result = [];
        foreach ($gradeUser as $key => $item) {
            $signIn_num = $detailsDao->getSignInCountByUser($item->user_id,$year, $term);
            $leave_num = $detailsDao->getLeaveCountByUser($item->user_id,$year, $term);
            $truant_num = $detailsDao->getTruantCountByUser($item->user_id, $year, $term);
            $result[] = [
                'username' => $item->user->name,
                'user_id' => $item->user_id,
                'signIn_num' => $signIn_num,
                'leave_num' => $leave_num,
                'truant_num' => $truant_num,
            ];
        }
        $this->dataForView['pageTitle'] = '学生签到统计';
        $this->dataForView['year'] = $year;
        $this->dataForView['term'] = $term;
        $this->dataForView['list'] = $result;
        return view('school_manager.attend.student_list',$this->dataForView);
    }


    /**
     * @param MyStandardRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function details(MyStandardRequest $request) {
        $year = $request->get('year');
        $term = $request->get('term');
        $userId = $request->get('user_id');
        $detailsDao = new AttendancesDetailsDao();
        $result = $detailsDao->getPageSignDetailByYearAndTerm($userId,$year, $term);
        $this->dataForView['pageTitle'] = '签到详情列表';
        $this->dataForView['year'] = $year;
        $this->dataForView['term'] = $term;
        $this->dataForView['user_id'] = $userId;
        $this->dataForView['list'] = $result;
        return view('school_manager.attend.details_list',$this->dataForView);
    }
}