<?php


namespace App\Http\Controllers\Operator;


use App\BusinessLogic\ImportExcel\Factory;
use App\Dao\Importer\ImporterDao;
use App\Dao\Users\UserDao;
use App\Http\Controllers\Controller;
use App\Models\Importer\ImportTask;
use App\User;
use App\Utils\FlashMessageBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;

class ImporterController extends Controller
{

    public function manager(Request $request)
    {
        $schoolId = $request->session()->get('school.id');
        $dao = new ImporterDao();
        $tasks = $dao->getTasks($schoolId);
        $this->dataForView['tasks'] = $tasks;
        return view('school_manager.importer.list', $this->dataForView);
    }

    public function add()
    {
        $this->dataForView['task'] = new ImportTask();
        return view('school_manager.importer.add', $this->dataForView);
    }

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

        if ($data['type'] == ImportTask::IMPORT_TYPE_NO_IDENTITY) {
            // todo:: 验证
        } elseif($data['type'] == ImportTask::IMPORT_TYPE_CERTIFIED) {
            // todo:: 验证
        }

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
        return redirect()->route('school_manager.importer.manager');


    }

    public function result(Request $request, $id)
    {
        $schoolId = $request->session()->get('school.id');
        $dao = new ImporterDao();
        $messages = $dao->result($id, $schoolId);
        $this->dataForView['messages'] = $messages;
        return view('school_manager.importer.result', $this->dataForView);

    }

}
