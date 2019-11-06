<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 3/11/19
 * Time: 5:08 PM
 */

namespace App\Dao\RecruitmentPlan;
use App\Dao\Schools\SchoolDao;
use App\Models\Schools\RecruitmentPlan;
use App\Utils\Time\GradeAndYearUtil;
use Illuminate\Support\Facades\DB;

class RecruitmentPlanDao
{
    private $schoolId;
    public function __construct($schoolId){
        $this->schoolId = $schoolId;
    }

    public function getPlan($id){
        return RecruitmentPlan::find($id);
    }

    /**
     * @param $planData
     * @return RecruitmentPlan
     */
    public function createPlan($planData){
        if(empty($planData['tags'])){
            $planData['tags'] = $planData['major_name'];
        }
        return RecruitmentPlan::create($planData);
    }

    /**
     * 更新计划
     * @param $planData
     * @return bool
     */
    public function updatePlan($planData){
        $id = $planData['id']??null;
        unset($planData['id']);
        if($id){
            return RecruitmentPlan::where('id',$id)->update($planData);
        }
        return false;
    }

    public function deletePlan($id, $hardDelete = false){
        if($hardDelete){
            return DB::table('recruitment_plans')->delete($id);
        }
        else{
            return RecruitmentPlan::where('id',$id)->delete();
        }
    }

    /**
     * 获取某个学校的招生简章
     *
     * @param $schoolId
     * @param null $year
     * @param int $pageNumber
     * @param int $pageSize
     * @return RecruitmentPlan[]
     */
    public function getPlansBySchool($schoolId, $year = null, $pageNumber = 0, $pageSize = 20){
        $query =  RecruitmentPlan::where('school_id', $schoolId)
            ->where('year',$year)
            ->orderBy('updated_at','desc')
            ->skip($pageNumber * $pageSize)
            ->take($pageSize);

        if($year){
            $query->where('year',$year);
        }
        return $query->get();
    }

    /**
     * 加载对于今天依然有效的招生简章
     *
     * @param $today
     * @param $schoolId
     * @return RecruitmentPlan[]
     */
    public function getPlansBySchoolForToday($today, $schoolId){
        return  RecruitmentPlan::where('school_id', $schoolId)
            ->where('start_at','<=',$today->format('Y-m-d'))
            ->where(function ($query) use($today){
                $query->whereNull('end_at')->orWhere('end_at','>=',$today->format('Y-m-d'));
            })
            ->orderBy('id','asc')
            ->get();
    }


    /**
     *根据ID获取专业详情包括专业下的课程
     * @param $majorId
     * @return mixed
     */
    public function getMajorDetailById($majorId)
    {
        $data = RecruitmentPlan::where('major_id', $majorId)->select('id', 'major_id', 'seats', 'enrolled_count', 'applied_count')->with([
                    'courseMajor' => function ($query) {
                        $query->select('major_id', 'course_name');
                    },
                    'major' => function ($query) {
                        $query->select('id', 'name', 'fee', 'period', 'description');
                    }
                ])->first();

        $data = $data->toArray();
        $result = [];
        if (is_array($data) && !empty($data)) {
            $result['major']['id']          = is_null($data['major_id']) ? '' : $data['major_id'];
            $result['major']['seats']       = is_null($data['seats']) ? '' : $data['seats'];
            $result['major']['enrolled']    = is_null($data['enrolled_count']) ? '' : $data['enrolled_count'];
            $result['major']['applied']     = is_null($data['applied_count']) ? '' : $data['applied_count'];
            $result['major']['name']        = is_null($data['major']['name']) ? '' : $data['major']['name'];
            $result['major']['fee']         = is_null($data['major']['fee']) ? '' : $data['major']['fee'];
            $result['major']['period']      = is_null($data['major']['period']) ? '' : $data['major']['period'];
            $result['major']['description'] = is_null($data['major']['description']) ? '' : $data['major']['description'];

            foreach ($data['course_major'] as $key => $val) {
                $result['courses'][] = $val;
                unset($result['courses'][$key]['major_id']);
            }
        }

        return $result;
    }


    /**
     * 根据学校id 获取招生的专业
     * @param $schoolId
     * @return array
     */
    public function getAllMajorBySchoolId($schoolId)
    {
        $data = RecruitmentPlan::where('school_id', $schoolId)
                    ->select('id', 'major_id', 'seats', 'enrolled_count', 'applied_count', 'hot')->with([
                    'major' => function ($query) {
                        $query->select('id', 'name', 'fee', 'period');
                    }
                ])->orderBy('created_at', 'desc')->get();

        $result = [];
        foreach ($data as $key => $val) {
            $result[$key]['id']       = $val['id'];
            $result[$key]['major_id'] = $val['major_id'];
            $result[$key]['name']     = $val['major']['name'];
            $result[$key]['fee']      = $val['major']['fee'];
            $result[$key]['period']   = $val['major']['period'];
            $result[$key]['seats']    = $val['seats'];
            $result[$key]['enrolled'] = $val['enrolled_count'];
            $result[$key]['applied']  = $val['applied_count'];
        }

        return  $result;
    }


    /**
     * 根据ID 获取一条招生计划
     * @param $id
     * @return mixed
     */
    public function getRecruitmentPlanById($id)
    {
        return RecruitmentPlan::find($id);
    }

}