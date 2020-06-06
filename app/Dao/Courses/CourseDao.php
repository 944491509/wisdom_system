<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 23/10/19
 * Time: 9:34 PM
 */

namespace App\Dao\Courses;
use App\Dao\Schools\MajorDao;
use App\Dao\Users\UserDao;
use App\Models\Course;
use App\Models\Courses\CourseArrangement;
use App\Models\Courses\CourseMajor;
use App\Models\Courses\CourseMaterial;
use App\Models\Courses\CourseTeacher;
use App\Models\Courses\TeachingLog;
use App\Models\ElectiveCourses\CourseElective;
use App\Models\Schools\Major;
use App\Utils\JsonBuilder;
use App\Utils\Misc\ConfigurationTool;
use App\Utils\ReturnData\IMessageBag;
use App\Utils\ReturnData\MessageBag;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use App\Dao\BuildFillableData;

class CourseDao
{
    use BuildFillableData;
    protected $fields = [
        'code','name','uuid','courses.id', 'scores', 'optional', 'year', 'term',
        'desc', 'duration',
    ];
    public function __construct()
    {

    }

    /**
     * 获取教学日志
     * @param $courseId
     * @param $teacherId
     * @param $skip
     * @return Collection
     */
    public function getTeachingLogs($courseId, $teacherId, $skip){
        return TeachingLog::select(['id','title','content','updated_at'])
            ->where('course_id',$courseId)
            ->where('teacher_id',$teacherId)
            ->orderBy('id','desc')
            ->skip($skip)
            ->take(10)
            ->get();
    }

    /**
     * @param $data
     * @return TeachingLog
     */
    public function saveTeachingLog($data){
        $log = null;
        if(empty($data['id'])){
            $log = TeachingLog::create($data);
        }
        else{
            TeachingLog::where('id',$data['id'])->update($data);
            $log = TeachingLog::find($data['id']);
        }
        return $log;
    }

    /**
     * 根据课程和老师, 获取课件的列表
     * @param $course
     * @param $teacher
     * @param $index
     * @return Collection
     */
    public function getCourseMaterials($course, $teacher, $index = null){
        $query = CourseMaterial::where('course_id',$course)
            ->where('teacher_id',$teacher)
            ->orderBy('index','asc')
            ->orderBy('type','asc');
        if($index){
            $query->where('index',$index);
        }
        return $query->get();
    }

    /**
     * 保存课件
     * @param $materialData
     * @return IMessageBag
     */
    public function saveMaterial($materialData){
        $bag = new MessageBag();
        try{
            if(empty($materialData['id'])){
                $material =  CourseMaterial::create($materialData);
                $bag->setData($material);
            }
            else{
                CourseMaterial::where('id',$materialData['id'])
                    ->update($materialData);
                $material =  CourseMaterial::find($materialData['id']);
                $bag->setData($material);
            }
        }catch (\Exception $exception){
            $bag->setCode(JsonBuilder::CODE_ERROR);
            $bag->setMessage($exception->getMessage());
        }
        return $bag;
    }

    /**
     * 根据id获取课件
     * @param $id
     * @return CourseMaterial
     */
    public function getCourseMaterial($id){
        return CourseMaterial::find($id);
    }

    /**
     * 根据id 删除课件
     * @param $id
     * @return mixed
     */
    public function deleteCourseMaterial($id){
        return CourseMaterial::where('id',$id)->delete();
    }

    /**
     * @deprecated 不要调用这个方法
     * @param $request
     * @return MessageBag
     */
    public function createMaterialOld($request){
        /**
         * @var UploadedFile $file
         */
        $file = $request->file('file');

        // 课件使用一个特殊的单独目录进行保存: /storage/course_material/course_id/teacher_id/
        $base = 'app/public/course_material';
        $materialData = json_decode($request->get('material'), true);
        $folderPath = $base.DIRECTORY_SEPARATOR.$materialData['course_id']
            .DIRECTORY_SEPARATOR.$materialData['teacher_id'];
        $folderPath = storage_path($folderPath);
        if(!file_exists($folderPath)){
            mkdir($folderPath, 0777, true);
        }

        if($file){
            $filePath = $file->storeAs(
                'public/course_material'.DIRECTORY_SEPARATOR.$materialData['course_id']
            .DIRECTORY_SEPARATOR.$materialData['teacher_id'],
                $file->getClientOriginalName()
            );
            $url = str_replace(
                'public',
                'storage',
                $filePath);
            $materialData['url'] = $url;
        }

        $bag = new MessageBag(JsonBuilder::CODE_ERROR);
        if(empty($materialData['id'])){
            try{
                $material = CourseMaterial::create($materialData);
                $bag->setCode(JsonBuilder::CODE_SUCCESS);
                $bag->setData($material);
            }
            catch (\Exception $exception){
                $bag->setMessage($exception->getMessage());
            }
        }
        else{
            try{
                $material = CourseMaterial::create($materialData);
                $bag->setCode(JsonBuilder::CODE_SUCCESS);
                $bag->setData($material);
            }
            catch (\Exception $exception){
                $bag->setMessage($exception->getMessage());
            }
        }

        return $bag;
    }

    /**
     * @param $idOrUuid
     * @return Course
     */
    public function getCourseByIdOrUuid($idOrUuid){
        if(strlen($idOrUuid) > 32){
            return $this->getCourseByUuid($idOrUuid);
        }
        else{
            return $this->getCourseById($idOrUuid);
        }
    }

    /**
     * @param $uuid
     * @return Course
     */
    public function getCourseByUuid($uuid){
        return Course::where('uuid',$uuid)->first();
    }

    /**
     * @param $id
     * @return Course
     */
    public function getCourseById($id){
        return Course::find($id);
    }

    /**
     * 根据 uuid 删除课程所有数据
     * @param $uuid
     * @return IMessageBag
     */
    public function deleteCourseByUuid($uuid){
        $bag = new MessageBag(JsonBuilder::CODE_ERROR);
        $course = $this->getCourseByUuid($uuid);
        if($course){

            if(count($course->timetableItems)){
                $bag->setMessage('该课程已经在课程表中安排了课时, 无法删除');
                return $bag;
            }

            DB::beginTransaction();
            try{
                $id = $course->id;
                $dao = new CourseArrangementDao($course);
                $dao->deleteByCourseId($id);
                CourseTeacher::where('course_id',$id)->delete();
                CourseMajor::where('course_id',$id)->delete();

                // Todo: 删除课程时, 同时也要删除选修课的申请表
                $course->delete();
                DB::commit();
                $bag->setCode(JsonBuilder::CODE_SUCCESS);
            }catch (\Exception $exception){
                DB::rollBack();
                $bag->setMessage($exception->getMessage());
            }
        }
        return $bag;
    }

    /**
     * @param $data
     * @return IMessageBag
     */
    public function updateCourse($data){
        $id = $data['id'];
        unset($data['id']);

        $teachersId = $this->_prepareTeachersId($data['teachers']);
        $majorsId = $data['majors'];

        $messageBag = new MessageBag(JsonBuilder::CODE_ERROR);

        DB::beginTransaction();
        try{
            // 先更新课程数据
            $filledData = $this->getFillableData(new Course(), $data);
            $course = Course::where('id',$id)->update($filledData);

            if($course){
                // 删除所有的授课老师
                CourseTeacher::where('course_id',$id)->delete();
                // 保存授课老师
                if(!empty($teachersId)){
                    $userDao = new UserDao();
                    foreach ($teachersId as $teacherId) {
                        // 先检查当前这条
                        $theTeacher = $userDao->getUserById($teacherId);
                        $d = [
                            'course_id'=>$id,
                            'course_code'=>$data['code'],
                            'teacher_id'=>$teacherId,
                            'school_id'=>$data['school_id'],
                            'teacher_name'=>$theTeacher->name ?? 'n.a',
                            'course_name'=>$data['name']
                        ];
                        CourseTeacher::create($d);
                    }
                }

                // 删除所有的关联专业
                CourseMajor::where('course_id',$id)->delete();
                // 保存课程所关联的专业
                if(!empty($majorsId)){
                    $majorDao = new MajorDao();
                    foreach ($majorsId as $majorId) {
                        $theMajor = $majorDao->getMajorById($majorId);
                        $d = [
                            'course_id'=>$id,
                            'course_code'=>$data['code'],
                            'major_id'=>$majorId,
                            'school_id'=>$data['school_id'],
                            'major_name'=>$theMajor->name ?? 'n.a',
                            'course_name'=>$data['name']
                        ];
                        CourseMajor::create($d);
                    }
                } else { // 所有专业都开放
                    $d = [
                        'course_id'=>$id,
                        'course_code'=>$data['code'],
                        'major_id'=> 0,
                        'school_id'=>$data['school_id'],
                        'major_name'=>$theMajor->name ?? '对所有专业都开放',
                        'course_name'=>$data['name']
                    ];
                    CourseMajor::create($d);
                }

                // 检查是选修课还是必修课, 如果是选修课, 则需要保留选修课的上课时间信息, 并保存到单独的记录表中
                if(intval($data['optional']) === 1){
                    // 是选修课
                    $this->_saveCourseArrangement($course, $data);
                }

                DB::commit();
                $messageBag->setCode(JsonBuilder::CODE_SUCCESS);
            }
            else{
                DB::rollBack();
                $messageBag->setMessage('无法更新课程信息, 请联系管理员');
            }

        }catch (\Exception $exception){
            DB::rollBack();
            $messageBag->setMessage($exception->getMessage());
        }

        return $messageBag;
    }

    /**
     * 老师的数据, 可能会由于前段的原因, 进行了变形, 需要用这个方法整理一下
     * @param $teachersIdAndNameStringArray
     * @return array
     */
    private function _prepareTeachersId($teachersIdAndNameStringArray){
        $result = [];
        foreach ($teachersIdAndNameStringArray as $item) {
            $arr = explode('ID:',$item);
            if(count($arr)>1){
                $result[] = $arr[1];
            }
            else{
                $result[] = $arr[0];
            }
        }
        return $result;
    }

    /**
     * 创建课程的方法
     * @param $data
     * @return IMessageBag
     */
    public function createCourse($data){
        if(isset($data['id']) || empty($data['id'])){
            unset($data['id']);
        }
        $teachersId = $this->_prepareTeachersId($data['teachers']);

        $majorsId = $data['majors'];

        $messageBag = new MessageBag(JsonBuilder::CODE_ERROR);
        $courseModel = new Course();
        // 验证唯一性
        $map = ['school_id'=>$data['school_id'], 'code'=>$data['code']];
        $info = $courseModel->where($map)->first();
        if(!is_null($info)) {
            $messageBag->setMessage('该课程编号已存在');
            return $messageBag;
        }

        DB::beginTransaction();
        try{
            $data['uuid'] = Uuid::uuid4()->toString();
            $fillableData = $this->getFillableData($courseModel,$data);

            // 先保存课程数据
            $course = Course::create($fillableData);

            if($course){
                // 保存授课老师
                if(!empty($teachersId)){
                    $teacherDao = new UserDao();
                    foreach ($teachersId as $teacherId) {
                        $theTeacher = $teacherDao->getUserById($teacherId);
                        $d = [
                            'course_id'=>$course->id,
                            'course_code'=>$course->code,
                            'teacher_id'=>$teacherId,
                            'school_id'=>$data['school_id'],
                            'teacher_name'=>$theTeacher->name ?? 'n.a',
                            'course_name'=>$course->name
                        ];
                        CourseTeacher::create($d);
                    }
                }
                // 保存课程所关联的专业
                if(!empty($majorsId)){
                    $majorDao = new MajorDao();
                    foreach ($majorsId as $majorId) {
                        $theMajor = $majorDao->getMajorById($majorId);
                        $d = [
                            'course_id'=>$course->id,
                            'course_code'=>$course->code,
                            'major_id'=>$majorId,
                            'school_id'=>$data['school_id'],
                            'major_name'=>$theMajor->name ?? 'n.a',
                            'course_name'=>$course->name
                        ];
                        CourseMajor::create($d);
                    }
                } else { // 对所有专业开放
                    $d = [
                        'course_id'=>$course->id,
                        'course_code'=>$course->code,
                        'major_id'=>0, // 所有专业都开放
                        'school_id'=>$data['school_id'],
                        'major_name'=>$theMajor->name ?? '对所有专业都开放',
                        'course_name'=>$course->name
                    ];
                    CourseMajor::create($d);
                }

                // 检查是选修课还是必修课, 如果是选修课, 则需要保留选修课的上课时间信息, 并保存到单独的记录表中
                if(intval($data['optional']) === Course::ELECTIVE_COURSE){
                    // 是选修课
                    $this->_saveCourseArrangement($course, $data);
                    //添加course_electives表的关联数据
                    $this->_saveCourseElective($course, $data);

                }

                DB::commit();
                $messageBag->setCode(JsonBuilder::CODE_SUCCESS);
                $messageBag->setData($course);
            }
            else{
                DB::rollBack();
                $messageBag->setMessage('保存课程数据失败, 请联系管理员');
            }

        }catch (\Exception $exception){
            DB::rollBack();
            $messageBag->setMessage($exception->getMessage());
        }

        return $messageBag;
    }

    /**
     * Course
     * @param $course
     * @param $data
     * @return bool
     */
/*    private function _saveCourseArrangement($course, $data){
        $days = $data['dayIndexes'];
        $timeSlotIds = $data['timeSlots'];
        $weeks = $data['weekNumbers'];
        $arrangement = new CourseArrangementDao($course);
        return $arrangement->save($weeks, $days, $timeSlotIds);
    }*/

    /**
     * 根据学校的 ID 获取课程
     * @param $schoolId
     * @param $pageNumber
     * @param $pageSize
     * @return array|Collection
     */
    public function getCoursesBySchoolId($schoolId, $pageNumber=0, $pageSize=20){
        $courses = Course::where('school_id',$schoolId)
//            ->skip($pageNumber * $pageSize)
//            ->take($pageSize)
            ->get();
        $data = [];
        foreach ($courses as $course) {
            /**
             * @var Course $course
             */
            $item = [];
            foreach ($this->fields as $field) {
                $item[$field] = $course->$field;
            }
            $item['teachers'] = $course->teachers;
            $item['majors'] = $course->majors;
            // 课程的教材
            $item['books'] = [];
            foreach ($course->courseTextbooks as $ct){
                $i['id'] = $ct->textbook->id;
                $i['name'] = $ct->textbook->name . '('.$ct->textbook->edition.')';
                $item['books'][] = $i;
            }

            $item['arrangements'] = [];
            if($course->optional){
                // 是选修课
                $item['arrangements'] = $course->arrangements;
            }

            $data[] = $item;
        }
        return $data;
    }


    /**
     * 课程分页
     * @param $data
     * @return mixed
     */
    public function getCoursePageBySchoolId($data) {
        $map = [['courses.school_id', '=', $data['school']]];
        if(!empty($data['year'])) {
            $map[] = ['year', '=', $data['year']];
        }
        if(!empty($data['term'])) {
            $map[] = ['term', '=', $data['term']];
        }
        if(!empty($data['keyword'])) {
            $map[] = ['name', 'like', $data['keyword'].'%'];
        }

        $return = Course::where($map)
            ->with('majors')->whereHas('majors', function ($que) use ($data){
                $que->when(!empty($data['major_id']) ,function ($que)  use($data){
                    return $que->where('major_id', $data['major_id'])
                        ->groupBy('course_id');});
                });

//            ->with('teachers')->whereHas('teachers', function ($que) use ($data) {
//                $que->where('teacher_id', $data['teacher_id'])
//                    ->groupBy('course_id');})
        if($data['download']) {
            $courses = $return->get();
        } else {
            $return = $return->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);
            $result = pageReturn($return);
            $courses = $result['list'];
        }

        $list = [];
        foreach ($courses as $key => $course) {
            unset($course->majors);
            /**
             * @var Course $course
             */
            $item = $course->toArray();

            $item['teachers'] = $course->teachers;
            $majors = $course->majors;
            if(!empty($data['major_id'])) {
                $majors = $course->majors->where('id', $data['major_id']);
            }
            $item['majors'] = $majors;
            // 课程的教材
            $item['books'] = [];
            foreach ($course->courseTextbooks as $ct){
                $i['id'] = $ct->textbook->id;
                $i['name'] = $ct->textbook->name . '('.$ct->textbook->edition.')';
                $i['press'] = $ct->textbook->press;
                $i['author'] = $ct->textbook->author;
                $i['type'] = $ct->textbook->typeText;
                $item['books'][] = $i;
            }

            $item['arrangements'] = [];
            if($course->optional){
                // 是选修课
                $item['arrangements'] = $course->arrangements;
            }

            $list[] = $item;
        }
        $result['list'] = $list;

        return $result;
    }


    /**
     * 通过idArr查询课程列表
     * @param $idArr
     * @param $simple
     * @return mixed
     */
    public function getCoursesByIdArr($idArr,$simple = true) {
        $field = '*';
        if($simple) {
            $field = ['id', 'code', 'name', 'year', 'term', 'scores'];
        }
        $result = Course::whereIn('id',$idArr)->with('courseTextbooks.textbook')->select($field)->get();
        return $result;
    }

    /*
     *
            [
                1=>[1=>[7,8],3=>[7]],
                2=>[1=>[7,8],3=>[7]],
                3=>[1=>[7,8],3=>[7]],
            ],
     *
     */
    private function _saveCourseArrangement($course, $data){

        // 保存课时安排
        if (!empty($data['group'])) {
            $times = $data['group'];
            DB::beginTransaction();
            try {
                foreach ($times as $time) {
                    $d = [
                        'course_id'     => $course->id,
                        'week'          => $time['week'],
                        'day_index'     => $time['day_index'],
                        'time_slot_id'  => $time['time_slot_id'],
                        'building_id'   => $time['building_id'],
                        'building_name' => $time['building_name'],
                        'classroom_id'  => $time['classroom_id'],
                        'classroom_name'=> $time['classroom_name'],
                    ];
                    $courseArrangement = CourseArrangement::create($d);
                }
                DB::commit();
            } catch (\Exception $exception) {
                DB::rollBack();
            }
        }
    }

    /**
     * 保存course_electives表数据
     * @param $course
     * @param $data
     */
    private function _saveCourseElective($course, $data)
    {
        DB::beginTransaction();
        try {
            $d = [
                'course_id'     => $course->id,
                'open_num'      => $data['open_num'],
                'max_num'       => $data['max_num'],
                'start_year'    => $data['start_year'],
                'enrol_start_at'=> $data['enrol_start_at'],
                'expired_at'    => $data['expired_at'],
            ];
            CourseElective::create($d);
            DB::commit();
        } catch (\Exception $exception) {
            dd($exception);
            DB::rollBack();
        }

    }


    /**
     * 根据课程ID和学年获取课程
     * @param $ids
     * @param $year
     * @return mixed
     */
    public function getCourseByIdsAndYear($ids, $year){
        return Course::whereIn('id',$ids)->where('year', $year)->get();
    }


    /**
     * 修改课程描述
     * @param $courseId
     * @param $desc
     * @return mixed
     */
    public function updateCourseDescByCourseId($courseId, $desc) {
        return Course::where('id', $courseId)
            ->update(['desc'=>$desc]);
    }

}
