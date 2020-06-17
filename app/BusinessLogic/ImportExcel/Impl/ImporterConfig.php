<?php


namespace App\BusinessLogic\ImportExcel\Impl;

use App\Models\Importer\ImportTask;

class ImporterConfig
{

    /**
     * 文件格式标准
     * @return array
     */
    private function config()
    {
        return [
            ImportTask::IMPORT_TYPE_NO_IDENTITY => [], // 未认证
            ImportTask::IMPORT_TYPE_CERTIFIED => [], // 已认证
        ];
    }

    /**
     * 获取一个文件格式标准
     * @param $type
     * @return array|mixed
     */
    private function getConfigType($type)
    {
        $config = $this->config();
        return $config[$type];
    }

    /**
     * 验证文件是否符合文件格式标准
     * @param $filePath
     * @param $fileType
     */
    public function validation($filePath, $fileType)
    {

    }
}
