<?php

namespace App\Http\Controllers\Api\School;

use App\User;
use App\Models\School;
use App\Utils\JsonBuilder;
use Illuminate\Http\Request;
use App\Dao\Schools\GradeDao;
use App\Dao\Schools\SchoolDao;
use App\Models\Timetable\TimeSlot;
use App\Dao\Timetable\TimeSlotDao;
use App\Http\Controllers\Controller;
use App\Utils\Misc\ConfigurationTool;
use App\Http\Requests\MyStandardRequest;
use App\Models\Schools\SchoolConfiguration;

class TimeSlotsController extends Controller
{

    /**
     * 编辑作息时间
     * @param Request $request
     * @return string
     */
    public function save_time_slot(Request $request){
        $tsDao = new TimeSlotDao();
        $timeSlot = $request->all();
        $id = $timeSlot['id'];
        $ts = $tsDao->getById($id);
        if(is_null($ts)){
           return JsonBuilder::Error('该课节不存在');
        } else {
            unset($timeSlot['id']);
            $re = $tsDao->update($id, $timeSlot);
            if($re) {
                return JsonBuilder::Success('编辑成功');
            } else {
                return JsonBuilder::Success('编辑失败');
            }
        }
    }


    /**
     * 加载学校年级
     * @param Request $request
     * @return string
     */
    public function load_year_school(Request $request)
    {

        $schoolId = $request->get('school_id');

        if(empty($schoolId)) {
            $schoolId = $request->user()->getSchoolId();
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
        foreach ($year as $key => $val) {
            $data[$key]['year'] = $val;
        }
        if(count($timeslots) == 0) {
            return JsonBuilder::Success($data);
        }
        foreach ($timeslots as $key => $item) {
            $data[$item->year-1]['time_slot'][] = $item;
        }
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

    /**
     * 添加作息时间
     * @param MyStandardRequest $request
     * @return string
     * @throws \Illuminate\Validation\ValidationException
     */
    public function addTimeSlot(MyStandardRequest $request) {
        $rules = [
            'school_id' => 'required|int',
            'type' => 'required|int',
            'from' => 'required|date_format:H:i',
            'to' => 'required|date_format:H:i|after_or_equal:from',
            'name' => 'required',
            'year' => 'required|int',
        ];

        $this->validate($request,$rules);
        $data = $request->all();
        $timeSlotDao = new TimeSlotDao();
        $result = $timeSlotDao->createTimeSlot($data);
        if($result) {
            return JsonBuilder::Success(['id'=>$result->id],'创建成功');
        } else {
            return JsonBuilder::Error('创建失败');
        }
    }


    /**
     * 删除课节
     * @param MyStandardRequest $request
     * @return string
     * @throws \Illuminate\Validation\ValidationException
     */
    public function delTimeslot(MyStandardRequest $request) {
        $rules = ['time_slot_id' => 'required|int'];
        $this->validate($request,$rules);
        $timeSlotId = $request->get('time_slot_id');
        $timeSlotDao = new TimeSlotDao();
        $timeSlot = $timeSlotDao->getById($timeSlotId);
        if(is_null($timeSlot)) {
            return JsonBuilder::Error('该节课不存在');
        }
        $timetableItems = $timeSlot->timeTableItems;
        if(count($timetableItems) > 0) {
            return JsonBuilder::Error('已经有课程表使用不能删除');
        }
        // 删除
        $re = $timeSlotDao->delTimeSlot($timeSlotId);
        if($re) {
            return JsonBuilder::Success('删除成功');
        } else {
            return JsonBuilder::Error('删除失败');
        }
    }
}
