<?php

namespace App\ThirdParty;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;

/**
 * 华三云班牌接口
 */
class CloudOpenApi
{

    const ERROR_CODE_OPEN_API_OK = 0; // 华三接口正常返回code
    const UPDATE_STUDENT_PHOTO = 1; // 更新照片
    
	private $appId;

	private $appSecret;

	private $timestamp;

    private $apiUrl;

    private $schoolUUid = '4b74dffc-e17c-4ba5-9f24-db0002639b82';

    public function __construct()
    {
        $this->appId     = env('CLOUD_APP_ID');
        $this->appSecret = env('CLOUD_APP_SECRET');
        $this->apiUrl    = env('CLOUD_APP_API');
        $this->timestamp = time();
    }


    /**
     * 计算 AccessKey
     */
    private function accessKey()
    {
        $test = $this->appId. $this->appSecret. $this->timestamp;
        return  md5($test);
    }

    /**
     * @param $imgPath
     * @param null $faceCode 更新需要
     * @return string
     */
    public function makePostUploadFaceImg($imgPath, $faceCode = null)
    {
        error_reporting(0);

        $url   = '/open/nva/custom_upload_face_img/';

        $headers = [
            'x-app-id'                => $this->appId,
            'x-access-key'            => $this->accessKey(),
            'x-time-stamp'            => $this->timestamp,
            'X-Custom-Header-3School' => $this->schoolUUid
        ];
        $data = [
            [
                'name'     => 'category',
                'contents' => 'face'
            ],
            [
                'name'     => 'file',
                'contents' => fopen($imgPath, 'r')
            ]
        ];

        if ($faceCode) {
            array_push($data, [
                'name'     => 'face_code',
                'contents' => $faceCode
            ]);
        }
        if (App::environment('local')) {
            return ['code' => 0, 'data' => ['face_code' => '123'], 'message' => '正常'];
        }
        $client   = new Client;
        $response = $client->request('POST', $this->apiUrl.$url, [
            'headers'   => $headers,
            'multipart' => $data
        ]);
        return  json_decode($response->getBody()->getContents(), true);
    }
}
