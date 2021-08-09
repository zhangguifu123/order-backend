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
        $work_model = new Work();
        //分页，每页10条
        $limit      = 10;
        $offset     = $request->route("page") * $limit - $limit;
        $work_model = $work_model::query();
        //数据total数量
        $count      = $work_model->count();
        $work       = $work_model->limit(10)
            ->offset($offset)->orderByDesc("created_at")
            ->get()->toArray();
        $result     = [
            'total' => $count,
            'data'  => $work
        ];
        return msg(0, $result);
    }
}
