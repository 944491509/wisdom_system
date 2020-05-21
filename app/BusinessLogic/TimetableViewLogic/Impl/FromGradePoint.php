<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 31/10/19
 * Time: 3:35 PM
 */

namespace App\BusinessLogic\TimetableViewLogic\Impl;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FromGradePoint extends AbstractPointView
{
    protected $gradeId;

    /**
     * 查询的必要提交是班级 id, 年和学期
     * TimetableBuilderLogic constructor.
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->gradeId = $request->get('grade');
    }

    /**
     * 创建课程表的具体方法
     * @return mixed
     */
    public function build(){

        $today = Carbon::today();
        $timetable = [];
        foreach (range(1, 7) as $weekDayIndex) {
            $re = $this->timetableItemDao->getItemsByWeekDayIndex(
                $weekDayIndex, $this->year, $this->term, $this->weekType, $this->gradeId
            );
            // 查询是否有调课
            foreach ($re as $key => $val) {
                if(!empty($val)) {
                    $map = [
                        ['to_replace', '=', $val['id']],
                        ['to_special_datetime', '>=', $today]
                    ];
                    $res = $this->timetableItemDao->getTimetable($map);
                    if(count($res) >0) {
                        $re[$key]['specials'] = array_column($res->toArray(),'id');
                    }
                }
            }

            $timetable[] = $re;
        }
        // 检查从当前时刻起的特殊情况, 主要就是调课

//        $specialCases = $this->timetableItemDao->getSpecialsAfterToday(
//            $this->year, $this->term, $this->gradeId, $today
//        );
//
//        $specialKeys = array_keys($specialCases);
//        foreach ($timetable as $idx=>$column) {
//            foreach ($column as $key=>$item) {
//                if(!empty($item) && in_array($item['id'], $specialKeys)){
//                    // 在 specials 放入所有调课的特殊 item 的 id 值数组
//                    $timetable[$idx][$key]['specials'] = $specialCases[$item['id']];
//                }
//            }
//        }
        return empty($timetable) ? '' : $timetable;
    }
}
