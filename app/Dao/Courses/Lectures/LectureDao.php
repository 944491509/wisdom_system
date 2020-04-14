<?php
/**
 * 课件的DAO
 * Author: Justin Wang
 * Email: hi@yue.dev
 */
namespace App\Dao\Courses\Lectures;


use App\User;
use Carbon\Carbon;
use App\Utils\JsonBuilder;
use App\Dao\Users\GradeUserDao;
use App\Models\Courses\Lecture;
use App\Models\Users\GradeUser;
use App\Models\Courses\Homework;
use Illuminate\Support\Facades\DB;
use App\Utils\ReturnData\MessageBag;
use App\Utils\Time\GradeAndYearUtil;
use App\Utils\Misc\ConfigurationTool;
use App\Models\Courses\LectureMaterial;
use App\Models\Courses\LectureMaterialType;
use Illuminate\Database\Eloquent\Collection;

class LectureDao
{
    /**
     * @param $lectureId
     * @return Lecture
     */
    public function getLectureById($lectureId){
        return Lecture::find($lectureId);
    }

    /**
     * @param $courseId
     * @param $teacherId
     * @param $index
     * @return Lecture
     */
    public function getLecturesByCourseAndTeacherAndIndex($courseId, $teacherId,$index){
        $lecture = Lecture::where('course_id',$courseId)
            ->where('teacher_id',$teacherId)
            ->where('idx',$index)
            ->first();
        if(!$lecture){
            $lecture = Lecture::create([
                'course_id'=>$courseId,
                'teacher_id'=>$teacherId,
                'idx'=>$index,
                'title'=>'',
                'summary'=>'',
                'tags'=>'',
            ]);
        }
        return $lecture;
    }

    /**
     * @param $courseId
     * @param $teacherId
     * @return Collection
     */
    public function getLecturesByCourseAndTeacher($courseId, $teacherId){
        return Lecture::where('course_id',$courseId)
            ->where('teacher_id',$teacherId)
            ->orderBy('idx','asc')
            ->get();
    }

    /**
     * 根据课节的id获取其所有课件附件的记录
     * @param $lectureId
     * @return Collection
     */
    public function getLectureMaterials($lectureId){
        return LectureMaterial::where('lecture_id',$lectureId)
            ->orderBy('type','asc')
            ->get();
    }

    /**
     * @param $lectureId
     * @param $grades
     * @return Collection
     */
    public function getLectureHomework($lectureId, $grades){
        $result = new Collection();
        if($grades){
            $gradeStudents = (new GradeUserDao())->getGradeUserWhereInGrades($grades);
            $studentsIds = [];
            foreach ($gradeStudents as $gradeStudent) {
                /**
                 * @var GradeUser $gradeStudent
                 */
                if($gradeStudent->isStudent()){
                    $studentsIds[] = $gradeStudent->user_id;
                }
            }
            $yearAndTerm = GradeAndYearUtil::GetYearAndTerm(Carbon::now());
            $result = Homework::where('year', $yearAndTerm['year'])
                ->where('lecture_id',$lectureId)
                ->whereIn('student_id',$studentsIds)
                ->orderBy('id','desc')
                ->get();
        }
        return $result;
    }

    /**
     * 学生获取自己某节课的作业
     * @param $studentId
     * @param $courseId
     * @param $idx
     * @param $year
     * @return Collection
     */
    public function getHomeworkByStudentAndLectureAndYear($studentId, $courseId, $idx, $year){
        return Homework::where('year', $year)
            ->where('course_id',$courseId)
            ->where('idx',$idx)
            ->where('student_id',$studentId)
            ->orderBy('id','desc')
            ->get();
    }

    /**
     * @param $data
     * @return Homework
     */
    public function saveHomework($data){
        return Homework::create($data);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function deleteHomework($id){
        $homework = Homework::find($id);
        $filePath = $homework->url;
        if($filePath){
            $file = str_replace(env('APP_URL').'/storage','',$filePath);
            unlink(storage_path('app/public').$file);
        }
        return $homework->delete();
    }

    public function getLectureMaterial($materialId){
        return LectureMaterial::find($materialId);
    }

    /**
     * 更新课件的记录，注意这个方法只会更新title和summary这两个字段
     * @param $data
     * @return mixed
     */
    public function updateLectureSummary($data){
        return Lecture::where('id',$data['id'])->update($data);
    }

    /**
     * 保存某个课节的附件材料
     * @param $data
     * @return MessageBag
     */
    public function saveLectureMaterial($data){
        $bag = new MessageBag();
        try{
            if(empty($data['id'])){
                $material =  LectureMaterial::create($data);
                $bag->setData($material);
            }
            else{
                LectureMaterial::where('id',$data['id'])
                    ->update($data);
                $material =  LectureMaterial::find($data['id']);
                $bag->setData($material);
            }
        }catch (\Exception $exception){
            $bag->setCode(JsonBuilder::CODE_ERROR);
            $bag->setMessage($exception->getMessage());
        }
        return $bag;
    }


    /**
     * 获取学习资料的类型
     * @param $schoolId
     * @return mixed
     */
    public function getMaterialType($schoolId) {
        $map = ['school_id'=>$schoolId];
        $field = ['id as type_id', 'name'];
        return LectureMaterialType::where($map)->select($field)->get();
    }


    /**
     * @param $courseId
     * @param $gradeId
     * @param $teacherId
     * @param $type
     * @param $keyword
     * @return mixed
     */
    public function getMaterialsByType($courseId, $gradeId, $teacherId, $type, $keyword=null){
        $map = [
            'course_id'=>$courseId,
            'grade_id'=>$gradeId,
            'teacher_id'=>$teacherId,
            'type'=>$type,
        ];
        $result = LectureMaterial::where($map)->where('media_id','<>', 0);
        if(!is_null($keyword)) {

            $result = $result->where('description', 'like', '%'.$keyword.'%');
        }
        return $result->get();
    }


    /**
     * @param $gradeId
     * @return mixed
     */
    public function getMaterialByGradeId($gradeId) {
        $map = ['grade_id'=>$gradeId];
        return LectureMaterial::where($map)
            ->orderBy('created_at', 'desc')
            ->first();
    }


    /**
     * @param $courseIds
     * @param $keyword
     * @return mixed
     */
    public function getMaterialByKeyword($courseIds, $keyword) {
        return LectureMaterial::where('description', 'like', '%'.$keyword.'%')
            ->whereIn('course_id', $courseIds)
            ->get();
    }


    /**
     * 获取课程列表
     * @param $teacherId
     * @return mixed
     */
    public function getMaterialByTeacherId($teacherId) {
        return LectureMaterial::where('teacher_id', $teacherId)
            ->groupBy('course_id')
            ->select('course_id')
            ->get();
    }


    /**
     * 根据课程查询学习资料
     * @param $courseId
     * @param $type
     * @param $teacherId
     * @param bool $isPage
     * @return mixed
     */
    public function getMaterialByCourseId($courseId, $type, $teacherId, $isPage = true) {
        $map = ['course_id'=>$courseId, 'type'=>$type, 'teacher_id'=>$teacherId];
        $result = LectureMaterial::where($map)->where('media_id','<>', 0);

        if($isPage) {
            return $result->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);
        }

        return $result->get();
    }

    /**
     * 删除学习资料
     * @param User $user
     * @param $lectureId 主表ID
     * @param $type
     * @return MessageBag
     */
    public function deleteMaterial(User $user,$lectureId, $type) {
        $messageBag = new MessageBag();
        $info = Lecture::where('id', $lectureId)->first();
        if(is_null($info)) {
            $messageBag->setCode(JsonBuilder::CODE_ERROR);
            $messageBag->setMessage('该资料不存在');
            return $messageBag;
        }
        if($info->teacher_id != $user->id) {
            $messageBag->setMessage('您没有权限删除');
            $messageBag->setCode(JsonBuilder::CODE_ERROR);
            return $messageBag;
        }
        try{
            DB::beginTransaction();
            $map = ['lecture_id'=>$lectureId, 'type'=>$type];
            $re = LectureMaterial::where($map)->delete();
            // 判断还有当前课程资料是否还有其他类型
            $materials = LectureMaterial::where('lecture_id',$lectureId)->get();
            if(count($materials) == 0) {
                // 删除主表
                $info->delete();
            }

            DB::commit();
            $messageBag->setMessage('删除成功');

        }catch (\Exception $e) {
            DB::rollBack();
            $msg = $e->getMessage();
            $messageBag->setCode(JsonBuilder::CODE_ERROR);
            $messageBag->setMessage($msg);
        }

        return $messageBag;
    }


    /**
     * 获取数量
     * @param $teacherId
     * @param $type
     * @return mixed
     */
    public function getMaterialNumByUserAndType($teacherId, $type) {
        $map = ['teacher_id'=>$teacherId, 'type'=>$type];
        return LectureMaterial::where($map)
            ->select('lecture_id')
            ->distinct('lecture_id')
            ->get()->toArray();
    }


    /**
     * 上传学习资料
     * @param $data
     * @return MessageBag
     */
    public function addMaterial($data) {
        $messageBag = new MessageBag();

        // 查询当前课节是否已上传
        $info = $this->getMaterialByCourseAndIdx($data['user_id'],$data['course_id'], $data['idx']);

        if(!empty($info)) {
            $messageBag->setMessage('该课程资料已经上传');
            return $messageBag;
        }

        $lecture = [
            'course_id' => $data['course_id'],
            'teacher_id' => $data['user_id'],
            'idx' => $data['idx'],
            'title' => $data['title'],
        ];

        try{
            DB::beginTransaction();

            $info = Lecture::create($lecture);

            foreach ($data['grade_id'] as $k => $val) {
                foreach ($data['material'] as $key => $item) {
                    foreach ($item['media'] as $value) {
                        $material = [
                            'lecture_id' => $info->id,
                            'teacher_id' => $data['user_id'],
                            'course_id' => $data['course_id'],
                            'media_id' => $value['media_id'],
                            'url' => $value['url'],
                            'type' => $item['type_id'],
                            'description' => $item['desc'],
                            'grade_id' => $val,
                            'idx' => $data['idx'],
                        ];

                        LectureMaterial::create($material);
                    }

                }

            }
            DB::commit();
            $messageBag->setMessage('上传成功');

        } catch (\Exception $e) {

            DB::rollBack();
            $msg = $e->getMessage();
            $messageBag->setCode(JsonBuilder::CODE_ERROR);
            $messageBag->setMessage($msg);
        }

        return $messageBag;
    }


    /**
     * 编辑课程教学资料
     * @param $data
     * @return MessageBag
     */
    public function updMaterial($data){
        $messageBag = new MessageBag();

        // 查询当前课节资料是否已上传
        $info = Lecture::where('id', $data['lecture_id'])->first();
        if(empty($info)) {
            $messageBag->setCode(JsonBuilder::CODE_ERROR);
            $messageBag->setMessage('该课程资料不存在');
            return $messageBag;
        }

        // 判断课节是否已经存在 判断是否修改了课节
        $re = $this->getMaterialByCourseAndIdx($data['user_id'],$data['course_id'], $data['idx'], $data['lecture_id']);
        if(!empty($re)) {
            $messageBag->setMessage(JsonBuilder::CODE_ERROR);
            $messageBag->setMessage('当前课节已经存在');
            return $messageBag;
        }

        $lecture = [
            'course_id' => $data['course_id'],
            'teacher_id' => $data['user_id'],
            'idx' => $data['idx'],
            'title' => $data['title'],
        ];

        try {
            DB::beginTransaction();
            Lecture::where('id',$data['lecture_id'])->update($lecture);
            LectureMaterial::where('lecture_id', $data['lecture_id'])->delete();

            foreach ($data['grade_id'] as $k => $val) {
                foreach ($data['material'] as $key => $item) {
                    foreach ($item['media'] as $value) {
                        $material = [
                            'lecture_id' => $info->id,
                            'teacher_id' => $data['user_id'],
                            'course_id' => $data['course_id'],
                            'media_id' => $value['media_id'],
                            'url' => $value['url'],
                            'type' => $item['type_id'],
                            'description' => $item['desc'],
                            'grade_id' => $val,
                            'idx' => $data['idx'],
                        ];
                        LectureMaterial::create($material);
                    }

                }

            }

            DB::commit();
            $messageBag->setMessage('上传成功');
        } catch (\Exception $e) {
            DB::rollBack();
            $msg = $e->getMessage();
            $messageBag->setCode(JsonBuilder::CODE_ERROR);
            $messageBag->setMessage($msg);
        }

        return $messageBag;

    }


    /**
     * 查询老师当前课程和课节的教学资料
     * @param $userId
     * @param $courseId
     * @param $idx
     * @param null $lectureId
     * @return mixed
     */
    public function getMaterialByCourseAndIdx($userId, $courseId, $idx, $lectureId = null ) {
        $map = [
            'teacher_id'=>$userId,
            'course_id' => $courseId,
            'idx'=>$idx
        ];
        $result = Lecture::where($map);
        if(!is_null($lectureId)) {
            $result = $result->where('id','<>',$lectureId);
        }
        return $result->first();
    }


    /**
     * 根据老师获取学习资料
     * @param $userId
     * @return mixed
     */
    public function getMaterialByUser($userId) {
        $map = ['teacher_id'=>$userId];
        return Lecture::where($map)->get();
    }


    /**
     * 根据课节和资料获取信息
     * @param $lectureId
     * @param $type
     * @return mixed
     */
    public function getMaterialByLectureIdAndMediaId(User $user, $lectureId, $type) {
        $map = ['teacher_id'=>$user->id, 'type'=>$type];
        return LectureMaterial::where($map)
            ->distinct(['lecture_id', 'type'])
            ->get();
    }


    /**
     * @param $courseId
     * @param $teacherId
     * @param $gradeId
     * @return mixed
     */
    public function getMaterialsByCourseIdAndTeacherIdAndGradeId($courseId, $teacherId, $gradeId) {
        $map = [
            'course_id'=>$courseId, 'teacher_id'=>$teacherId,
            'grade_id'=>$gradeId
        ];
        return LectureMaterial::where($map)->where('media_id','<>', 0)
            ->orderBy('idx', 'asc')
            ->get();
    }


    /**
     * 查询分类
     * @param $courseId
     * @param $teacherId
     * @param $gradeId
     * @return mixed
     */
    public function getMaterialTypeByCourseId($courseId, $teacherId, $gradeId) {
        $map = [
            'course_id'=>$courseId, 'teacher_id'=>$teacherId,
            'grade_id'=>$gradeId
        ];
        return LectureMaterial::where($map)->where('media_id','<>',0)
            ->select('type')
            ->distinct('type')
            ->get();
    }
}