<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 27/10/19
 * Time: 5:27 PM
 */

namespace App\BusinessLogic\TimetableLogic;
use App\Dao\Timetable\TimetableItemDao;
use App\Models\Timetable\TimetableItem;
use App\Utils\Time\CalendarDay;
use App\Utils\Time\GradeAndYearUtil;

class SpecialItemsLoadLogic
{
    protected $ids;
    public function __construct($ids)
    {
        $this->ids = $ids;
    }

    public function build(){
        $dao = new TimetableItemDao();

        /**
         * @var TimetableItem[] $items
         */
        $items = $dao->getItemsByIdArray($this->ids);

        $result = [];

        foreach ($items as $item) {
            $grade = $item->grade->name;
            if($item->type == TimetableItem::TYPE_SUBSTITUTION_NOTHING) {

                if($item->to_replace != 0) {
                    $timetable = $dao->getItemById($item->to_replace);

                } else {
                    $timetable = $dao->getItemById($item->substitute_id);
                }
                $grade = $timetable->grade->name;
            }
            $start_time = $item->at_special_datetime;
            $end_time = $item->to_special_datetime;
            $days = CalendarDay::getDays($start_time, $end_time,$item->weekday_index);


            $result = [
                'timetable_id' => $item->id,
                'type' => $item->getTypeText(),
                'initiative' => $item->getInitiativeText(),
                'start_time' => $start_time,
                'end_time' => $end_time,
                'course' => $item->course->name ?? '-',
                'room' => $item->room_id ?  $item->building->name . ' - ' .$item->room->name : '-',
                'practical_start_time' => $days[0],
                'teacher' => $item->teacher->name ?? '-',
                'updated_by' => $item->updatedBy->name ?? '',
                'course_source' => $grade.' '. CalendarDay::GetWeekDayIndex($item->weekday_index) . ' '. $item->timeSlot->name,
            ];
        }

        return $result;
    }
}
