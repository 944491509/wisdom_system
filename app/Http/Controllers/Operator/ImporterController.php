<?php


namespace App\Http\Controllers\Operator;


use App\BusinessLogic\ImportExcel\Impl\ImporterConfig;
use App\Dao\Importer\ImporterDao;
use App\Http\Controllers\Controller;
use App\Models\Importer\ImportTask;
use App\Utils\FlashMessageBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImporterController extends Controller
{
    /**
     * 列表
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function manager(Request $request)
    {
        $schoolId = $request->session()->get('school.id');
        $dao = new ImporterDao();
        $tasks = $dao->getTasks($schoolId);
        $this->dataForView['tasks'] = $tasks;
        return view('school_manager.importer.list', $this->dataForView);
    }

    /**
     * 添加页面
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add()
    {
        $this->dataForView['task'] = new ImportTask();
        return view('school_manager.importer.add', $this->dataForView);
    }

    /**
     * 保存
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     */
    public function save(Request $request)
    {
        $schoolId = $request->session()->get('school.id');
        $data = $request->get('task');
        $user = $request->user();

        $dao = new ImporterDao;

        $file = $request->file('source');

        if ('xlsx' != $file->extension()) {
            FlashMessageBuilder::Push($request, FlashMessageBuilder::DANGER, '资源文件类型错误');
            return redirect()->back()->withInput();
        } else {
            $path = $file->store('import');
        }
        // 验证文件格式
        $fileFormat = new ImporterConfig($path, $data['type']);
        $validation = $fileFormat->validation();
        if (!empty($validation)) {
            Storage::delete($path); // 删除错误文件
            $errorStr = '';
            foreach ($validation as $value) {
                $errorStr.= $value;
            }
            FlashMessageBuilder::Push($request, FlashMessageBuilder::DANGER, '文件格式错误' . $errorStr);
        } else {
            $data['manager_id'] = $user->id;
            $data['school_id'] = $schoolId;
            $data['path'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
            $result = $dao->create($data);
            if ($result) {
                FlashMessageBuilder::Push($request, FlashMessageBuilder::SUCCESS, $data['title'] . '任务保存成功');
            } else {
                FlashMessageBuilder::Push($request, FlashMessageBuilder::DANGER, '无法保存' . $data['title']);
            }
        }
        return redirect()->route('school_manager.importer.manager');
    }

    /**
     * 结果页面
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function result(Request $request)
    {
        $id = $request->get('id');
        $dao = new ImporterDao();
        $messages = $dao->result($id);
        $this->dataForView['messages'] = $messages;
        return view('school_manager.importer.result', $this->dataForView);
    }

    /**
     * 撤回
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function withdraw(Request $request)
    {
        $id = $request->get('id');
        $dao = new ImporterDao;
        $dao->update($id, ['status' => ImportTask::IMPORT_TASK_WITHDRAW]);
        return redirect()->route('school_manager.importer.manager');
    }

    /**
     * 下载模板
     * @param Request $request
     * @return mixed
     */
    public function download(Request $request)
    {
        $type = $request->get('type');
        switch ($type) {
            case ImportTask::IMPORT_TYPE_NO_IDENTITY:
                $file = 'import/template/未认证用户导入模板.xlsx';
                break;
            case ImportTask::IMPORT_TYPE_CERTIFIED:
                $file = 'import/template/新生导入模板.xlsx';
                break;
            case ImportTask::IMPORT_TYPE_ADDITIONAL_INFORMATION:
                $file = 'import/template/导入学生寄宿信息模板.xlsx';
                break;
        }
        return Storage::download($file);
    }

}
