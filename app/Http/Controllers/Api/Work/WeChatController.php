<?php

namespace App\Http\Controllers\Api\Work;

use App\Http\Controllers\Controller;
use App\Model\Order;
use App\Model\Work;
use Illuminate\Http\Request;

class WeChatController extends Controller
{
    //
    public function selectWork(Request $request){
        $work_model  = new Work();
        //分页，每页10条
        $limit = 10;
        $offset = $request->route("page") * $limit - $limit;

        $result = $work_model::query()->limit(10)
            ->offset($offset)->orderByDesc("evaluations.created_at")
            ->orderByDesc("evaluations.top")
            ->get()->toArray();
        return $result;
    }
}
