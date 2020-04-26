<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 31/10/19
 * Time: 6:47 PM
 */

namespace App\BusinessLogic\TimetableViewLogic\Impl;

use App\Dao\Schools\GradeDao;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Dao\Timetable\TimeSlotDao;
use App\Dao\Timetable\TimetableItemDao;
use App\BusinessLogic\TimetableViewLogic\Contracts\ITimetableBuilder;

abstract class AbstractPointView implements ITimetableBuilder
{
    protected $schoolId;
    protected $weekType;
    protected $term;
    protected $year;
    protected $gradeId;
    protected $gradeYear;
    protected $timetableItemDao;
    protected $timeSlotDao;

    /**
     * 查询的必要提交是班级 id, 年和学期
     * TimetableBuilderLogic constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->year = $request->get('year') ?? Carbon::now()->year;
        $this->term = $request->get('term');
        $this->schoolId = $request->get('school');
        $this->weekType = intval($request->get('weekType')); // 指示位: 是否为单双周
        $this->gradeId = $request->get('grade');
        $gradeDao = new GradeDao();
        $grade = $gradeDao->getGradeById($this->gradeId);
        $this->gradeYear = $grade->gradeYear();  // 年级

        $this->timeSlotDao = new TimeSlotDao();
        // 找到所有的和学习相关的时间段
        $forStudyingSlots = $this->timeSlotDao->getAllStudyTimeSlots($this->schoolId, $this->gradeYear);
//        dd($forStudyingSlots);
        // 构建课程表项的 DAO
        $this->timetableItemDao = new TimetableItemDao($forStudyingSlots);
    }
}