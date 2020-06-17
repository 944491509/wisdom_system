<?php


namespace App\BusinessLogic\ImportExcel\Impl;


use App\BusinessLogic\ImportExcel\Contracts\IImportExcel;
use PhpOffice\PhpSpreadsheet\IOFactory;

abstract class AbstractImporter implements IImportExcel
{
    protected $filePath;
    protected $data;
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
        $this->data   = [];
    }

    /**
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function loadExcelFile()
    {
        set_time_limit(0);
        ini_set('memory_limit', -1);
        $filePath = config('filesystems.disks.import')['path'].DIRECTORY_SEPARATOR .$this->filePath;
        $objReader = IOFactory::createReader('Xlsx');
        $objPHPExcel = $objReader->load($filePath);  //$filename可以是上传的表格，或者是指定的表格
        $worksheet = $objPHPExcel->getAllSheets();
        $this->data = $worksheet;
    }


    /**
     * @return mixed
     */
    public function getConfig()
    {
        return $this->config;
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


}
