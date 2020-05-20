<?php

namespace App\Http\Controllers\Api\School;

use App\Models\Schools\SchoolConfiguration;
use App\Models\Timetable\TimeSlot;
use App\User;
use App\Models\School;
use App\Utils\JsonBuilder;
use Illuminate\Http\Request;
use App\Dao\Schools\GradeDao;
use App\Dao\Schools\SchoolDao;
use App\Dao\Timetable\TimeSlotDao;
use App\Http\Controllers\Controller;
use App\Utils\Misc\ConfigurationTool;
use App\Http\Requests\MyStandardRequest;

class TimeSlotsController extends Controller
{
    public function save_time_slot(Request $request){
        $dao = new SchoolDao();
        $school = $dao->getSchoolByUuid($request->get('school'));
        if($school){
            $tsDao = new TimeSlotDao();
            $timeSlot = $request->get('timeSlot');
            $id = $timeSlot['id'];
            $ts = $tsDao->getById($id);
            if($ts && $ts->school_id === $school->id){
                unset($timeSlot['id']);
                $tsDao->update($id, $timeSlot);
                return JsonBuilder::Success();
            }
        }
        return JsonBuilder::Error('系统繁忙, 请稍候再试!');
    }


    /**
     * 加载学校年级
     * @param Request $request
     * @return string
     */
    public function load_year_school(Request $request) {
        $schoolId = $request->get('school_id');
        if(empty($schoolId)) {
            return JsonBuilder::Error('缺少参数');
        }
        $configuration = SchoolConfiguration::where('school_id',$schoolId)->first();
        $year = $configuration->yearText();

        return JsonBuilder::Success($year);

    }




    /**
     * 根据指定的学校 uuid 返回作息时间表
     * @param Request $request
     * @return mixed
     */
    public function load_by_school(Request $request){
        $schoolUuid = $request->get('school');
        $year = $request->get('year',1);

        $schoolDao = new SchoolDao(new User());

        $school = $schoolDao->getSchoolByIdOrUuid($schoolUuid);

        if($school){
            $timeFrame = $school->getCurrentTimeFrame($year);
            return JsonBuilder::Success(['time_frame'=>$timeFrame]);
        }
        else{
            return JsonBuilder::Error();
        }
    }

    /**
     * 添加课程表和选修课选择时间共用一个接口
     * 加载所有的学习时间段, 以及学期中用来学习的总周数
     * @param MyStandardRequest $request
     * @return string
     */
    public function load_study_time_slots(MyStandardRequest $request){
        $schoolIdOrUuid = $request->get('school');
        $noTime = $request->get('no_time', false);
        $year = $request->get('year');
        if(is_null($year)) {
            $gradeId = $request->getGradeId();
            if(is_null($gradeId)) {
                return JsonBuilder::Error('却少参数');
            }
            $gradeDao = new GradeDao();
            $grade = $gradeDao->getGradeById($gradeId);
            $year = $grade->gradeYear();
        }
        $school = null;
        $schoolDao = new SchoolDao(new User());

        if(strlen($schoolIdOrUuid) > 10){
            $school = $schoolDao->getSchoolByIdOrUuid($schoolIdOrUuid);
            if($school){
                $schoolIdOrUuid = $school->id;
            }else{
                $schoolIdOrUuid = null;
            }
        }else{
            $school = $schoolDao->getSchoolById($schoolIdOrUuid);
        }
        if($schoolIdOrUuid && $school){
            $timeSlotDao = new TimeSlotDao();
            $field = ConfigurationTool::KEY_STUDY_WEEKS_PER_TERM;

            $timeFrame = $timeSlotDao->getAllStudyTimeSlots($schoolIdOrUuid, $year,true, $noTime);
            $data = [
                'time_frame'=> $timeFrame,
                'total_weeks'=>$school->configuration->$field,
            ];

            return JsonBuilder::Success($data);
        }
        return JsonBuilder::Error();
    }

    /**
     * @param School $school
     * @param SchoolDao $dao
     */
    private function _getStudyWeeksCount($school, $dao){
        $dao->getSchoolConfig($school, ConfigurationTool::KEY_STUDY_WEEKS_PER_TERM);
    }


    /**
     * 获取所有的作息时间
     * @param MyStandardRequest $request
     * @return string
     */
    public function getAllTimeSlot(MyStandardRequest $request) {
        $schoolId = $request->get('school_id');
        if(empty($schoolId)) {
            return JsonBuilder::Error('school_id 不能为空');
        }
        $timeSlotDao = new TimeSlotDao();
        $timeslots = $timeSlotDao->getAllTimeSlots($schoolId);
        $configuration = SchoolConfiguration::where('school_id',$schoolId)->first();
        $year = $configuration->yearText();
        $data = [];
        if(count($timeslots) == 0) {
            return JsonBuilder::Success($data);
        }
        foreach ($timeslots as $key => $item) {
            $data[$item->year]['name'] = $year[$item->year - 1]['text'];
            $data[$item->year]['time_slot'][] = $item;
        }
        $data = array_merge($data);
        return JsonBuilder::Success($data);
    }


    /**
     * 获取课节的所有类型
     * @param MyStandardRequest $request
     * @return string
     */
    public function getTimeSlotType(MyStandardRequest $request) {
        $allType = TimeSlot::AllTypes();
        $data = [];
        foreach ($allType as $key => $item) {
            $data[] = [
                'id' => $key,
                'name' => $item
            ];
        }
        return JsonBuilder::Success($data);
    }
}
