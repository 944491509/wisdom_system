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
        $schoolId                   = $request->session()->get('school.id');
        $dao                        = new ImporterDao();
        $tasks                      = $dao->getTasks($schoolId);
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
        $data     = [];
        $dao      = new ImporterDao();
        $data     = $request->get('task');
        if ($data['type'] == 1) {
            $data['config'] = json_encode(json_decode(strip_tags($data['config']), 1));
        }
        $data['title']      = strip_tags($data['title']);
        $user               = $request->user();
        $data['manager_id'] = $user->id;


        $fileCharater = $request->file('source');
        if (!empty($fileCharater) && $fileCharater->isValid()) {
            //获取文件的扩展名
            $ext = $fileCharater->getClientOriginalExtension();

            if ('xlsx' != $ext)
            {
                FlashMessageBuilder::Push($request, FlashMessageBuilder::DANGER,'资源文件类型错误');
                return redirect()->back()->withInput();
            }
            if ($data['type'] == 0) {
                $fileName = $schoolId.'/'.rand(1, 99).time().'/'.$fileCharater->getClientOriginalName();
            } else {
                $fileName = $schoolId.'/'.$fileCharater->getClientOriginalName();
            }
            //获取文件的绝对路径
            $path = $fileCharater->getRealPath();
            //存储文件。disk里面的public。总的来说，就是调用disk模块里的public配置
            $uploadResult = Storage::disk('import')->put($fileName, file_get_contents($path));
            if ($uploadResult)
            {
                $fileConfig = config('filesystems.disks.import');
                $data['file_path'] =$fileName;
            }

        }

        if(isset($data['id'])){
            $data['school_id'] = $schoolId;
            $result = $dao->update($data);
        }
        else{
            $data['status'] = 1;
            $data['school_id'] = $schoolId;
            $result = $dao->create($data);
        }

        if($result){
            FlashMessageBuilder::Push($request, FlashMessageBuilder::SUCCESS,$data['title'].'任务保存成功');
        }else{
            FlashMessageBuilder::Push($request, FlashMessageBuilder::DANGER,'无法保存'.$data['title']);
        }
        return redirect()->route('school_manager.importer.manager');


    }

    public function result(Request $request,$id)
    {
        $schoolId= $request->session()->get('school.id');
        $dao = new ImporterDao();
        $messages = $dao->result($id,$schoolId);
        $this->dataForView['messages'] = $messages;
        return view('school_manager.importer.result', $this->dataForView);

    }

}
