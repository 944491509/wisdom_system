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
            $grade = [
                'id' => $item->grade->id,
                'name' => $item->grade->name,
            ];
            if($item->type == TimetableItem::TYPE_SUBSTITUTION_NOTHING) {

                if($item->to_replace != 0) {
                    $timetable = $dao->getItemById($item->to_replace);

                } else {
                    $timetable = $dao->getItemById($item->substitute_id);
                }
                $grade = [
                    'id' => $timetable->grade->id,
                    'name' => $timetable->grade->name,
                ];
            }
            $result[] = [
                'id'        =>$item->id,
                'date'      =>$item->at_special_datetime->format(GradeAndYearUtil::DEFAULT_FORMAT_DATE),
                'end_time'  => $item->to_special_datetime->format(GradeAndYearUtil::DEFAULT_FORMAT_DATE),
                'grade' => $grade,
                'course'    =>$item->course->name ?? '-',
                'teacher'   =>$item->teacher->name ?? '-',
                'location'  =>$item->room_id ? $item->building->name . ' - ' .$item->room->name :'-' ,
                'updated_by'=>$item->updatedBy->name ?? '',
                'published' =>$item->published,
            ];
        }

        return $result;
    }
}
