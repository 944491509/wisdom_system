<?php

namespace App\Dao\Students;

use App\Models\Students\StudentAdditionInformation;

class StudentAdditionInformationDao
{

    /**
     * 根据用户ID 查询
     * @param $userId
     * @return mixed
     */
    public function getStudentAddInfoByUserId($userId)
    {
        return StudentAdditionInformation::where('user_id', $userId)->first();
    }

    /**
     * 添加
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
       return StudentAdditionInformation::create($data);
    }

    /**
     * 修改
     * @param $userId
     * @param $data
     * @return mixed
     */
    public function update($userId, $data)
    {
        return StudentAdditionInformation::where('user_id', $userId)->update($data);
    }

}
