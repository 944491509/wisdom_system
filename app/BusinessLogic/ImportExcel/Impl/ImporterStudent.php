<?php

namespace App\BusinessLogic\ImportExcel\Impl;

use App\Dao\Importer\ImporterDao;
use App\Dao\Schools\GradeDao;
use App\Dao\Schools\MajorDao;
use App\Dao\Students\StudentAdditionInformationDao;
use App\Dao\Students\StudentProfileDao;
use App\Dao\Users\GradeUserDao;
use App\Dao\Users\UserDao;
use App\Models\Acl\Role;
use App\Models\Importer\ImportLog;
use App\Models\Importer\ImportTask;
use App\Models\Schools\Grade;
use App\Models\Schools\Major;
use App\Models\Users\UserSearchConfig;
use App\User;
use Dompdf\Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;

class ImporterStudent extends AbstractImporter
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
        $additionDao = new StudentAdditionInformationDao;
        // 修改任务状态
//        $importDao->update($this->task['id'], ['status' => ImportTask::IMPORT_TASK_EXECUTION]);

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

        try {
            DB::beginTransaction();

            // 开始循环导入
            foreach ($sheetIndexArray as $sheetIndex) {

                echo '已拿到第' . ($sheetIndex + 1) . ' sheet的数据 开始循环.....' . PHP_EOL;
                $sheetData = $this->getSheetData($sheetIndex);
                unset($sheetData[0]); // 去掉文件头

                foreach ($sheetData as $key => $val) {
                    $student = [
                        'uuid' => Uuid::uuid4()->toString(),
                        'name' => $val[0],
                        'mobile' => $val[1],
                        'email' => $val[35], // 邮箱
                        'password' => Hash::make(substr($val[5], -6)),
                        'type' => Role::VERIFIED_USER_STUDENT,
                        'status' => User::STATUS_VERIFIED,
                    ];
                    foreach ($val as $k => $v) {
                        // 需要转换成 ID 的字段
                        if (in_array($k, [11, 14, 28, 29, 30, 31, 43])) {
                            $id = UserSearchConfig::where('name', $val[$k])->value('id');
                            if ($id) {
                                $val[$k] = $id;
                            } else {
                                $val[$k] = 0;
                            }
                        }
                    }

                    $profile = [
                        'origin' => 1,
                        'year' => date('Y'),
                        'serial_number' => '-',
                        'uuid' => Uuid::uuid4()->toString(),
                        'gender' => $val[4] == '男' ? 1 : 2, // 性别
                        'nation_name' => $val[5],   // 民族
                        'political_name' => $val[6], // 政治面貌
                        'id_number' => $val[8], // 身份证号
                        'birthday' => $this->getBirthday($val[5]), // 出生日期
                        'student_code' => $val[9], // 学籍号
                        'country' => $val[10], // 籍贯
                        'health_status' => $val[11], // 健康状况 * 传ID
                        'graduate_school' => $val[12], // 毕业学校
                        'graduate_type' => $val[13], // 学生来源
                        'cooperation_type' => $val[14], // 	联招合作类型 * 传ID
                        'source_place_state' => $val[15], // 生源地(省)
                        'source_place_city' => $val[16], // 生源地(省)
                        'recruit_type' => $val[17], // 招生方式
                        'resident_type' => $val[18], // 户口性质
                        'resident_state' => $val[19], // 户籍所在省
                        'resident_city' => $val[20], // 户籍所在市
                        'resident_area' => $val[21], // 户籍所在区
                        'resident_suburb' => $val[22], // 户籍所在乡镇
                        'resident_village' => $val[23], // 户籍所在村
                        'detailed_address' => $val[24], // 户籍详细地址
                        'current_residence' => $val[25], // 现居住地址
                        'create_file' => $val[26] == '是' ? 1 : 0, // 是否建档贫困户
                        'enrollment_at' => $val[27], // 入学日期
                        'educational_system' => $val[28], // 学制 * 传ID
                        'entrance_type' => $val[29], // 入学方式 * 传ID
                        'student_type' => $val[30], // 学生类别 * 传ID
                        'segmented_type' => $val[31], // 分段培养方式 * 传ID
                        'student_number' => $val[32], // 学号
                        'qq' => $val[33], // QQ
                        'wx' => $val[34], // 微信
                        'volunteer' => $val[39], // 报考志愿
                        'examination_site' => $val[41], // 考点
                        'license_number' => $val[40], // 准考证号
                        'examination_score' => $val[42], // 考试成绩
                        'family_poverty_status' => $val[43], // 家庭贫困程度  * 传ID
                        'zip_code' => $val[44], // 家庭地址邮编
                        'residence_type' => $val[45], // 学生居住地类型
                        'parent_name' => $val[46], // 监护人
                        'parent_mobile' => $val[47], // 监护人手机号
                        'relationship' => $val[48], // 监护人关系
                        'learning_form' => $val[49], // 监护人关系
                    ];
                    $addition = [
                        'borrow_type' => $val[36], // 寄宿类型
                        'people' => $val[37], // 寄宿联系人
                        'mobile' => $val[38] // 寄宿联系电话
                    ];

                    $errorArr = [
                        'task_id' => $this->task['id'],
                        'number' => 0,
                        'name' => $student['name'],
                        'id_number' => $profile['id_number'],
                    ];
                    // 手机号不能为空
                    if (empty($student['mobile']) || strlen($student['mobile']) != 11) {
                        $errorArr['error_log'] = '手机号为空或者位数不对';
                        $this->errorLog($this->task['title'], $errorArr);
                        echo $val[0] . "手机号为空或者位数不对 跳过" . PHP_EOL;
                        continue;
                    }
                    // 身份证
                    if (empty($profile['id_number']) || strlen($profile['id_number']) != 18) {
                        $errorArr['error_log'] = '身份证号格式错误';
                        $this->errorLog($this->task['title'], $errorArr);
                        echo $val[0] . "身份证号为空或者位数不对 跳过" . PHP_EOL;
                        continue;
                    }
                    $userResult = $userDao->getUserByMobile($student['mobile']);
                    if ($userResult) {
                        $errorArr['error_log'] = '手机号已经被注册了';
                        $this->errorLog($this->task['title'], $errorArr);
                        echo $val[0] . "手机号已经被注册了 跳过此人" . PHP_EOL;
                        continue;
                    }
                    $profileResult = $profileDao->getStudentInfoByIdNumber($profile['id_number']);
                    if ($profileResult) {
                        $errorArr['error_log'] = '身份证号已经被注册了';
                        $this->errorLog($this->task['title'], $errorArr);
                        echo $val[0] . "身份证已经被注册了 跳过此人" . PHP_EOL;
                        continue;
                    }
                    $major = Major::where(['school_id' => $this->task['school_id'], 'name' => $val[2]])->first();
                    if (empty($major)) {
                        $errorArr['error_log'] = '根据名称未找到专业';
                        $this->errorLog($this->task['title'], $errorArr);
                        echo $val[0] . "根据名称未找到班级" . PHP_EOL;
                        continue;
                    }
                    $grade = Grade::where(['school_id' => $this->task['school_id'], 'name' => $val[3]])->first();
                    if (empty($grade)) {
                        $errorArr['error_log'] = '根据名称未找到班级';
                        $this->errorLog($this->task['title'], $errorArr);
                        echo $val[0] . "根据名称未找到班级" . PHP_EOL;
                        continue;
                    }

                    // 创建学生数据
                    // 创建学生的档案
                    // 创建学生班级的关联
                    // 创建学生的附件信息

                    $user = $userDao->createUser($student);

                    $profile['user_id'] = $user->id;
                    $profile['enrollment_at'] = '2020-02-02';
                    $profile['birthday'] = '2020-02-02';
                    $profileDao->create($profile);

                    $gradeData = [
                        'user_id' => $user->id,
                        'name' => $student['name'],
                        'user_type' => Role::VERIFIED_USER_STUDENT,
                        'school_id' => $this->task['school_id'],
                        'campus_id' => $major->campus->id,
                        'institute_id' => $major->institute->id,
                        'department_id' => $major->department->id,
                        'grade_id' => $grade->id
                    ];
                    $gradeUserDao->create($gradeData);

                    $addition['user_id'] = $user->id;
                    $additionDao->create($addition);
                    // 已导入条
                    $importDao->increment($this->task['id']);
                    echo $val[0] . '----------创建成功' . PHP_EOL;

                }
            }

            // 统计未导入总数
            $count = ImportLog::where('task_id', $this->task['id'])->count();
            // 修改任务状态 和 未导入条数
            $importDao->update($this->task['id'], ['status' => ImportTask::IMPORT_TASK_COMPLETE, 'surplus' => $count]);

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
        }
    }


}
