<?php
/**
 * Created by PhpStorm.
 * User: justinwang
 * Date: 5/11/19
 * Time: 12:56 PM
 */

namespace App\BusinessLogic\QuickSearch\Impl;
use App\Dao\Users\UserDao;
use App\Models\Acl\Role;

class SuperQuickSearchLogic extends AbstractQuickSearchLogic
{
    public function getUsers()
    {
        $dao = new UserDao();
        return $dao->getUsersWithNameLike($this->queryString, Role::SUPER_ADMIN);
    }

    /**
     * 对教职工的搜索, 不需要任何的其他的 facility
     * @return array|\Illuminate\Support\Collection
     */
    public function getFacilities()
    {
        return [];
    }

    public function getNextAction($facility)
    {
        return '';
    }
}
