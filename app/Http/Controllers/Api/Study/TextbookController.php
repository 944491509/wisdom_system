<?php


namespace App\Http\Controllers\Api\Study;


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
}
