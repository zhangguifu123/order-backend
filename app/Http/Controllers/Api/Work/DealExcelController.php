<?php

namespace App\Http\Controllers\Api\Work;

use App\Http\Controllers\Controller;

use App\Model\Association;
use App\Model\Order;
use App\Model\Work;
use App\Services\ExcelService;
use App\Services\HttpService;
use App\Services\OrdersService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PHPExcel;
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
     * @param Request $request
     * @return string
     * @throws \PHPExcel_Reader_Exception
     */
    public function updateOrder(Request $request) {
        $supplier = $request->input('supplier');
        if (empty($supplier) || !isset($supplier)) {
            return msg(1, __LINE__);
        }
        $excelService = new ExcelService();
        //上传excel文件
        $file = $request->file('file');
        $excel = $excelService->readExcel($file);
        if ($excel === 1) {
            return msg(1, '数据解析失败');
        }

        //读取第一张表
        $sheet  = $excel->getSheet(0);
        $check  = $sheet->getCell("D1")->getValue();
        if ($check !== '商品名称') {
            return msg(7, __LINE__);
        }
        return $excelService->dealRebackExcel($excel,$supplier);
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
            $redis           = new Redis();
            $http_model      = new HttpService();
            $redis->connect("order_redis", 6379);
            $suppliers = $redis->hKeys('supplier');
            if (empty($suppliers)){
                return msg(1,'无数据');
            }
            //获取 供应商
            foreach ($suppliers as $supplier) {
                $export_data = [];
                $files  = $redis->hGetAll($supplier);
                //创建workID
                $wordId = $this->_createWorkId();
                $work   = ['work_id' => $wordId];
                //获取订单数量
                $count  = 0;
                //获取文件数据
                foreach ($files as $fileName => $import_data){
                    $import_data = json_decode($import_data,true);
                    $count += count($import_data);
                    //将workID插入
                    array_walk($import_data, function (&$value, $key, $work) {
                        $value = array_merge($value, $work);
                    }, $work);
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
                if (empty($export_data)){
                    return msg(5,__LINE__);
                }
                $stencil      = $stencil_model::query()->where('supplier',$supplier)->where('goods',$export_data[0]['goods'])->get(['stencil','wx_id'])->toArray();
                //导出模版
                $excelService = new ExcelService();
                $url          = $excelService->chooseExcelExport($export_data, $stencil[0]['stencil'], $supplier);
                $wxId         = $stencil[0]['stencil'];
                $files        = array_keys($files);
                $newFiles     = [];
                foreach ($files as $file) {
                    $str = $file;
                    preg_match('/\d+/', $str, $matches);
                    $newFiles[] = $matches[0];
                }
                //创建推送任务
                $data   = [
                    'work_id'      => $wordId,
                    'wx_id'        => $wxId,
                    'supplier'     => $supplier,
                    'files'        => json_encode($newFiles),
                    'export_url'   => $url,
                    'order_count'  => $count,
                    'status'       => 0,
                    'reback_count' => 0,
                ];
                $pushUrl          = '47.94.130.183:8085/sendTextMsg';
                $pushToWeChatData = ['wx_id' => $wxId, 'content' => '今日订单：'.$url, ];
                $http_model->pushWeChat($pushUrl, json_encode($pushToWeChatData));
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
        //上传excel文件
        $excelService = new ExcelService();
        $file = $request->file('file');
        $excel = $excelService->readExcel($file);
        if ($excel === 1) {
            return msg(1, '数据解析失败');
        }

        //读取第一张表
        $sheet  = $excel->getSheet(0);
        $check  = $sheet->getCell("G1")->getValue();
        //获取上传到后台的文件名
        $fileName = $file->getClientOriginalName();
        switch ($check)
        {
            case '商品名称':
                $return_data = $this->_dealExcelStencil1($excel, $fileName);
                break;
            case '商品信息':
                $return_data = $this->_dealExcelStencil2($excel, $fileName);
                break;
            case '收货地址':
                $return_data = $this->_dealExcelStencil3($excel, $fileName);
                break;
            default:
                return msg(3,'文件模版出错！');
        }

        if (empty($return_data)) {
            return msg(1, '数据解析失败');
        }
        $goods      = array_column($return_data['import_data'],'goods');
        $orderCount = count($return_data['import_data']);
        $supplier   = $this->model->getSupplier($goods[0]);
        $result     = [
          'supplier'   => $supplier,
          'fileName'   => $fileName,
          'fileId'     => $return_data['fileId'],
          'orderCount' => $orderCount,
        ];
        return msg(0, $result);
    }


    public function exportBuyerExcel (Request $request) {
        $workIds = $request->input('workIds');
        if (empty($workIds)) {
            return msg(1, __LINE__);
        }
        $excelService = new ExcelService();
        $work_model   = new Work();
        $order_model  = new Order();
        $objExcel     = new PHPExcel();
        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
        foreach ($workIds as $workId) {
            $fileIds = $excelService->getFileidsByWork($workId, $work_model);
	    $fileIds = json_decode($fileIds['files']);
            $excelService->chooseOrderExcelExport($fileIds, $objExcel, $objWriter, $order_model);
        }
        return msg(0, __LINE__);
    }

    private function _dealExcelStencil3($excel,$fileName) {
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
            $import_data[$i]['file_stencil_id']   = 3;
            $import_data[$i]['created_at']        = date('Y-m-d H:i:s');
            $import_data[$i]['updated_at']        = date('Y-m-d H:i:s');
        }
        $result = $this->_cacheFile($fileId, $fileName, $import_data, $supplier);
        Log::notice('fileId:'.$fileId.'fileName'.$fileName);
        if ($result) {
            $return_result = [
                'import_data' => $import_data,
                'fileId'      => $fileId,
            ];
            return $return_result;
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
    private function _dealExcelStencil2($excel,$fileName) {
        //读取第一张表
        $sheet = $excel->getSheet(0);
        //获取总行数
        $row_num  = $sheet->getHighestRow();
        //获取供应商名称
        $check    = $sheet->getCell("G2")->getValue();
        $supplier = $this->model->getSupplier($check);
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
            $import_data[$i]['file_stencil_id']   = 2;
            $import_data[$i]['created_at']        = date('Y-m-d H:i:s');
            $import_data[$i]['updated_at']        = date('Y-m-d H:i:s');
        }

        $result = $this->_cacheFile($fileId, $fileName, $import_data, $supplier);
        if ($result) {
            $return_result = [
                'import_data' => $import_data,
                'fileId'      => $fileId,
            ];
            return $return_result;
        } else {
            return false;
        }
    }

    /**
     * @param $excel
     * @param $fileName
     * @return array
     * 第一种模版读取
     */
    private function _dealExcelStencil1($excel,$fileName){
        //读取第一张表
        $sheet = $excel->getSheet(0);
        //获取总行数
        $rowColumn = $sheet->getHighestRow();
        //获取供应商名称
        $check    = $sheet->getCell("G2")->getValue();
        $supplier = $this->model->getSupplier($check);
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
            $import_data[$i]['file_stencil_id']   = 1;
            $import_data[$i]['created_at']        = date('Y-m-d H:i:s');
            $import_data[$i]['updated_at']        = date('Y-m-d H:i:s');
        }

        $result = $this->_cacheFile($fileId, $fileName, $import_data, $supplier);
        if ($result) {
            $return_result = [
                'import_data' => $import_data,
                'fileId'      => $fileId,
            ];
            return $return_result;
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
     * @return bool|mixed|string
     * 生成文件id
     */
    private function _createWorkId(){
        //redis添加文件id
        try {
            $redis = new Redis();
            $redis->connect("order_redis", 6379);
            if (!$redis->get('workId')){
                $redis->set('workId' , 1);
            }else{
                $redis->incr('workId');
            }
            $fileId = $redis->get('workId');
            return $fileId;
        } catch (Exception $e) {
            return false;
            die();
        }
    }

    /**
     * redis添加缓存
     * @param $fileId
     * @param $fileName
     * @param $import_data
     * @param $supplier
     * @return bool
     */
    private function _cacheFile($fileId, $fileName,$import_data,$supplier) {
        try {
            $redis = new Redis();
            $redis->connect("order_redis", 6379);
            if (empty($fileId) || empty($fileName) || empty($import_data)){
                return false;
            }
            $import_data = json_encode($import_data);
            $fileIdName = "[".$fileId."]".$fileName;
            $redis->hSet($supplier, $fileIdName, $import_data);
            $redis->hSet('supplier',$supplier, 1);
            return $fileName;
        } catch (Exception $e) {
            return false;
            die();
        }
    }
}
