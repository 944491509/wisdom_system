<?php
namespace App\Http\Controllers\Operator;

use App\Dao\Schools\BuildingDao;
use App\Dao\Schools\CampusDao;
use App\Dao\Schools\RoomDao;
use App\Models\School;
use App\Models\Schools\Facility;
use App\Utils\FlashMessageBuilder;
use App\Http\Controllers\Controller;
use App\Dao\FacilityManage\FacilityDao;
use App\Http\Requests\FacilityManage\MonitoringRequest;
use App\Utils\JsonBuilder;
use Illuminate\Support\Facades\Storage;


class FacilityController extends Controller
{

    public function __construct() {
        $this->middleware('auth');
    }

    public function list(MonitoringRequest $request) {


        $schoolId = $request->getSchoolId();
        $facilityDao = new FacilityDao();
        $map = ['school_id' => $schoolId];
        $result = $facilityDao->getFacilityPage($map);

        $this->dataForView['facility'] = $result;

        return view('school_manager.facility.list', $this->dataForView);

    }


    public function add(MonitoringRequest $request) {

        $facilityDao = new FacilityDao();
        if($request->isMethod('post')) {
            $all = $request->post('facility');
            $all['school_id'] = $request->getSchoolId();
            $result = $facilityDao->save($all);
            if($result) {
                FlashMessageBuilder::Push($request, FlashMessageBuilder::SUCCESS,'保存成功');
            } else {
                FlashMessageBuilder::Push($request, FlashMessageBuilder::DANGER,'保存失败');
            }
            return redirect()->route('school_manager.facility.list');

        }
        $user = $request->user();
        $field = ['id', 'name'];
        $campusDao = new CampusDao($user);
        $schoolId = $request->getSchoolId();
        $campus = $campusDao->getCampusesBySchool($schoolId,$field);
        $this->dataForView['campus'] = $campus;
        $this->dataForView['type'] = $facilityDao->getType();
        return view('school_manager.facility.add', $this->dataForView);

    }


    public function edit(MonitoringRequest $request) {
        $facilityDao = new FacilityDao();
        if($request->isMethod('post')) {
            $all = $request->post('facility');
            if ($all['card_type'] == Facility::CARD_TYPE_PUBLIC) {
                unset($all['grade_id']);
            }
            $result = $facilityDao->save($all);
            if($result) {
                FlashMessageBuilder::Push($request, FlashMessageBuilder::SUCCESS,'编辑成功');
            } else {
                FlashMessageBuilder::Push($request, FlashMessageBuilder::DANGER,'编辑失败');
            }
            return redirect()->route('school_manager.facility.list');
        }

        $user = $request->user();
        $schoolId = $request->getSchoolId();
        $id = $request->get('id');
        $result = $facilityDao->facilityInfoDispose($id,$user,$schoolId);
        $this->dataForView['facility'] = $result['facility'];
        $this->dataForView['campus'] = $result['campus'];
        $this->dataForView['building'] = $result['building'];
        $this->dataForView['room'] = $result['room'];
        $this->dataForView['type'] = $facilityDao->getType();

        return view('school_manager.facility.edit', $this->dataForView);
    }


    public function delete(MonitoringRequest $request) {
        $id = $request->get('id');
        $facilityDao = new FacilityDao();
        $result = $facilityDao->delete($id);
        if($result) {
            FlashMessageBuilder::Push($request, FlashMessageBuilder::SUCCESS,'删除成功');
        } else {
            FlashMessageBuilder::Push($request, FlashMessageBuilder::DANGER,'删除失败');
        }
        return redirect()->route('school_manager.facility.list');
    }

    /**
     * 上传班牌视频
     * @param MonitoringRequest $request
     */
    public function video(MonitoringRequest $request)
    {
        $school = new School();
        $schoolId = $request->getSchoolId();
        if($request->isMethod('post')) {
            $file = $request->file('video');
            $fileName = $file->getClientOriginalName();
            $path = $file->storeAs('public/school/video',$fileName);
            $path = Storage::url($path);
            $school->where(['id'=>$schoolId])->update(['video'=>$path]);
        }
        $school = $school->where(['id'=>$schoolId])->first();
        $this->dataForView['video'] = $school['video'];
        return view('school_manager.facility.video', $this->dataForView);
    }

    /**
     * 验证文件是否合法
     */
    public function upload($file) {
        // 1.是否上传成功
        if (! $file->isValid()) {
            return false;
        }

        // 2.是否符合文件类型 getClientOriginalExtension 获得文件后缀名
        $fileExtension = $file->getClientOriginalExtension();
        if(! in_array($fileExtension, ['mp4'])) {
            return false;
        }
        // 3.判断大小是否符合 2M
        $tmpFile = $file->getRealPath();
        // 5.每天一个文件夹,分开存储, 生成一个随机文件名
        $fileName = date('Y_m_d').'/'.md5(time()) .mt_rand(0,9999).'.'. $fileExtension;
        if (Storage::disk('public')->put($fileName, file_get_contents($tmpFile)) ){
            return Storage::url($fileName);
        }
    }





    /**
     * 根据校区获取建筑列表
     * @param MonitoringRequest $request
     * @return string
     */
    public function getBuildingList(MonitoringRequest $request) {
        $campusId = $request->get('campus_id');
        $user = $request->user();
        $buildingDao = new BuildingDao($user);
        $list = $buildingDao->getBuildingByCampusId($campusId)->toArray();
        if(!empty($list)) {
            $result = ['building'=>$list];
            return JsonBuilder::Success($result,'请求成功');
        } else {
            return JsonBuilder::Error('暂无数据',0);
        }
    }


    /**
     * 根据建筑获取教室列表
     * @param MonitoringRequest $request
     * @return string
     */
    public function getRoomList(MonitoringRequest $request) {
        $roomId = $request->get('building_id');
        $user = $request->user();
        $roomDao = new RoomDao($user);
        $list = $roomDao->getRoomByBuildingId($roomId)->toArray();
        if(!empty($list)) {
            $result = ['room'=>$list];
            return JsonBuilder::Success($result,'请求成功');
        } else {
            return JsonBuilder::Error('暂无数据',0);
        }
    }


}
