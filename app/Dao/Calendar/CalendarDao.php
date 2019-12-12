<?php


namespace App\Dao\Calendar;

use App\Models\Schools\SchoolCalendar;
use App\Models\Schools\SchoolConfiguration;
use App\Utils\Time\CalendarDay;
use App\Utils\Time\CalendarWeek;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class CalendarDao
{

    /**
     * 新增
     * @param $data
     * @return mixed
     */
    public function createCalendarEvent($data)
    {
        return SchoolCalendar::create($data);
    }

    /**
     * 修改
     * @param $data
     * @return mixed
     */
    public function updateCalendarEvent($data)
    {
        $event = SchoolCalendar::find($data['id']);
        if($event){
            $event->tag = $data['tag'];
            $event->content = $data['content'];
            $event->event_time = $data['event_time'];
            return $event->save();
        }
    }

    /**
     * 获取校历的事件
     * @param $schoolId
     * @return mixed
     */
    public function getCalendarEvent($schoolId)
    {
        return SchoolCalendar::where('school_id', $schoolId)->select('id' ,'tag', 'content', 'event_time')->orderBy('event_time')->get();
    }

    /**
     * 获取事件详情
     */
    public function getEventById($id)
    {
        return SchoolCalendar::find($id);
    }

    /**
     * @param $start
     * @param $end
     * @param $schoolId
     * @return Collection
     */
    public function getBetween($start, $end, $schoolId){
        return SchoolCalendar::where('school_id', $schoolId)
            ->where('event_time','>=',$start)
            ->where('event_time','<=',$end)
            ->select('id' ,'tag', 'content', 'event_time')
            ->get();
    }

    /**
     * @param SchoolConfiguration $configuration
     * @param null $year
     * @param null $month
     * @param null $term
     * @return array
     */
    public function getCalendar(SchoolConfiguration $configuration, $year = null, $month = null, $term = null){
        $weeks = $configuration->getAllWeeksOfTerm($term);

        // 根据给定的年和月, 获取所需要的天数
        $days = [];
        $firstDayOfMonth = null;
        $lastDayOfMonth = null;
        if($year && $month){
            $firstDayOfMonth = Carbon::create($year, $month, 1);
            $lastDayOfMonth = Carbon::create($year,$month,$firstDayOfMonth->lastOfMonth()->day);
        }
        else{
            $firstDayOfMonth = Carbon::now()->firstOfMonth();
            $lastDayOfMonth = Carbon::now()->lastOfMonth();
        }

        $firstDay = CalendarDay::GetFirstDayOfTheWeek($firstDayOfMonth);
        $firstDayText = $firstDay->format('Y-m-d');
        $firstDay->subDay();

        $lastDay = CalendarDay::GetLastDayOfTheWeek($lastDayOfMonth);

        $diff = $firstDay->diffInDays($lastDay);

        foreach (range(1, $diff) as $num) {
            $key = $firstDay->addDay()->format('Y-m-d');
            $days[$key] = new CalendarDay(
                Carbon::createFromFormat('Y-m-d',$key),
                $weeks
            );
        }

        $events = (new CalendarDao())->getBetween($firstDayText, $lastDay, $configuration->getSchoolId());


        $daysFinal = [];
        foreach ($events as $event) {
            $key = $event->event_time->format('Y-m-d');
            $days[$key]->addEvent($event);
        }
        foreach ($days as $day) {
            $daysFinal[] = $day;
        }
        return [
            'days' =>$daysFinal,
            'weeks'=>$weeks,
        ];
    }
}
