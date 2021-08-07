<?php

namespace App\Http\Controllers\Api\Work;

use App\Http\Controllers\Controller;

use App\Model\Association;
use App\Model\Order;
use App\Model\Work;
use App\Services\ExcelService;
use App\Services\OrdersService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPExcel_IOFactory;
use \Redis;

class DealExcelController extends Controller
{
    /**
     * @var OrdersService
     */
    private $model;

    public function __construct()
    {
        $this->model = new OrdersService();
    }


    /**
     * 导出缓存excel
     * @param Request $request
     * @return bool|string
     */
    public function exportCacheExcel(Request $request){
        try {
            //将数据保存到数据库
            $model           = new Order();
            $stencil_model   = new Association();
            $redis = new Redis();
            $redis->connect("order_redis", 6379);
            $suppliers = $redis->hKeys('supplier');
            if (empty($suppliers)){
                return msg(1,'无数据');
            }
            //获取 供应商
            foreach ($suppliers as $supplier) {
                $export_data = [];
                $files = $redis->hGetAll($supplier);
                //获取订单数量
                $count = 0;
                //获取文件数据
                foreach ($files as $fileName => $import_data){
                    $import_data = json_decode($import_data,true);
                    $count += count($import_data);
                    //插入mysql
                    $result = $model::insert($import_data);
                    if (!$result){
                        return msg(6,$import_data);
                    }
                    //删除redis
                    $redis->hdel($supplier,$fileName);
                    //放入大数组
                    foreach ($import_data as $order) {
                        $export_data[] = $order;
                    }
                }
                //查找模版
                try{
                    $stencil = $stencil_model::query()->where('supplier',$supplier)->where('goods',$export_data[0]['goods'])->get(['stencil'])->toArray();
                } catch (Exception $e) {
                    return msg(5,__LINE__);
                }


                //导出模版
                $url = $model->chooseExcelExport($export_data, $stencil[0]['stencil'], $supplier);
                $files  = array_keys($files);
                //创建推送任务
                $data   = [
                    'supplier'     => $supplier,
                    'files'        => json_encode($files),
                    'export_url'   => $url,
                    'order_count'  => $count,
                    'status'       => 1,
                    'reback_count' => 0,
                ];
                $work_model = new Work($data);
                $result = $work_model->save();
                if (!$result){
                    return msg(6,$data);
                }

                //删除redis
                $redis->hdel('supplier',$supplier);

            };

            return msg(0,__LINE__);
        } catch (Exception $e) {
            return false;
        }
    }




    public function dealExcel(Request $request){
        $excelService = new ExcelService();
        $deal = $excelService->readExcel($request);
        if ($deal === 1) {
            return msg(1, '数据解析失败');
        }
        $excel  = $deal['excel'];
        $file   = $deal['file'];

        //读取第一张表
        $sheet  = $excel->getSheet(0);
        $check  = $sheet->getCell("G1")->getValue();
        //获取上传到后台的文件名
        $fileName = $file->getClientOriginalName();
        switch ($check)
        {
            case '商品名称':
                $import_data = $this->_dealExcelStencilOne($excel, $fileName);
                break;
            case '商品信息':
                $import_data = $this->_dealExcelStencilTwo($excel, $fileName);
                break;
            case '收货地址':
                $import_data = $this->_dealExcelStencilThree($excel, $fileName);
                break;
            default:
                return msg(3,'文件模版出错！');
        }

        if (empty($import_data)) {
            return msg(1, '数据解析失败');
        }
        $goods    = array_column($import_data,'goods');

        $supplier = $this->model->getSupplier($goods[0]);
        $result   = [$supplier => $fileName];
        return msg(0, $result);
    }





    private function _dealExcelStencilThree($excel,$fileName) {
        //读取第一张表
        $sheet = $excel->getSheet(0);
        //获取总行数
        $row_num = $sheet->getHighestRow();
        //获取供应商名称
        $check    = $sheet->getCell("H2")->getValue();
        $supplier = $this->model->getSupplier($check);
        if (!$supplier){
            return null;
        }
        //生成文件Id
        $fileId  = $this->_createFileId();
        $import_data = []; //数组形式获取表格数据
        for ($i = 2; $i <= $row_num; $i++) {
            $import_data[$i]['file_id']           = $fileId;
            $import_data[$i]['file_name']         = $fileName;
            $import_data[$i]['order_number']      = $sheet->getCell("A" . $i)->getValue();
            $import_data[$i]['count']             = 1;
            $import_data[$i]['solitaire_number']  = $sheet->getCell("C" . $i)->getValue();
            $import_data[$i]['receiver']          = $sheet->getCell("D" . $i)->getValue();
            $import_data[$i]['phone']             = $sheet->getCell("F" . $i)->getValue();
            $import_data[$i]['goods']             = $sheet->getCell("H" . $i)->getValue();
            $import_data[$i]['count']             = $sheet->getCell("J" . $i)->getValue();
            $import_data[$i]['address']           = $sheet->getCell("G" . $i)->getValue();
            $import_data[$i]['created_at']        = date('Y-m-d H:i:s');
            $import_data[$i]['updated_at']        = date('Y-m-d H:i:s');
        }
        $result = $this->_cacheFile($fileName, $import_data, $supplier);
        if ($result) {
            return $import_data;
        } else {
            return false;
        }
    }

    /**
     * @param $excel
     * @param $fileName
     * @return array
     * 第二种模版读取
     */
    private function _dealExcelStencilTwo($excel,$fileName) {
        //读取第一张表
        $sheet = $excel->getSheet(0);
        //获取总行数
        $row_num  = $sheet->getHighestRow();
        //获取供应商名称
        $check    = $sheet->getCell("G2")->getValue();
        $model    = new Association();
        $supplier = $model->query()->where('goods',$check)->get()->toArray();
        $supplier = $supplier[0]['supplier'];
        //生成文件Id
        $fileId  = $this->_createFileId();
        $import_data = []; //数组形式获取表格数据
        for ($i = 2; $i <= $row_num; $i++) {
            $import_data[$i]['file_id']           = $fileId;
            $import_data[$i]['file_name']         = $fileName;
            $import_data[$i]['order_number']      = $sheet->getCell("C" . $i)->getValue();
            $import_data[$i]['count']             = 1;
            $import_data[$i]['solitaire_number']  = $sheet->getCell("I" . $i)->getValue();
            $import_data[$i]['receiver']          = $sheet->getCell("D" . $i)->getValue();
            $import_data[$i]['phone']             = $sheet->getCell("E" . $i)->getValue();
            $import_data[$i]['goods']             = $sheet->getCell("G" . $i)->getValue();
            $import_data[$i]['province']          = $sheet->getCell("K" . $i)->getValue();
            $import_data[$i]['city']              = $sheet->getCell("L" . $i)->getValue();
            $import_data[$i]['area']              = $sheet->getCell("M" . $i)->getValue();
            $import_data[$i]['address']           = $sheet->getCell("F" . $i)->getValue();
            $import_data[$i]['created_at']        = date('Y-m-d H:i:s');
            $import_data[$i]['updated_at']        = date('Y-m-d H:i:s');
        }

        $result = $this->_cacheFile($fileName, $import_data, $supplier);
        if ($result) {
            return $import_data;
        } else {
            return false;
        }
    }

    /**
     * @param $excel
     * @return array
     * 第一种模版读取
     */
    private function _dealExcelStencilOne($excel,$fileName){
        //读取第一张表
        $sheet = $excel->getSheet(0);
        //获取总行数
        $rowColumn = $sheet->getHighestRow();
        //获取供应商名称
        $check    = $sheet->getCell("G2")->getValue();
        $model    = new Association();
        $supplier = $model->query()->where('goods',$check)->get()->toArray();
        $supplier = $supplier[0]['supplier'];
        //生成文件Id
        $fileId  = $this->_createFileId();
        $import_data = []; //数组形式获取表格数据
        for ($i = 2; $i <= $rowColumn; $i++) {
            $import_data[$i]['file_id']           = $fileId;
            $import_data[$i]['file_name']         = $fileName;
            $import_data[$i]['order_number']      = $sheet->getCell("C" . $i)->getValue();
            $import_data[$i]['solitaire_number']  = $sheet->getCell("D" . $i)->getValue();
            $import_data[$i]['receiver']          = $sheet->getCell("E" . $i)->getValue();
            $import_data[$i]['phone']             = $sheet->getCell("F" . $i)->getValue();
            $import_data[$i]['goods']             = $sheet->getCell("G" . $i)->getValue();
            $import_data[$i]['count']             = $sheet->getCell("I" . $i)->getValue();
            $import_data[$i]['province']          = $sheet->getCell("J" . $i)->getValue();
            $import_data[$i]['city']              = $sheet->getCell("K" . $i)->getValue();
            $import_data[$i]['area']              = $sheet->getCell("L" . $i)->getValue();
            $import_data[$i]['address']           = $sheet->getCell("M" . $i)->getValue();
            $import_data[$i]['remarks']           = $sheet->getCell("N" . $i)->getValue();
            $import_data[$i]['created_at']        = date('Y-m-d H:i:s');
            $import_data[$i]['updated_at']        = date('Y-m-d H:i:s');
        }

        $result = $this->_cacheFile($fileName, $import_data, $supplier);
        if ($result) {
            return $import_data;
        } else {
            return false;
        }
    }

    /**
     * @return bool|mixed|string
     * 生成文件id
     */
    private function _createFileId(){
        //redis添加文件id
        try {
            $redis = new Redis();
            $redis->connect("order_redis", 6379);
            if (!$redis->get('fileId')){
                $redis->set('fileId' , 1);
            }else{
                $redis->incr('fileId');
            }
            $fileId = $redis->get('fileId');
            return $fileId;
        } catch (Exception $e) {
            return false;
            die();
        }
    }

    /**
     * redis添加缓存
     * @param $fileName
     * @param $import_data
     * @param $supplier
     * @return bool
     */
    private function _cacheFile($fileName,$import_data,$supplier) {
        try {
            $redis = new Redis();
            $redis->connect("order_redis", 6379);
            if (empty($fileName) || empty($import_data)){
                return false;
            }
            $count       = count($import_data);
            $import_data = json_encode($import_data);
            $redis->hSet($supplier, $fileName, $import_data);
            $redis->hSet('supplier',$supplier, 1);
            return $count;
        } catch (Exception $e) {
            return false;
            die();
        }
    }
}
