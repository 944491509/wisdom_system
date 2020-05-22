<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 31/10/19
 * Time: 3:35 PM
 */

namespace App\BusinessLogic\TimetableViewLogic\Impl;
use App\Models\Timetable\TimetableItem;
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
                        $specials = [];
                        foreach ($res as $k => $item) {

                            // 判断类型 代课
                            if($item->type == TimetableItem::TYPE_SUPPLY) {
                                $specials[$k] = $item->id;
                            } else {
                                // 调课 课程互换
                                $specials[$k] = $item->substitute_id;
                            }

                        }

                        $re[$key]['specials'] = $specials;
                    }
                }
                else {
                    // 查询当前时间没有课 被调来来一节课
                        $map = [
                            ['to_replace', '>', 0],
                            ['to_special_datetime', '>=', $today],
                            ['time_slot_id', '=', $key],
                            ['weekday_index','=',$weekDayIndex],
                            ['grade_id', '=', $this->gradeId],
                            ['year', '=', $this->year],
                            ['term', '=', $this->term],
                        ];
                        $item = TimetableItem::where($map)->first();
                        if(!is_null($item)) {
                            $re[$key]['specials'] = [$item->id];
                        }
                }
            }

            $timetable[] = $re;
        }

        return empty($timetable) ? '' : $timetable;
    }
}
