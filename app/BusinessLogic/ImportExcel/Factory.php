<?php


namespace App\BusinessLogic\ImportExcel;

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
        } else {
            $adapterName = $configArr['importerName'];
            $instance = new $adapterName($configArr);
        }


        return $instance;
    }
}
