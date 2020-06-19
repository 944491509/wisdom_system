<?php


namespace App\BusinessLogic\ImportExcel\Impl;


use App\BusinessLogic\ImportExcel\Contracts\IImportExcel;
use App\Dao\Importer\ImporterDao;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;

abstract class AbstractImporter implements IImportExcel
{
    protected $fileRelativePath;
    private $data;
    private $config;

    public function __construct($fileRelativePath)
    {
        $this->fileRelativePath = $fileRelativePath;
        $this->data   = [];
        $this->config = [];
    }

    /**
     * @throws Exception
     */
    public function loadExcelFile()
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        $filePath = $this->getFileAbsolutePath();
        $objReader = IOFactory::createReader('Xlsx');
        $objPHPExcel = $objReader->load($filePath);
        $worksheet = $objPHPExcel->getAllSheets();
        $this->data = $worksheet;
    }


    /**
     * 获取绝对文件路径
     * @return string
     */
    public function getFileAbsolutePath()
    {
        return config('filesystems.disks.import')['path'].DIRECTORY_SEPARATOR .$this->fileRelativePath;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getSheetIndexArray()
    {
        return array_keys($this->data);
    }

    /**
     * @param $sheetIndex
     * @return mixed
     */
    public function getSheetData($sheetIndex)
    {
        return $this->data[$sheetIndex]->toArray();
    }

    /**
     * 错误记录
     * @param $taskName
     * @param $logArr
     */
    public function errorLog($taskName, $logArr)
    {
          $dao = new ImporterDao;
          $dao->createErrorLog($logArr);
          $logArr['title'] = $taskName;
          Log::channel('import_log')->info($logArr);
    }

    /**
     * 根据身份证 获取 出生日期
     * @param $idCard
     * @return string
     */
    public function getBirthday($idCard)
    {
        $bir = substr($idCard, 6, 8);
        $year = (int) substr($bir, 0, 4);
        $month = (int) substr($bir, 4, 2);
        $day = (int) substr($bir, 6, 2);
        return $year . "-" . $month . "-" . $day;
    }
}
