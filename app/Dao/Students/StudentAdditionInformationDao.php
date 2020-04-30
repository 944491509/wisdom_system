<?php

namespace App\Dao\Students;

use App\Models\Students\StudentAdditionInformation;

class StudentAdditionInformationDao
{


    public function getStudentAddInfoByUserId($userId)
    {
        return StudentAdditionInformation::where('user_id', $userId)->first();
    }

    public function create($data)
    {
       return StudentAdditionInformation::create($data);
    }


    public function update($userId, $data)
    {
        return StudentAdditionInformation::where('user_id', $userId)->update($data);
    }
}
