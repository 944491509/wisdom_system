<?php


namespace App\Dao\Schools;


use App\Models\Schools\GradeResource;

class GradeResourceDao
{
    /**
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        return GradeResource::create($data);
    }

    /**
     * 根据 ID 删除
     * @param $id
     * @return mixed
     */
    public function delete($id)
    {
        return GradeResource::where('id', $id)->delete();
    }

    /**
     * 根据 班级ID 获取
     * @param $gradeId
     * @return mixed
     */
    public function getResourceByGradeId($gradeId)
    {
        return GradeResource::where('grade_id', $gradeId)->orderBy('created_at', 'desc')->get();
    }

}
