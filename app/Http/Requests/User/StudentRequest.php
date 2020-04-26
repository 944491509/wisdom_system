<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 18/10/19
 * Time: 10:18 PM
 */

namespace App\Http\Requests\User;

use App\Http\Requests\MyStandardRequest;

class StudentRequest extends MyStandardRequest
{

    public function getFormData() {
        $user = $this->get('user');
        $profile = $this->get('profile');
        $addition = $this->get('addition');
        $gradeUser = $this->get('grade_user');
        return ['user'=>$user, 'profile'=>$profile, 'addition'=> $addition, 'grade_user' => $gradeUser];
    }
}
