<?php


namespace App\Models\OA;


use Illuminate\Database\Eloquent\Model;

class ProjectTaskFiles extends Model
{
    protected $table = 'oa_project_task_files';

    public $fillable = [
        'task_id', 'url', 'file_name',
    ];

    public $timestamps = false;


    const DRIVER_LOCAL      = 1;
    const DRIVER_ALI_YUN    = 2;
    const DRIVER_QI_NIU     = 3;

    const DEFAULT_UPLOAD_PATH_PREFIX = 'public/users/';   // 存放用户文件路径
    const DEFAULT_URL_PATH_PREFIX = '/storage/users/';     // 对外的


    /**
     * 转换上传路径到 url 路径
     * @param $uploadPath
     * @return string
     */
    public static function ConvertUploadPathToUrl($uploadPath)
    {
        // 本地图片服务
        if(env('NETWORK_DISK_DRIVER', self::DRIVER_LOCAL) === self::DRIVER_LOCAL) {
            return str_replace(self::DEFAULT_UPLOAD_PATH_PREFIX, self::DEFAULT_URL_PATH_PREFIX, $uploadPath);
        }
        return '';
    }


    /**
     * 转换 url 路径 为上传路径
     * @param $url
     * @return string
     */
    public static function ConvertUrlToUploadPath($url)
    {
        // 本地图片服务
        if(env('NETWORK_DISK_DRIVER', self::DRIVER_LOCAL) === self::DRIVER_LOCAL) {
            return str_replace(self::DEFAULT_URL_PATH_PREFIX,self::DEFAULT_UPLOAD_PATH_PREFIX, $url);
        }
        return '';
    }

    public function getUrlAttribute($value)
    {
        return $value ? asset($value) : '';
    }
}
