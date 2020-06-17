<?php

namespace App\Http\Controllers\Api\Location;

use App\Http\Controllers\Controller;
use App\Models\CnArea;
use App\Utils\JsonBuilder;
use Illuminate\Http\Request;

class NewAreaController extends Controller
{

    public function getArea(Request $request)
    {
        $code = $request->get('area_code');
        $data  = CnArea::where('parent_code', $code)->get();
        return JsonBuilder::Success($data);
    }

}
