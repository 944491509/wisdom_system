<?php


namespace App\Http\Controllers\Api\OA;

use App\Dao\OA\InternalMessageDao;
use App\Dao\Users\UserDao;
use App\Http\Controllers\Controller;
use App\Http\Requests\MyStandardRequest;
use App\Models\OA\InternalMessage;
use App\Models\OA\InternalMessageFile;
use App\Utils\JsonBuilder;
use Carbon\Carbon;

class InternalMessageController extends Controller
{

    /**
     * 发信
     * @param MyStandardRequest $request
     * @return string
     */
    public function addMessage(MyStandardRequest $request)
    {
        $user        = $request->user();
        $collectId   = $request->get('collectId');
        $collectUser = $request->get('collectUser');
        $title       = $request->get('title');
        $content     = $request->get('content');
        $type        = $request->get('type');
        $isRelay     = $request->get('isRelay');
        $relayId     = $request->get('relayId');
        $isFile      = $request->get('isFile');
        $fileArr     = $request->get('image');

        $dao = new InternalMessageDao;

        $data = [
            'user_id'           => $user->id,
            'collect_user_id'   => $collectId,
            'collect_user_name' => $collectUser,
            'title'             => $title,
            'content'           => $content,
            'type'              => $type,
            'is_relay'          => $isRelay,
            'relay_id'          => $relayId,
            'is_file'           => $isFile,
        ];

        $result = $dao->create($data, $fileArr);
        $msg = $result->getMessage();
        if ($result->isSuccess()) {
            return JsonBuilder::Success($msg);
        } else {
            return JsonBuilder::Error($msg);
        }
    }

    /**
     * 信件列表
     * @param MyStandardRequest $request
     * @return string
     */
    public function messageList(MyStandardRequest $request)
    {
        $user = $request->user();
        $type = $request->get('type');
        if ($type == 1 || $type == 2) {
            $condition['collect_user_id'] = $user->id;
            $condition['type']   = $type; # 1未读 2已读
        }

        if ($type == 3 || $type == 4) {
            $condition['user_id'] = $user->id;
            $condition['type']    = $type; # 3已发送 4草稿箱
        }

        $dao = new InternalMessageDao;
        $data   = $dao->getInternalMessageByUserId($user->id, $condition);
        $result = [];

        foreach ($data as $key => $val) {

            $profile = $val->teacherProfile;
            $user    = $val->user;

            $result[$key]['id']                = $val->id;
            $result[$key]['collect_user_name'] = $val->collect_user_name;
            $result[$key]['title']             = $val->title;
            $result[$key]['content']           = $val->content;
            $result[$key]['is_file']           = $val->is_file;
            $result[$key]['create_time']       = $val->created_at->format('Y-m-d H:i');
            $result[$key]['user_username']     = $user->name;
            $result[$key]['user_pics']         = $profile->avatar;
        }

        return [
            'code' => JsonBuilder::CODE_SUCCESS,
            'message' => "ok",
            'data' => $result,
            'lastPage' => $data->lastPage(),
        ];
    }

    /**
     * 信息详情
     * @param MyStandardRequest $request
     * @return string
     */
    public function massageInfo(MyStandardRequest $request)
    {
        $id = $request->get('id');

        $dao  = new InternalMessageDao;
        $data = $dao->getInternalMessageById($id);
        $data->file;
        $data['user_username'] = $data->user->name;
        $data['create_time'] = $data->created_at->format('Y-m-d H:i');
        $data['relay'] = [];

        if ($data->is_relay == InternalMessage::IS_RELAY) { // 是否有转发内容
            $data['relay'] = $dao->getForwardMessageByIds(explode(',', $data->message_id));
            foreach ($data['relay'] as $key => $val) {
                $data['relay'][$key]['user_username'] = $data->user->name;
                $data['relay'][$key]['create_time'] = $data->created_at->format('Y-m-d H:i');
                if ($val->file) {
                    $data['relay'][$key]['file'] = $val->file;
                } else {
                    $data['relay'][$key]['file'] = [];
                }
            }
        }

        if ($data->is_file == InternalMessage::IS_FILE) {
            $data->file;
        }else {
            $data['file'] = [];
        }

        $data->makeHidden('user');
        return JsonBuilder::Success($data);
    }


    /**
     * 更新已读 或 删除
     * @param MyStandardRequest $request
     * @return string
     */
    public function updateOrDelMessage(MyStandardRequest $request)
    {
        $type = $request->get('type');
        $id = $request->get('id');
        $dao = new InternalMessageDao;
        if ($type == InternalMessage::DELETE) {
            $result = $dao->updateMessage($id, ['status' => InternalMessage::STATUS_ERROR]);
        } else {
            $result = $dao->updateMessage($id, ['type' => InternalMessage::TYPE_READ]);
        }

        if ($result) {
            return JsonBuilder::Success('更新成功');
        } else {
            return JsonBuilder::Error('更新失败');
        }
    }

    /**
     * 上传图片
     * @param MyStandardRequest $request
     * @return string
     */
    public function uploadFiles(MyStandardRequest $request)
    {
        $files   = $request->file('file');
        $fileArr = [];
        if ($files) {
            foreach ($files as $key => $file) {
                $fileArr[$key]['name'] = $file->getClientOriginalName();
                $fileArr[$key]['type'] = $file->extension();
                $fileArr[$key]['size'] = getFileSize($file->getSize());
                $fileArr[$key]['path'] = InternalMessage::internalMessageUploadPathToUrl($file->store(InternalMessage::DEFAULT_UPLOAD_PATH_PREFIX));
            }
        }

        return JsonBuilder::Success(['imgPath' => $fileArr]);
    }



    /**
     * 获取当前学校所有老师
     * @param MyStandardRequest $request
     * @return string
     */
    public function getTeachers(MyStandardRequest $request)
    {
        $user     = $request->user();
        $schoolId = $user->getSchoolId();

        $dao      = new UserDao;
        $teachers = $dao->getTeachersBySchool($schoolId);

        $data = [];
        foreach ($teachers as $key => $teacher) {
            $data[$key]['id']     = $teacher->user_id;
            $data[$key]['name']   = $teacher->name;
            $data[$key]['avatar'] = $teacher->user->profile->avatar;
        }

        return JsonBuilder::Success($data);
    }

    /**
     * 更新信件
     * @param MyStandardRequest $request
     * @return string
     */
    public function messageUpdate(MyStandardRequest $request)
    {
        $id = $request->get('id');

        $user        = $request->user();
        $collectId   = $request->get('collectId');
        $collectUser = $request->get('collectUser');
        $title       = $request->get('title');
        $content     = $request->get('content');
        $type        = $request->get('type');
        $isRelay     = $request->get('isRelay');
        $relayId     = $request->get('relayId');
        $isFile      = $request->get('isFile');
        $fileArr     = $request->get('image');

        $dao = new InternalMessageDao;

        $data = [
            'user_id'           => $user->id,
            'collect_user_id'   => $collectId,
            'collect_user_name' => $collectUser,
            'title'             => $title,
            'content'           => $content,
            'type'              => $type,
            'is_relay'          => $isRelay,
            'relay_id'          => $relayId,
            'is_file'           => $isFile,
        ];

        $result = $dao->update($id, $data, $fileArr);
        if ($result) {
            return JsonBuilder::Success('更新成功');
        } else {
            return JsonBuilder::Error('更新失败');
        }

    }

}
