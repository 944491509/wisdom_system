<?php


namespace App\BusinessLogic\ImportExcel;

use App\BusinessLogic\ImportExcel\Impl\ImporterStudent;
use App\BusinessLogic\ImportExcel\Impl\ImporterStudentAccommodation;
use App\BusinessLogic\ImportExcel\Impl\ImporterUsers;
use App\BusinessLogic\ImportExcel\Impl\UpdateTeacherPhone;

class Factory
{
    public static function createAdapter($task)
    {
        $instance = null;
        if ($task['adapter'] == 'update_teacher_phone') {
            $instance = new UpdateTeacherPhone; // 修改教师手机号
        } elseif ($task['adapter'] == 'importer_student_accommodation') {
            $instance = new ImporterStudentAccommodation($task); // 导入学生住宿信息
        } elseif ($task['adapter'] == 'import_student') {
            $instance = new ImporterStudent($task); // 导入带专业班级的学生
        } elseif ($task['adapter'] == 'import_users') {
            $instance = new ImporterUsers($task); // 导入未认证的学生(不带专业班级)
        }
        return $instance;
    }
}
