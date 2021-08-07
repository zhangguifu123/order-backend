<?php

namespace App\Services;

use App\Model\Association;
use App\Model\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use \Redis;
class OrdersService
{
    /**
     * 获取excel缓存订单
     * @param $data
     * @return bool|mixed
     */
    public function selectMysqlOrder($data) {
        try {
            $model  = new Order();
            $result = $model::query()
                ->where('file_name',$data['fileName'])
                ->whereNotIn('logistics_number',['空'])
                ->count();

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
            $result = $redis->hGet($data['supplier'],$data['fileName']);
            if (empty($result) ){
                return false;
            }
            return json_decode($result ,true);
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
            $result = $redis->hDel($data['supplier'],$data['fileName']);
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
            "fileName"   => ["string", "max:400"],
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
