<?php

namespace App\Console\Commands;

use App\Models\Schools\Grade;
use App\Models\Students\StudentProfile;
use App\Models\Users\GradeUser;
use App\ThirdParty\CloudOpenApi;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UploadStudentPhoto extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:student_photo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '上传学生人脸识别照片';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('任务开始');
        $grades = Grade::where('school_id', 2)
                ->whereIn('year', [2018, 2019])
                ->get();
        $num = 0;
        try {
            // 学校所有的班级
            foreach ($grades as $key => $grade) {
                // 根据班级名称找到目录下所有学生照片
                $path = storage_path('app/student_photo/' . $grade->name);
                $fileArr = $this->myScanDir($path);
                if (!is_array($fileArr)) {
                     Log::channel('face_log')->info($grade->name.'不是个数组'.PHP_EOL);
                     continue;
                }
                foreach ($fileArr as $k => $val) {
                    $studentName = substr($val, 0, strpos($val, '.jpg'));
                    // 根据班级id , 学生姓名判断班级是否有该学生, 如果有重名的 先记录下来单独处理
                    $studentNum = GradeUser::where(['grade_id' => $grade->id, 'name' => $studentName])->count();
                    if ($studentNum > 1) {
                        Log::channel('face_log')->info($grade->name . '中有重名的学生----' . $val);
                        continue;
                    } elseif($studentNum == 0) {
                        Log::channel('face_log')->info($grade->name . '中沒有找到该学生----' . $val);
                        continue;
                    } else {
                        // 上传照片
                        $openApi = new CloudOpenApi;
                        $result = $openApi->makePostUploadFaceImg($path.'/'.$val);
                        if ($result['code'] != CloudOpenApi::ERROR_CODE_OPEN_API_OK) {
                            Log::channel('face_log')->info($grade->name.'中'.$val. '-----错误原因:'. $result['message']. '---code:'. $result['code']);
                        } else {
                            $gradeUser = GradeUser::where(['grade_id' => $grade->id, 'name' => $studentName])->first();
                            $studentProfile = StudentProfile::where('user_id', $gradeUser->user_id)->update(['face_code' => $result['data']['face_code']]);
                            if (!$studentProfile) {
                                Log::channel('face_log')->info($grade->name.'中'.$val. '修改失败:'. 'face_code:'. $result['code']);
                            }
                        }
                    }
                    $num ++;
                }
                echo $grade->name. '循环完成'.PHP_EOL;
            }
            echo '总次数:'.$num.PHP_EOL;
        } catch (\Exception $exception) {
            Log::channel('face_log')->info('异常错误'. $exception. '----'.$grade->name);
        }
        $this->info('任务结束');
    }



    public function myScanDir($dir)
    {
        try {
            $dh  = opendir($dir);
            $tmpArr = [];
            while (false !== ($filename = readdir($dh))) {
                if($filename !='..' && $filename !='.'){
                    if(is_dir($dir.'/'.$filename)){
                        $tmpArr[$filename] = $this->myScanDir($dir.'/'.$filename);
                    }else{
                        $tmpArr[] = $filename;
                    }
                }

            }
            closedir($dh);
            return $tmpArr;
        } catch (\Exception $exception) {
            Log::channel('face_log')->info('没有找到'. $dir.PHP_EOL);
        }
    }
}
