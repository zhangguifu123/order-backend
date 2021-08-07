<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class lottery extends Model
{
    //
    protected $fillable = [
        "pid","type", "name", "total", "chance","daynum", "pay"
    ];
    public $total_chance = 10000;
    private $thanks_prize = [
        'id' => 0,
        'pid' => 0,
        'type' => 1,
        'name' => '谢谢参与'
    ];
    /**
     * 插入奖品
     */
    public function add_lottery(){
        $data = ["pid" => 13, "type" => 1, "name" => '玩偶公仔', "total" => 1, "chance" => 1,"daynum" => 1, "pay" => 1];
        $lottery = new lottery($data);
        if ($lottery->save()){
            return 0;
        };
    }
    /**
     * 重构奖池、重组概率
     * @return array
     */
    public function init_lottery_pond($prize){
        $award = [];

        //加入谢谢惠顾
        $now_chance = array_sum(array_column($prize,'chance'));
        $remain_chance = $this->total_chance - $now_chance;
        $prize[] = ['id' => 0,'pid' => 0,"type" => 1, "name" => '谢谢参与', "total" => 0, "chance" => $remain_chance,"daynum" => 0, "pay" => 0];

        //重组概率
        $num = 0;
        foreach ($prize as $_v){
            $num += $_v['chance'];
            $award[] = ['id' => $_v['id'], 'pid' => $_v['pid'], 'type' => $_v['type'], 'name' => $_v['name'], 'total' => $_v['total'], 'chance' => $num, 'daynum' => $_v['daynum'], 'pay' => $_v['pay']];
        }

        return $award;
    }

    /**
     * 获取抽奖结果
     * @return array
     */
    public function get_prize()
    {
        $prize = lottery::query()->get()->toArray();
        return $prize;
    }



    /**
     * 抽奖过滤回调函数
     * @param $result
     * @return array
     */
    public function filter($result)
    {
        //奖品总数限制，此处应该查数据库
        if ($result['id'] != 0){
            if ($result['total'] > 0){
                $lottery = lottery::query()->find($result['id']);
                $yet_num = $lottery->total;
                DB::table('lotteries')->where('id',$result['id'])->increment('total', -1);
                $yet_today_num = $lottery->daynum;
            }else{
                $yet_num = 0;
                $yet_today_num = 0;
            }
        }else{
            $yet_num = 0;
            $yet_today_num = 0;
        }


        if($result['pid'] != 0 && $yet_num > $result['total']) {
            $result = $this->thanks_prize;
        }

        //奖品每日数量限制，此处应该查数据库
        if($result['pid'] != 0 && $yet_today_num > $result['daynum']) {
            $result = $this->thanks_prize;
        }

        //不暴露敏感信息
        unset($result['total'], $result['chance'], $result['daynum'], $result['pay'] );
        return $result;
    }



}
