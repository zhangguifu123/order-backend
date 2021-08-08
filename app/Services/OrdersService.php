<?php

namespace App\Services;

use App\Model\Association;
use App\Model\Order;
use App\Model\Work;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \Redis;
class OrdersService
{
    /**
     * 通过订单号 修改订单
     * @param $update_data
     * @return bool|mixed
     */
    public function updateMysqlOrder($update_data) {
        $check_params = array_column($update_data, 'order_number');
        $model = new Order();
        $check = $model::query()->whereIn('order_number',$check_params)->get()->toArray();
        if (empty($check)) {
            return msg(7, __LINE__);
        }
        $lost_order = [];
        foreach ($update_data as $data) {
            $order = $model::query()->where('order_number',$data['order_number'])->where('goods', $data['goods'])->first();
            //若查询不到 则抓出来
            if (!$order){
                $lost_order['wrong_order_number'][] = $data['order_number'];
            } else {
                $order->update($data);
            }
        }
        //返回商家自行查找
        if (!empty($lost_order)) {
            $lost_order['code'] = 400;
            return $lost_order;
        }
        return $update_data;
    }

    public function pushRedisWork($supplier){
        try {
            $redis  = new Redis();
            $redis->connect("order_redis", 6379);
            $redis->rPush('work',$supplier);
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * 获取excel缓存订单
     * @param $data
     * @return bool|mixed
     */
    public function selectMysqlOrder($data) {
        try {
            $model  = new Order();
            $result = $model::query()
                ->where('file_name',$data['fileId'])
                ->where('logistics_number','!=',0)
                ->count();

            return $result;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 查看redis订单
     * @return array|bool
     */
    public function selectAllCacheOrderCount(){
        try {
            $redis = new Redis();
            $redis->connect("order_redis", 6379);
            $suppliers = $redis->hKeys('supplier');
            $result = [];
            foreach ($suppliers as $supplier) {
                $files = $redis->hGetAll($supplier);
                //获取文件数据
                foreach ($files as $fileId => $data){
                    $data = json_decode($data,true);
                    //获取订单数量
                    $count = count($data);
                    $result[$supplier][$fileId] = $count;
                }
            };
            if (empty($result) ){
                return false;
            }
            return $result;
        } catch (Exception $e) {
            return false;
        }
    }
    /**
     * 获取excel缓存订单
     * @param $data
     * @return bool|mixed
     */
    public function selectCacheOrder($data) {
        try {
            $redis  = new Redis();
            $redis->connect("order_redis", 6379);
            $result = $redis->hGet($data['supplier'],$data['fileId']);
            if (empty($result) ){
                return false;
            }
            return json_decode($result ,true);
        } catch (Exception $e) {
            return false;
        }
    }

    public function delAllCacheOrder() {
        try {
            $redis  = new Redis();
            $redis->connect("order_redis", 6379);
            $suppliers = $redis->hKeys('supplier');
            if (!empty($suppliers)) {
                foreach ($suppliers as $supplier){
                    $redis->hDel($supplier);
                }
                $redis->hDel('supplier');
            } else {
                return false;
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 删除excel缓存订单
     * @param $data
     * @return bool|mixed
     */
    public function delCacheOrder($data) {
        try {
            $redis  = new Redis();
            $redis->connect("order_redis", 6379);
            $result = $redis->hDel($data['supplier'],$data['fileId']);
            $check  = $redis->hGetAll($data['supplier']);
            if (empty($check)) {
                $redis->hDel('supplier', $data['supplier']);
            }
            if (empty($result) ){
                return false;
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    public function dataHandle(Request $request){
        //声明理想数据格式
        $mod = [
            "supplier"   => ["string", "max:400"],
            "fileId"   => ["string", "max:400"],
        ];
        //是否缺失参数
        if (!$request->has(array_keys($mod))){
            return msg(1,__LINE__);
        }
        //提取数据
        $data = $request->only(array_keys($mod));
        //判断数据格式
        if (Validator::make($data, $mod)->fails()) {
            return msg(3, '数据格式错误or文件名称过长' . __LINE__);
        };
        return $data;
    }

    /**
     * 通过商品 获取商家
     * @param $goods
     * @return array
     */
    public function getSupplier ($goods) {
        $model    = new Association();
        $supplier = $model->query()->where('goods',$goods)->get()->toArray();
        $supplier = $supplier[0]['supplier'];

        return $supplier;
    }

}
