<?php

namespace App\BusinessLogic\ImportExcel\Impl;

use App\Dao\Importer\ImporterDao;
use App\Dao\Students\StudentProfileDao;
use App\Dao\Users\GradeUserDao;
use App\Dao\Users\UserDao;
use App\Models\Acl\Role;
use App\Models\Importer\ImportLog;
use App\Models\Importer\ImportTask;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;


class ImporterUsers extends AbstractImporter
{

    private  $task;
    public function __construct($task)
    {
        $this->task = $task['data'];
        parent::__construct($task['data']['path']);
    }

    public function handle()
    {
        $userDao = new UserDao;
        $profileDao = new StudentProfileDao;
        $gradeUserDao = new GradeUserDao;
        $importDao = new ImporterDao;
        // 修改任务状态
        $importDao->update($this->task['id'], ['status' => ImportTask::IMPORT_TASK_EXECUTION]);

        $this->loadExcelFile();
        $sheetIndexArray = $this->getSheetIndexArray();

        // 再次验证文件格式
        $config = new ImporterConfig($this->fileRelativePath, $this->task['type']);
        $validation = $config->validation();
        if (!empty($validation)) {
            $error = [
                'task_id' => $this->task['id'],
                'error_log' => '文件格式错误'
            ];
            $this->errorLog($this->task['title'], $error);
            exit();
        }

        // 开始循环导入
        foreach($sheetIndexArray as $sheetIndex) {

            echo '已拿到第'. ($sheetIndex+1).' sheet的数据 开始循环.....'.PHP_EOL;
            $sheetData = $this->getSheetData($sheetIndex);
            unset($sheetData[0]); // 去掉文件头
            foreach ($sheetData as $key => $val) {

                $student = [
                    'uuid' => Uuid::uuid4()->toString(),
                    'name' => $val[0],
                    'mobile' => $val[1],
                    'password' => Hash::make(substr($val[5],-6)),
                    'type' => Role::VERIFIED_USER_STUDENT,
                    'status' => User::STATUS_VERIFIED,
                ];

                $profile = [
                    'origin' => 1,
                    'year' => date('Y'),
                    'serial_number' => '-',
                    'uuid' => Uuid::uuid4()->toString(),
                    'gender'  => $val[2] == '男' ? 1 : 2, // 性别
                    'nation_name' => $val[3],   // 民族
                    'political_name' => $val[4], // 政治面貌
                    'id_number' => $val[5], // 身份证号
                    'student_code' => $val[6], // 学籍号
                    'country' => $val[7], // 籍贯
                    'graduate_school' => $val[8], // 毕业学校
                    'graduate_type' => $val[9], // 学生来源
                    'source_place_state' => $val[10], // 生源地(省)
                    'source_place_city' => $val[11], // 生源地(省)
                    'recruit_type' => $val[12], // 招生方式
                    'resident_type' => $val[13], // 户口性质
                    'resident_state' => $val[14], // 户籍所在省
                    'resident_city' => $val[15], // 户籍所在市
                    'resident_area' => $val[16], // 户籍所在区
                    'resident_suburb' => $val[17], // 户籍所在乡镇
                    'resident_village' => $val[18], // 户籍所在村
                    'detailed_address' => $val[19], // 户籍详细地址
                    'current_residence' => $val[20], // 现居住地址
                    'parent_name' => $val[21], // 监护人
                    'parent_mobile' => $val[22], // 监护人
                    'relationship' => $val[23], // 监护人关系
                    'create_file' => $val[24] == '是' ? 1 : 0 , // 是否建档贫困户
                    'volunteer' => $val[25], // 报考支援
                    'examination_site' => $val[26], // 考点
                    'license_number' => $val[27], // 准考证号
                    'examination_score' => $val[27], // 考试成绩
                ];

                $errorArr = [
                    'task_id' => $this->task['id'],
                    'number' => 0,
                    'name' => $student['name'],
                    'id_number' => $profile['id_number'],
                ];
                // 手机号不能为空
                if (empty($student['mobile']) || strlen($student['mobile'])!= 11 ) {
                    $errorArr['error_log'] = '手机号为空或者位数不对';
                    $this->errorLog($this->task['title'], $errorArr);
//                    echo $val[0]."手机号为空或者位数不对 跳过".PHP_EOL;
                    continue;
                }
                // 身份证
                if (empty($profile['id_number']) || strlen($profile['id_number'])!= 18) {
                     $errorArr['error_log'] = '身份证号格式错误';
                     $this->errorLog($this->task['title'], $errorArr);
//                    echo $val[0]."身份证号为空或者位数不对 跳过".PHP_EOL;
                    continue;
                }
                $userResult = $userDao->getUserByMobile($student['mobile']);
                if ($userResult) {
                    $errorArr['error_log'] = '手机号已经被注册了';
                    $this->errorLog($this->task['title'], $errorArr);
                    echo $val[0]. "手机号已经被注册了 跳过此人".PHP_EOL;
                    continue;
                }
                $profileResult = $profileDao->getStudentInfoByIdNumber($profile['id_number']);
                if ($profileResult) {
                    $errorArr['error_log'] = '身份证号已经被注册了';
                    $this->errorLog($this->task['title'], $errorArr);
                    echo $val[0]. "身份证已经被注册了 跳过此人".PHP_EOL;
                    continue;
                }

                DB::beginTransaction();
                try{
                    // 创建用户数据
                    // 创建用户班级的关联
                    // 创建用户的档案
                    $user = $userDao->createUser($student);
                    $profile['user_id'] = $user->id;
                    $profileDao->create($profile);
                    $gradeData = [
                        'user_id' => $user->id,
                        'name' => $student['name'],
                        'user_type' => Role::VERIFIED_USER_STUDENT,
                        'school_id' => $this->task['school_id'],
                    ];
                    $gradeUserDao->create($gradeData);
                    DB::commit();
                    echo $val[0].'----------创建成功'.PHP_EOL;
                    // 已导入条
                    $importDao->increment($this->task['id']);
                }
                catch (\Exception $exception){
                    DB::rollBack();
                }
            }
        }

        // 统计未导入总数
        $count = ImportLog::where('task_id', $this->task['id'])->count();
        // 修改任务状态 和 未导入条数
        $importDao->update($this->task['id'], ['status' => ImportTask::IMPORT_TASK_COMPLETE, 'surplus' => $count]);
    }


}
