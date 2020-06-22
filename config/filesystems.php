<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
        ],
        //android的apk文件存储路径
        'apk' => [
            'driver' => 'local',
            'root' => storage_path('app/apk'),
            'url' => env('APP_URL').'/storage/app/apk',
            'visibility' => 'public',
        ],
        //社群的图片存储路径
        'community'     => [
            'driver' => 'local',
            'root' => storage_path('app/community'),
            'url' => env('APP_URL').'/storage/app/community',
            'visibility' => 'public',
        ],
        'import'        => [
            'driver'     => 'local',
            'root'       => storage_path('app/import'),
            'path'       => storage_path('app'),
            'url'        => env('APP_URL') . '/storage/app/import',
            'visibility' => 'public',
        ],
        // 轮播图图片存储路径
        'banner'        => [
            'driver'     => 'local',
            'root'       => storage_path('app/public/banner'),
            'url'        => env('APP_URL') . '/storage/public/banner',
            'visibility' => 'public',
        ],
        // 学生人脸识别照片
        'student_photo' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public/student_photo'),
            'url'        => env('APP_URL') . '/storage/public/student_photo',
            'visibility' => 'public',
        ],
    ],

];
