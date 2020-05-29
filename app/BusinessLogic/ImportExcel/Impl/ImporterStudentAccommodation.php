<?php


namespace App\BusinessLogic\ImportExcel\Impl;

use App\Dao\Schools\GradeDao;
use App\Dao\Students\StudentAdditionInformationDao;
use App\Models\Schools\Grade;
use App\Models\Users\GradeUser;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImporterStudentAccommodation extends AbstractImporter
{
    protected  $data;

    public function __construct()
    {

    }

    public function handle()
    {
        $this->loadExcelFile();
        $sheetIndexArray = $this->getSheetIndexArray();
        $str = "文件数据格式有问题请重新上传, 第";
         foreach($sheetIndexArray as $sheetIndex) {
             echo '已拿到第' . ($sheetIndex + 1) . ' sheet的数据 开始循环.....' . PHP_EOL;
             $sheetData = $this->getSheetData($sheetIndex);
             echo '开始检查文件格式是否正确' . PHP_EOL;
             // 检查文件格式是否正确
             foreach ($sheetData[0] as $key => $val) {
                 if ($this->fileFormat()[$key] != $val) {
                     echo $str . ($key + 1) . "列应该是" . $this->fileFormat()[$key];
                     die;
                 }
             }
             echo '检查文件格式正确 开始循环....'.PHP_EOL;
             unset($sheetData[0]); // 去掉文件头
             foreach ($sheetData as $key => $val) {

                  $grade = Grade::where(['name'=>trim($val['4']), 'school_id'=> 2])->first();
                  if (empty($grade)) {
                      echo "没有找到此班级----". $val[4].PHP_EOL;
                      Log::channel('smslog')->info('没有找到此班级------'.$val[4]);
                      continue;
                  }
                  $gradeUser = GradeUser::where(['grade_id' => $grade->id, 'name' => $val[0]])->first();
                  if (empty($gradeUser)) {
                      echo "没有找到此学生----". $val[0].PHP_EOL;
                      Log::channel('smslog')->info('没有找到此学生------'.$val[0]);
                      continue;
                  }

                  $studentAdditionDao = new StudentAdditionInformationDao;
                  $info = $studentAdditionDao->getStudentAddInfoByUserId($gradeUser->user_id);
                  $addition = [
                      'user_id' => $gradeUser->user_id,
                      'people' => $val[1],
                      'mobile' => $val[2],
                      'address' => $val[3]
                  ];

                  if ($info) {
                      $result = $studentAdditionDao->update($gradeUser->user_id, $addition);
                  }else {
                      $result = $studentAdditionDao->create($addition);
                  }
                  if ($result) {
                      echo "执行成功----". $val[0].PHP_EOL;
                  } else {
                      echo "执行失败----". $val[0].PHP_EOL;
                  }
             }
         }

    }

    public function loadExcelFile()
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        $filePath =  'app/Console/Commands/data/18级汇总住宿信息.xlsx';
        $objReader = IOFactory::createReader('Xlsx');
        $objPHPExcel = $objReader->load($filePath);
        $worksheet = $objPHPExcel->getAllSheets();
        $this->data = $worksheet;
    }

    /**
     * 文件格式标准
     */
    public function fileFormat()
    {
       return [
           "姓名",
           "房东姓名",
           "房东联系电话",
           "寄宿地址",
           "班级",
        ];
    }


}
