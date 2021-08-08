<?php

namespace App\Console\Commands;

use App\Model\Order;
use App\Model\Work;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use \Redis;
class WorkInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'work:redis';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '消费队列';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $redis    = new Redis();
            $redis->connect("order_redis", 6379);
            $supplier = $redis->lPop('work');
            if (!$supplier) {
                die();
            }
            $model       = new Work();
            $order_model = new Order();
            $workIds   = $model::query()->where('supplier', $supplier)->where('status', 1)->get('work_id')->toArray();
            $workIds   = array_column($workIds, 'work_id');
            foreach ($workIds as $workId) {
                $reback = $order_model::query()->where('work_id', $workId)->where('logistics_number','!=',0)->count();
                if ($reback != 0){
                     $result = $model::query()->where('work_id',$workId)->first()->update(['reback_count' => $reback]);
                }
            }
        } catch (Exception $e) {
            return false;
        }
    }
}
