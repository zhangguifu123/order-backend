<?php

namespace App\Http\Controllers\Api\Work;

use App\Http\Controllers\Controller;
use App\Services\OrdersService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CheckExcelController extends Controller
{
    /**
     * @var OrdersService
     */
    private $orderService;

    public function __construct()
    {
        $this->orderService = new OrdersService();
    }

    public function search(Request $request) {

    }
    /**
     * 查看目前缓存订单明细
     * @param Request $request
     * @return string
     */
    public function checkAllExcel(Request $request) {
        $result = $this->orderService->selectAllCacheOrderCount();
        if (!$result) {
            return msg(5, __LINE__);
        }
        return msg(0, $result);
    }
    /**
     * 查看订单明细
     * @param Request $request
     * @return array|string
     */
    public function checkExcel(Request $request){
        //获取并检查函数
        $data   = $this->orderService->dataHandle($request);
        //如果是json数据 直接返回报错
        if (!is_array($data)){
            return $data;
        }
        $result = $this->orderService->selectCacheOrder($data);
        if (!$result){
            return msg(5 , __LINE__);
        }
        return msg(0 ,$result);
    }

    /**
     * 查看回单信息
     * @param Request $request
     * @return array|string
     */
    public function checkExcelReback(Request $request){
        //获取并检查函数
        $data   = $this->orderService->dataHandle($request);
        //如果是json数据 直接返回报错
        if (!is_array($data)){
            return $data;
        }
        $result = $this->orderService->selectMysqlOrder($data);
        return msg(0 ,$result);
    }

    /**
     * 删除excel上传数据
     * @param Request $request
     * @return array|string
     */
    public function delExcel(Request $request){
        //获取并检查函数
        $data   = $this->orderService->dataHandle($request);
        //如果是json数据 直接返回报错
        if (!is_array($data)){
            return $data;
        }
        $result = $this->orderService->delCacheOrder($data);
        if (!$result){
            return msg(5 , __LINE__ );
        }
        return msg(0 ,$result);
    }

    /**
     * 删除所有excel上传数据
     * @param Request $request
     * @return array|string
     */
    public function delAllExcel(Request $request){
        $result = $this->orderService->delAllCacheOrder();
        if (!$result){
            return msg(5 , __LINE__ );
        }
        return msg(0 ,$result);
    }
}
