<?php
/**
 * Created by Justin
 */

namespace App\Dao\Users;
use App\Dao\Teachers\TeacherProfileDao;
use App\Models\School;
use App\Models\Teachers\Teacher;
use App\Models\Users\GradeUser;
use App\User;
use App\Models\Acl\Role;
use App\Utils\JsonBuilder;
use App\Utils\Misc\ConfigurationTool;
use App\Utils\ReturnData\MessageBag;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;

class UserDao
{
    private $protectedRoles = [Role::SUPER_ADMIN, Role::OPERATOR];

    public function createUser($data){
        return User::create($data);
    }


    /**
     * 获取平台管理员
     * @return mixed
     */
    public function getAdminPage() {
        return User::where('type',Role::SUPER_ADMIN)
            ->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);
    }


    /**
     * 添加平台管理员
     * @param $data
     * @return MessageBag
     * @throws Exception
     */
    public function addAdmin($data) {
        $bag = new MessageBag(JsonBuilder::CODE_ERROR);
        // 查询账号是否存在
        $info = $this->getUserByMobile($data['mobile']);
        if(!is_null($info)) {
            $bag->setMessage('该账号已存在');
            return $bag;
        }
        $add = [
            'mobile'=>$data['mobile'],
            'uuid'=>Uuid::uuid4()->toString(),
            'password'=>Hash::make($data['password']),
            'status'=>User::STATUS_VERIFIED,
            'type'=>Role::SUPER_ADMIN,
            'name' => $data['name'],
        ];

        $re = User::create($add);
        if($re) {
            $bag->setMessage('创建成功');
            $bag->setCode(JsonBuilder::CODE_SUCCESS);
        } else {
            $bag->setMessage('创建失败');
        }
        return $bag;
    }


    /**
     * 编辑管理员
     * @param $userId
     * @param $name
     * @param $password
     * @return mixed
     */
    public function updateAdminByUserId($userId, $name, $password) {
        $upd = ['name' => $name];
        if(!is_null($password)) {
            $upd['password'] = Hash::make($password);
        }
        return User::where('id', $userId)->update($upd);
    }


    /**
     * 根据用户的电话号码获取用户
     * @param string $mobile
     * @return User
     */
    public function getUserByMobile($mobile){
        return User::where('mobile',$mobile)->first();
    }

    /**
     * 根据用户的 id 或者 uuid 获取用户对象
     * @param string $idOrUuid
     * @return User|null
     */
    public function getUserByIdOrUuid($idOrUuid){
        if(is_string($idOrUuid) && strlen($idOrUuid) > 10){
            return $this->getUserByUuid($idOrUuid);
        }
        elseif ($idOrUuid){
            return $this->getUserById($idOrUuid);
        }
        return null;
    }

    /**
     * @param $idOrUuid
     * @return Teacher|null
     */
    public function getTeacherByIdOrUuid($idOrUuid){
        if(is_string($idOrUuid) && strlen($idOrUuid) > 10){
            return Teacher::where('uuid',$idOrUuid)->first();
        }
        elseif ($idOrUuid){
            return Teacher::find($idOrUuid);
        }
        return null;
    }

    /**
     * @param $uuid
     * @return User|null
     */
    public function getUserByUuid($uuid){
        return User::where('uuid',$uuid)->first();
    }

    /**
     * @param $id
     * @return User|null
     */
    public function getUserById($id){
        return User::find($id);
    }

    /**
     * @param $id
     * @return Teacher|null
     */
    public function getTeacherById($id){
        return Teacher::find($id);
    }

    /**
     * @param $uuid
     * @return Teacher|null
     */
    public function getTeacherByUuid($uuid){
        return Teacher::where('uuid',$uuid)->first();
    }

	/**
     * 获取用户所在班级
     * @param $uuid
     * @return
     */
    public function getUserGradeByUuid($uuid)
    {
        return User::where('uuid', $uuid)->with('gradeUser')->first();
    }

    /**
     * 获取用户的所有角色, 返回值为角色的 slug
     * @param User|int|string $user
     * @return string[]|null
     */
    public function getUserRoles($user){
        if(is_object($user)){
            return $user->getRoles();
        }else{
            $user = $this->getUserByIdOrUuid($user);
            return $user ? $user->getRoles() : null;
        }
    }

    /**
     * 给用户赋予一个角色
     * @param User $user
     * @param int|string $roleId
     * @param bool $ignoreProtectedRoles
     * @return bool
     */
    public function assignRoleToUser(User $user, $roleId, $ignoreProtectedRoles = false){
        if($ignoreProtectedRoles || !in_array($roleId, $this->protectedRoles)){
            // 不提供给用户赋予超级管理员和运营人员角色的功能
            $user->assignRole($roleId);
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * 撤销用户的某个角色
     * @param User $user
     * @param int|string $roleId
     * @param bool $ignoreProtectedRoles
     * @return bool
     */
    public function revokeRoleFromUser(User $user, $roleId, $ignoreProtectedRoles = false){
        if($ignoreProtectedRoles || !in_array($roleId, $this->protectedRoles)){
            // 不提供给用户撤销 超级管理员和运营人员角色 的功能
            $user->revokeRole($roleId);
            return true;
        }
        else{
            return false;
        }
    }

    /**
     * 返回 APP 用户身份
     * @param $userType
     * @return string
     */
    public function  getUserRoleName($userType)
    {
        switch ($userType) {
            case Role::TEACHER :
                return trans('AppName.teacher');
                break;
            case Role::VERIFIED_USER_STUDENT :
                return trans('AppName.student');
                break;
            // todo :: 用到了再补充, 用来获取用户身份的名字
            default: return "" ;
                break;
        }
    }

    /**
     * 获取指定学校的所有的教师的列表
     * @param $schoolId
     * @param bool $simple: 简单的返回值 id=>name 的键值对组合
     * @param string  $keyword 关键词
     * @return Collection
     */
    public function getTeachersBySchool($schoolId, $simple = false, $keyword = null){
        if($simple){
            return GradeUser::select(DB::raw('user_id as id, name'))
                ->where('school_id',$schoolId)
                ->where('user_type',Role::TEACHER)
                ->where('name', 'like', $keyword.'%')
                ->get();
        }
        return GradeUser::where('school_id',$schoolId)
            ->where('user_type',Role::TEACHER)
            ->where('name', 'like', $keyword.'%')
            ->get();
    }

    /**
     * 创建学校管理员账户
     * @param $schoolId
     * @param $mobile
     * @param $password
     * @param $name
     * @param $userType
     * @return MessageBag
     */
    public function createSchoolManager($schoolId, $mobile, $password,$name, $userType){
        $bag = new MessageBag(JsonBuilder::CODE_ERROR);
        // 判断账号是否存在
        $info = $this->getUserByMobile($mobile);
        if(!is_null($info)) {
            $bag->setMessage('该账号已存在');
            return $bag;
        }

        DB::beginTransaction();
        try{
            $data = [
                'mobile'=>$mobile,
                'name'=>$name,
                'api_token'=>Uuid::uuid4()->toString(),
                'uuid'=>Uuid::uuid4()->toString(),
                'password'=>Hash::make($password),
                'status'=>User::STATUS_VERIFIED,
                'type'=>$userType,
                'mobile_verified_at'=>Carbon::now(),
            ];

            $user = User::create($data);

            // 创建 grade user 的记录
            $gradeUserDao = new GradeUserDao();
            $gradeUserDao->addGradUser([
                'user_id'=>$user->id,
                'name'=>$name,
                'user_type'=>$userType,
                'school_id'=>$schoolId,
            ]);
            // 创建他的资料账户
            $teacherProfileDao = new TeacherProfileDao();
            $teacherProfileDao->createProfile([
                'uuid'=>Uuid::uuid4()->toString(),
                'user_id'=>$user->id,
                'school_id'=>$schoolId,
                'serial_number'=>'n.a',
                'group_name'=>'管理',
                'title'=>'易同学管理员',
                'avatar'=>User::DEFAULT_USER_AVATAR,
            ]);
            DB::commit();
            $bag->setCode(JsonBuilder::CODE_SUCCESS);
            $bag->setMessage('创建成功');

        }
        catch (Exception $exception){
            DB::rollBack();
            $bag->setMessage($exception->getMessage());
        }
        return $bag;
    }


    /**
     * 编辑管理员
     * @param $userId
     * @param $mobile
     * @param $password
     * @param $name
     * @param $userType
     * @return MessageBag
     */
    public function updateSchoolManager($userId, $mobile, $password, $name, $userType) {
        $bag = new MessageBag(JsonBuilder::CODE_ERROR);
        $save = ['name'=>$name, 'type'=>$userType, 'mobile'=>$mobile];
        if(!is_null($password)) {
            $save['password'] = Hash::make($password);
        }
        try {
            DB::beginTransaction();
            // 修改用户表
            User::where('id', $userId)->update($save);
            // 修改gradeUser
            $upd = ['name'=>$name, 'user_type'=>$userType];
            GradeUser::where('user_id', $userId)->update($upd);
            DB::commit();
            $bag->setCode(JsonBuilder::CODE_SUCCESS);
            $bag->setMessage('编辑成功');
        } catch (Exception $e) {
            DB::rollBack();
            $msg = $e->getMessage();
            $bag->setMessage($msg);
        }
        return $bag;
    }

    /**
     * 更新用户的基本数据
     * @param $userId
     * @param null $mobile
     * @param null $password
     * @param null $name
     * @param null $email
     * @param null $niceName
     * @return mixed
     */
    public function updateUser($userId, $mobile=null, $password=null, $name=null, $email = null, $niceName=null, $userSignture=null){
        $data = [];

        if($mobile){
            $data['mobile'] = $mobile;
        }
        if($password){
            $data['password'] = Hash::make($password);
        }
        if($name){
            $data['name'] = $name;
        }
        if($email){
            $data['email'] = $email;
        }
        if($niceName) {
            $data['nice_name'] = $niceName;
        }
        if($userSignture) {
            $data['user_signture'] = $userSignture;
        }
        if(!empty($data)){
            return User::where('id',$userId)->update($data);
        }
    }

    /**
     * excel导入用户时使用
     * @param $mobile
     * @param $name
     * @param $passwordInPlainText
     * @param int $type
     * @param int $status
     * @return mixed
     * @throws Exception
     */
    public function importUser($mobile,$name,$passwordInPlainText, $type = Role::VERIFIED_USER_STUDENT, $status=User::STATUS_VERIFIED)
    {
        $data = [
            'mobile'=>$mobile,
            'name'=>$name,
            'api_token'=>Uuid::uuid4()->toString(),
            'uuid'=>Uuid::uuid4()->toString(),
            'password'=>Hash::make($passwordInPlainText),
            'status'=>$status,
            'type'=>$type,
        ];
        return User::create($data);
    }

    /**
     * 更新 api_token
     * @param $userId
     * @param string $token
     * @return User
     * @throws Exception
     */
    public function updateApiToken($userId, $token = '')
    {
        return User::where('id', $userId)->update(['api_token' => $token]);
    }

    /**
     * 获取用户
     * @param $apiToken
     * @return mixed
     */
    public function getUserByApiToken($apiToken)
    {
        return User::where('api_token', $apiToken)->first();
    }

    /**
     * 更新用户邮箱
     * @param $userId
     * @param $email
     * @return mixed
     */
    public function updateEmail($userId, $email = null)
    {
        return User::where('id', $userId)->update(['email' => $email]);
    }

    /**
     * 根据用户的电话号码获取用户
     * @param $email
     * @return User
     */
    public function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }


    /**
     * 更新用户的基本数据
     * @param $userId
     * @param $data
     * @return mixed
     */
    public function updateUserInfo($userId, $data)
    {
        return User::where('id', $userId)->update($data);
    }

    public function getUsersWithNameLike($name, $userType = null, $schoolId = null){
        $where = [
            ['name','like',$name.'%'],
        ];
        if ($schoolId) {
            $where[] = ['school_id','=',$schoolId];
        }
        $query = User::select(['id','id as user_id','name','user_type'])
            ->where($where);

        if($userType){
            if(is_array($userType)){
                // 如果同时定位多个角色
                $query->whereIn('user_type',$userType);
            }
            else{
                $query->where('user_type',$userType);
            }
        }

        return $query->take(ConfigurationTool::DEFAULT_PAGE_SIZE_QUICK_SEARCH)->get();
    }


}
