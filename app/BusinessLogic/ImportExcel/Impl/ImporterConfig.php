<?php


namespace App\BusinessLogic\ImportExcel\Impl;

use App\Models\Importer\ImportTask;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

class ImporterConfig extends AbstractImporter
{
    private $filePath;
    private $fileType;

    public function __construct($filePath, $fileType)
    {
        $this->filePath = $filePath;
        $this->fileType = $fileType;
        parent::__construct($filePath);
    }


    /**
     * 文件格式标准
     * @return array
     */
    private function getFileFormat()
    {
        return [
            ImportTask::IMPORT_TYPE_NO_IDENTITY => [ // 未认证
                '姓名', '本人手机号',	'性别', '民族','政治面貌', '身份证号','学籍号', '籍贯', '毕业学校', '学生来源', '生源地(省)',
                '生源地(市)', '招生方式','户口性质', '户籍地(省)', '户籍地(市)', '户籍地(区/县)', '户籍地(乡/镇)', '户籍地(村)',
                '户籍详细地址', '现居住地址', '监护人姓名', '监护人电话', '与本人关系', '是否建档立卡贫困户', '报考志愿', '考点', '准考试证号',
                '考试成绩'
            ],
            ImportTask::IMPORT_TYPE_CERTIFIED => [], // 已认证
        ];
    }

    /**
     * 获取一个文件格式标准
     * @return array|mixed
     */
    private function getConfigType()
    {
        $config = $this->getFileFormat();
        return $config[$this->fileType];
    }

    /**
     * 验证文件是否符合文件格式标准
     * @throws Exception
     */
    public function validation()
    {
        $errorArr = [];
        $this->loadExcelFile();
        $sheetIndexArray = $this->getSheetIndexArray();
        // $str = "文件数据格式有问题请重新上传, 第";
        foreach($sheetIndexArray as $sheetIndex) {
        // echo '已拿到第' . ($sheetIndex + 1) . ' sheet的数据 开始循环.....' . PHP_EOL;
            $sheetData = $this->getSheetData($sheetIndex);
        // echo '开始检查文件格式是否正确' . PHP_EOL;
          // 检查文件格式是否正确
          foreach ($sheetData[0] as $key => $val) {
              if ($this->getConfigType()[$key] != $val) {
                  $errorArr[] = '第'. ($key + 1) . "列应该是" . $this->getConfigType()[$key]. ', ';
              }
          }
        }
        return $errorArr;
    }

}
