<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 19/12/19
 * Time: 8:39 PM
 */

namespace App\Http\Controllers\H5\Teacher;


use App\Dao\Calendar\CalendarDao;
use App\Dao\Schools\SchoolDao;
use App\Http\Controllers\Controller;
use App\Models\Schools\SchoolConfiguration;
use App\User;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function view(Request $request){
        $type = $request->get('type');
        /**
         * @var User $teacher
         */
        $teacher = $request->user('api');
        $this->dataForView['api_token'] = $request->get('api_token');
        $this->dataForView['teacher'] = $teacher;
        $school = (new SchoolDao())->getSchoolById($teacher->getSchoolId());
        $this->dataForView['school'] = $school;
        $this->dataForView['pageTitle'] = $type;


        $viewPath = 'h5_apps.teacher.news';

        switch ($type){
            case '通讯录':
                $viewPath = 'h5_apps.teacher.contact';
                break;
            case '校历':
                $viewPath = 'h5_apps.teacher.calendar';
                $data = $this->loadEvents($school->id, $school->configuration);
                $this->dataForView['events'] = $data['events'];
                $this->dataForView['tags'] = $data['tags'];
                break;
            case '值班':
                $viewPath = 'h5_apps.teacher.attendance';
                break;
            default:
                $this->dataForView['items'] = [];
                break;
        }

        return view($viewPath, $this->dataForView);
    }

    private function loadEvents($schoolId, SchoolConfiguration $configuration){
        $dao = new CalendarDao();
        $data = $dao->getCalendarEvent($schoolId);
        $weeks = $configuration->getAllWeeksOfTerm();
        $tags = [];
        foreach ($data as $datum) {
            $week = $configuration->getScheduleWeek($datum->event_time, $weeks);
            $datum->week = $week->getName();
        }
        return [
            'events'=>$data,
            'tags'=>$tags,
        ];
    }
}