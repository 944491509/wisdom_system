<?php


namespace App\BusinessLogic\ImportExcel;

use App\BusinessLogic\ImportExcel\Impl\ImporterStudentAccommodation;
use App\BusinessLogic\ImportExcel\Impl\ImporterUsers;
use App\BusinessLogic\ImportExcel\Impl\UpdateTeacherPhone;

class Factory
{
    public static function createAdapter(array $configArr)
    {

        $instance = null;
        if (!isset($configArr['importerName'])) {
            $instance = new ImporterUsers($configArr);
        } elseif ($configArr['importerName'] == 'update_teacher_phone') {
            $instance = new UpdateTeacherPhone();
        } elseif ($configArr['importerName'] == 'importer_student_accommodation') {
            $instance = new ImporterStudentAccommodation();
        }
        else {
            $adapterName = $configArr['importerName'];
            $instance = new $adapterName($configArr);
        }


        return $instance;
    }
}
