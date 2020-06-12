<?php


namespace App\BusinessLogic\ImportExcel;

use App\BusinessLogic\ImportExcel\Impl\ImporterStudentAccommodation;
use App\BusinessLogic\ImportExcel\Impl\ImporterUsers;
use App\BusinessLogic\ImportExcel\Impl\ImportStudent;
use App\BusinessLogic\ImportExcel\Impl\UpdateTeacherPhone;

class Factory
{
    public static function createAdapter($taskName)
    {
        $instance = null;
        if ($taskName == 'update_teacher_phone') {
            $instance = new UpdateTeacherPhone; // 修改教师手机号
        } elseif ($taskName == 'importer_student_accommodation') {
            $instance = new ImporterStudentAccommodation; // 导入学生住宿信息
        } elseif ($taskName == 'import_student') {
            $instance = new ImportStudent; // 导入带专业班级的学生
        } elseif ($taskName == 'import_users') {
            $instance = new ImporterUsers; // 导入未认证的学生(不带专业班级)
        }
        return $instance;
    }
}
