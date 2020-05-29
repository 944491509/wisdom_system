<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 10/11/19
 * Time: 1:30 PM
 */

namespace App\BusinessLogic\QuickSearch\Impl;

use App\Dao\Users\GradeUserDao;
use App\Models\Acl\Role;

class StudentQuickSearchLogic extends AbstractQuickSearchLogic
{
    public function getFacilities()
    {
        return [];
    }

    public function getNextAction($facility)
    {
        return '';
    }

    public function getUsers()
    {
        $dao = new GradeUserDao;
        return $dao->getUsersWithNameLike($this->queryString, $this->schoolId, [Role::VERIFIED_USER_STUDENT, Role::VERIFIED_USER_CLASS_LEADER, Role::VERIFIED_USER_CLASS_SECRETARY]);
    }
}
