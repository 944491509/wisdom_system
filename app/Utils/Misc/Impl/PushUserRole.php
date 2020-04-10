<?php


namespace App\Utils\Misc\Impl;

use App\Models\Acl\Role;
use App\User;

trait PushUserRole
{
    protected function setKeyAndRegId($users)
    {
        $student = [];
        $teacher = [];
        $shop    = [];
        /**
         * @var  user $user
         */
        $studentI = 0;
        $teacherI = 0;
        $shopI = 0;
        foreach ($users as $user) {
            $user = $user->user;
            if (empty($user)) {
                continue;
            }
            if ($user->isStudent() || $user->isRegisteredUsers()) {
                // 学生端
                $this->appKey       = env('PUSH_STUDENT_KEY');
                $this->masterSecret = env('PUSH_STUDENT_SECRET');

                $student['key'] = ['appKey' => $this->appKey , 'masterSecret' => $this->masterSecret, 'production' => env('PUSH_STUDENT_PRODUCTION')];
                if (!empty($user->userDevices) && !empty($user->userDevices->push_id)) {
                    $studentI++;
                    $student['regId'][ceil($studentI / 999)][] = $user->userDevices->push_id;
                }

            } elseif ($user->isTeacher() || $user->isEmployee() || $user->isSchoolManager()) {
                // 教师端
                $this->appKey       = env('PUSH_TEACHER_KEY');
                $this->masterSecret = env('PUSH_TEACHER_SECRET');

                $teacher['key'] = ['appKey' => $this->appKey , 'masterSecret' => $this->masterSecret, 'production' => env('PUSH_TEACHER_PRODUCTION')];
                if (!empty($user->userDevices) && !empty($user->userDevices->push_id)) {
                    $teacherI++;
                    $teacher['regId'][ceil($teacherI / 999)][] = $user->userDevices->push_id;
                }

            } elseif ($user->type == Role::COMPANY || $user->type == Role::DELIVERY || $user->type == Role::BUSINESS_INNER || $user->type == Role::BUSINESS_OUTER) {
                // TODO :: 以后实现
                continue;

                // 商企端
                $this->appKey       = env('PUSH_ENTERPRISE_KEY');
                $this->masterSecret = env('PUSH_ENTERPRISE_SECRET');
                $shop['key'] = ['appKey' => $this->appKey , 'masterSecret' => $this->masterSecret, 'production' => env('PUSH_ENTERPRISE_PRODUCTION')];
                foreach ($user->userDevices as $device) {
                    $shop['regId'][] = $device->push_id;
                }

            } else {
                $this->appKey       = null;
                $this->masterSecret = null;
            }
        }
        return [$student,  $teacher, $shop];
    }

}
