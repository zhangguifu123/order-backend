<?php

namespace App\Http\Controllers\Api\Work;

use App\Http\Controllers\Controller;
use App\Model\Order;
use App\Model\Work;
use Carbon\Carbon;
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

    public function search(Request $request) {
        $model = new Work();
        if (isset($request['start_date']) && isset($request['end_date'])) {
            $model = $model::query()->where('created_at','>',Carbon::parse($request->start_date))
                ->where('created_at','<',Carbon::parse($request->end_date));
            if (isset($request['status'])) {
                $model = $model->where('status',$request['status']);
            }
        } elseif (isset($request['status'])) {
            $model = $model::query()->where('status',$request['status']);
        } else {
            $model = $model::query();
        }
        $count = $model->count();
        //分页，每页10条
        $limit  = 10;
        $offset = $request->route("page") * $limit - $limit;
        $order  = $model->limit(10)
            ->offset($offset)->get()->toArray();
        $result = [
            'total' => $count,
            'data'  => $order
        ];
        return msg(0, $result);
    }
}
