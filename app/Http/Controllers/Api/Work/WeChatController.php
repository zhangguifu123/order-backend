<?php

namespace App\Http\Controllers\Api\Work;

use App\Http\Controllers\Controller;
use App\Model\Order;
use App\Model\Work;
use App\Services\HttpService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WeChatController extends Controller
{
    //
    public function repeatWork(Request $request){
        if (empty($request['workIds'])) {
            return msg(1, __LINE__);
        }
        $workIds       = $request['workIds'];
        $returnWorkIds = [];
        $work_model    = new Work();
        $http_model    = new HttpService();
        foreach ($workIds as $workId ) {
            $work_model = $work_model::query()->where('work_id',$workId);
            $workData   = $work_model->get(['export_url','wx_id'])->toArray();
            if (empty($workData)) {
                $returnWorkIds['badWorkId'] = $workId;
                return msg(11, $returnWorkIds);
            }
            $wxId     = $workData[0]['wx_id'];
            $url      = $workData[0]['export_url'];
            $pushUrl          = '47.94.130.183:8085/sendTextMsg';
            $pushToWeChatData = ['wxid' => $wxId, 'content' => '今日订单：'.$url, ];
            //Log::notice('微信推送参数：'.$pushToWeChatData,[]);
            $result = $http_model->pushWeChat($pushUrl, json_encode($pushToWeChatData));
            $result = json_decode($result, true);
            if ($result['code'] !== 200) {
                $returnWorkIds['badWorkId'] = $workId;
                return msg(8, $returnWorkIds);
            }
            $work_model->update(['wx_status' => 1]);
            $returnWorkIds['okWorkIds'][] = $workId;
        }
        return msg(0, $returnWorkIds);
    }

    /**
     * @param Request $request
     * @return string
     */
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

    /**
     * @param Request $request
     * @return string
     */
    public function selectSupplierWork(Request $request){
        $work_model = new Work();
        //分页，每页10条
        $limit      = 10;
        $offset     = $request->route("page") * $limit - $limit;
        $work_model = $work_model::query()->where('supplier', $request->supplier);
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

    /**
     * @param Request $request
     * @return string
     */
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

    /**
     * @param Request $request
     * @return string
     */
    public function supplierSearch(Request $request) {
        $model = new Work();
        if (isset($request['start_date']) && isset($request['end_date'])) {
            $model = $model::query()->where('supplier', $request->supplier)
                ->where('created_at','>',Carbon::parse($request->start_date))
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
