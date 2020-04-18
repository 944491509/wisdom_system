<?php


namespace App\Http\Controllers\Teacher\Code;


use App\Dao\Users\UserCodeRecordDao;
use App\Http\Controllers\Controller;
use App\Http\Requests\MyStandardRequest;
use App\Models\Teachers\TeacherCode;
use App\Utils\FlashMessageBuilder;
use App\Utils\Misc\ConfigurationTool;

class CodeController extends Controller

{

    /**
     * 开通设置
     * @param MyStandardRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function set(MyStandardRequest $request)
    {
        $data = TeacherCode::orderBy('id', 'desc')->paginate(ConfigurationTool::DEFAULT_PAGE_SIZE);
        $this->dataForView['list'] = $data;
        return view('teacher.code.set', $this->dataForView);
    }

    /**
     * 添加
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add()
    {
        return view('teacher.code.create', $this->dataForView);
    }

    /**
     * 编辑
     * @param MyStandardRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(MyStandardRequest $request)
    {
        $data = TeacherCode::where('id', $request->get('id'))->first();
        $this->dataForView['old'] = $data;
        return view('teacher.code.edit', $this->dataForView);
    }

    /**
     * 保存
     * @param MyStandardRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(MyStandardRequest $request)
    {
        $data = $request->get('code');
        if (isset($data['id'])) {
            $result = TeacherCode::where('id', $data['id'])->update($data);
        } else {
            $result = TeacherCode::create($data);
        }
        if ($result) {
            FlashMessageBuilder::Push($request, FlashMessageBuilder::SUCCESS, '操作成功');
        } else {
            FlashMessageBuilder::Push($request, FlashMessageBuilder::DANGER, '操作失败');
        }
        return redirect()->route('teacher.code.set');
    }



    public function list(MyStandardRequest $request)
    {
        $schoolId = $request->getSchoolId();
        $dao = new UserCodeRecordDao();
        $list = $dao->getCodeRecordBySchoolId($schoolId);

        $this->dataForView['pageTitle'] = '二维码使用记录';
        $this->dataForView['list'] = $list;
        return view('teacher.code.list', $this->dataForView);
    }


}
