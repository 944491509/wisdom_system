<?php


namespace App\Http\Controllers\Api\Study;


use App\Dao\Textbook\TextbookDao;
use App\Http\Requests\Textbook\TextbookRequest;
use App\Utils\JsonBuilder;
use App\Models\Schools\Textbook;
use App\Http\Controllers\Controller;

class TextbookController extends Controller
{

    /**
     * 教材类型
     * @return string
     */
    public function allType() {
        $textbook = new Textbook();
        $all = $textbook->getAllType();
        $data = [];
        foreach ($all as $key => $val) {
            $data[] = [
                'id' => $key,
                'type' => $val,
            ];
        }
        return JsonBuilder::Success($data);
    }


    /**
     * 编辑教材 添加和保存
     * @param TextbookRequest $request
     * @return string
     * @throws \Illuminate\Validation\ValidationException
     */
    public function save(TextbookRequest $request) {
        $all = $request->all();
        $rules = [
            'name' => 'required',
            'edition' => 'required',
            'author' => 'required',
            'press' => 'required',
            'type' => 'required | int',
            'purchase_price' => 'required',
            'price' => 'required',
            'year' => 'required | int',
            'term' => 'required | int',
            'school_id' => 'required | int',
        ];

        $this->validate($request,$rules);
        $textbookDao = new TextbookDao();
        if(empty($all['id'])) {
            $result = $textbookDao->create($all);
        } else {
            $result = $textbookDao->editById($all);
        }

        $msg = $result->getMessage();
        if($result->isSuccess()) {
            $data = $result->getData();
            return JsonBuilder::Success(['id'=>$data['id']], $msg);
        } else {
            return JsonBuilder::Error($msg);
        }
    }
}
