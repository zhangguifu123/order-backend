<?php

namespace App\Services;

use \PHPExcel;
use App\Model\Order;
use Illuminate\Http\Request;
use PHPExcel_IOFactory;
class ExcelService
{
    /**
     * @param $excel
     * @return string
     */
    public function dealRebackExcel($excel) {
        //读取第一张表
        $sheet = $excel->getSheet(0);
        //获取总行数
        $rowColumn = $sheet->getHighestRow();
        $update_data = []; //数组形式获取表格数据
        for ($i = 2; $i <= $rowColumn; $i++) {
            $update_data[$i]['logistics']        = $sheet->getCell("A" . $i)->getValue();
            $update_data[$i]['logistics_number'] = $sheet->getCell("B" . $i)->getValue();
            $update_data[$i]['order_number']     = $sheet->getCell("C" . $i)->getValue();
        }
        $orderService = new OrdersService();
        $result = $orderService->updateMysqlOrder($update_data);
        if ($result) {
            return msg(0, $result);
        } else {
            return msg(7, __LINE__);
        }
    }

    /**
     * @param $file
     * @return array|int|string
     * @throws \PHPExcel_Reader_Exception
     */
    public function readExcel($file) {
        date_default_timezone_set('Asia/Shanghai');
        header("content-type:text/html;charset=utf-8");
        //获取上传到后台的文件名
        $fileName = $file->getClientOriginalName();
        //将文件保存到storage目录下面
        $info = $file->move(storage_path('app/public/buyer/'),$fileName);
        if ($info) {

            //获取文件后缀
            $suffix = $file->getClientOriginalExtension();
            //判断哪种类型
            if ($suffix == "xlsx") {
                $reader = PHPExcel_IOFactory::createReader('Excel2007');
            } else {
                $reader = PHPExcel_IOFactory::createReader('Excel5');
            }
        } else {
            return msg(4,'文件过大或格式不正确导致上传失败-_-!'.__LINE__);
        }
        //载入excel文件
        $excel  = $reader->load(storage_path('app/public/buyer/').$fileName, $encode = 'utf-8');
        if (!$excel) {
            return 1;
        }
        return $excel;
    }

    /**
     * @param $export_data
     * @param $stencil
     * @param $supplier
     * @return mixed
     * @throws \PHPExcel_Reader_Exception
     */
    public function chooseExcelExport($export_data, $stencil, $supplier) {
        $objExcel = new PHPExcel();
        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
        $function = '_supplierExcelExport'.$stencil;
        return $this->$function($export_data, $supplier, $objExcel, $objWriter);
    }

    /**
     * @param $supplier
     * @param $objWriter
     * @return string
     */
    private function _exportExcel($supplier, $objWriter){
        header('Content-Type: applicationnd.ms-excel');
        $time = date('Y-m-d');
        $fileName = $supplier . $time . ".xls";
        header("Content-Disposition: attachment;filename=$fileName");
        header('Cache-Control: max-age=0');
        $objWriter->save(storage_path('app/public/buyer/').$fileName);
        $url = config("app.url")."/storage/buyer/".$fileName;
        return $url;
    }

    /**
     * lisa5-14
     * @param $export_data
     * @param $supplier
     * @param $objExcel
     * @param $objWriter
     * @return string
     */
    private function _supplierExcelExport1($export_data, $supplier, $objExcel, $objWriter) {
        $objActSheet = $objExcel->getActiveSheet(0);
        $objActSheet->setTitle('lisa5-14'); //设置excel的标题
        $objActSheet->setCellValue('A1', '日期');
        $objActSheet->setCellValue('B1', '订单号');
        $objActSheet->setCellValue('C1', '收件人姓名');
        $objActSheet->setCellValue('D1', '收件电话');
        $objActSheet->setCellValue('E1', '收件地址');
        $objActSheet->setCellValue('F1', '商品名称');
        $objActSheet->setCellValue('G1', '发货数量');

        $baseRow = 2;
        //默认数据
        $row_num  = count($export_data);
        for ($i = 0; $i < $row_num; $i++) {
            $j = $i + $baseRow;
            $objExcel->getActiveSheet()->setCellValue('B' . $j, $export_data[$i]['order_number']);
            $objExcel->getActiveSheet()->setCellValue('C' . $j, $export_data[$i]['receiver']);
            $objExcel->getActiveSheet()->setCellValue('D' . $j, $export_data[$i]['phone']);
            $objExcel->getActiveSheet()->setCellValue('E' . $j, $export_data[$i]['address']);
            $objExcel->getActiveSheet()->setCellValue('F' . $j, $export_data[$i]['goods']);
            $objExcel->getActiveSheet()->setCellValue('G' . $j, $export_data[$i]['count']);
        }

        $objExcel->setActiveSheetIndex(0);
        //4、输出
        $objExcel->setActiveSheetIndex();

        return $this->_exportExcel($supplier, $objWriter);
    }

    /**
     * 歌帝梵 德氏
     * @param $export_data
     * @param $supplier
     * @return string
     */
    private function _supplierExcelExport2($export_data, $supplier, $objExcel, $objWriter) {
        $objActSheet = $objExcel->getActiveSheet(0);
        $objActSheet->setTitle('歌帝梵发货总表'); //设置excel的标题
        $objActSheet->setCellValue('A1', '订单号');
        $objActSheet->setCellValue('B1', '收件人');
        $objActSheet->setCellValue('C1', '联系电话');
        $objActSheet->setCellValue('D1', '详细地址');
        $objActSheet->setCellValue('E1', '商品');
        $objActSheet->setCellValue('F1', '数量');
        $objActSheet->setCellValue('G1', '备注');

        $baseRow = 2;
        //默认数据
        $row_num  = count($export_data);
        for ($i = 0; $i < $row_num; $i++) {
            $j = $i + $baseRow;
            $objExcel->getActiveSheet()->setCellValue('A' . $j, $export_data[$i]['order_number']);
            $objExcel->getActiveSheet()->setCellValue('B' . $j, $export_data[$i]['receiver']);
            $objExcel->getActiveSheet()->setCellValue('C' . $j, $export_data[$i]['phone']);
            $objExcel->getActiveSheet()->setCellValue('D' . $j, $export_data[$i]['address']);
            $objExcel->getActiveSheet()->setCellValue('E' . $j, $export_data[$i]['goods']);
            $objExcel->getActiveSheet()->setCellValue('F' . $j, $export_data[$i]['count']);
            $objExcel->getActiveSheet()->setCellValue('G' . $j, $export_data[$i]['count']);
        }

        $objExcel->setActiveSheetIndex(0);
        //4、输出
        $objExcel->setActiveSheetIndex();

        return $this->_exportExcel($supplier, $objWriter);
    }

    /**
     * 尊乐模版
     * @param $export_data
     * @param $supplier
     * @return string
     */
    private function _supplierExcelExport3($export_data, $supplier, $objExcel, $objWriter) {
        $objActSheet = $objExcel->getActiveSheet(0);
        $objActSheet->setTitle('尊乐发货总表'); //设置excel的标题
        $objActSheet->setCellValue('A1', '原始单号');
        $objActSheet->setCellValue('B1', '收件人');
        $objActSheet->setCellValue('C1', '手机');
        $objActSheet->setCellValue('D1', '地址');
        $objActSheet->setCellValue('E1', '数量');
        $objActSheet->setCellValue('F1', '备注');
        $objActSheet->setCellValue('G1', '商品名称');

        $baseRow = 2;
        //默认数据
        $row_num  = count($export_data);
        for ($i = 0; $i < $row_num; $i++) {
            $j = $i + $baseRow;
            $objExcel->getActiveSheet()->setCellValue('A' . $j, $export_data[$i]['order_number']);
            $objExcel->getActiveSheet()->setCellValue('B' . $j, $export_data[$i]['receiver']);
            $objExcel->getActiveSheet()->setCellValue('C' . $j, $export_data[$i]['phone']);
            $objExcel->getActiveSheet()->setCellValue('D' . $j, $export_data[$i]['address']);
            $objExcel->getActiveSheet()->setCellValue('E' . $j, $export_data[$i]['count']);
            $objExcel->getActiveSheet()->setCellValue('F' . $j, $export_data[$i]['remarks']);
            $objExcel->getActiveSheet()->setCellValue('G' . $j, $export_data[$i]['goods']);
        }

        $objExcel->setActiveSheetIndex(0);
        //4、输出
        $objExcel->setActiveSheetIndex();
        return $this->_exportExcel($supplier, $objWriter);
    }

    /**
     * erp订单导入模版
     * @param $export_data
     * @param $supplier
     * @param $objExcel
     * @param $objWriter
     * @return string
     */
    private function _supplierExcelExport4($export_data, $supplier, $objExcel, $objWriter) {
        $objActSheet = $objExcel->getActiveSheet(0);
        $objActSheet->setTitle('erp发货总表'); //设置excel的标题
        $objActSheet->setCellValue('A1', '店铺');
        $objActSheet->setCellValue('B1', '平台单号');
        $objActSheet->setCellValue('C1', '买家会员');
        $objActSheet->setCellValue('D1', '支付金额');
        $objActSheet->setCellValue('E1', '商品名称');
        $objActSheet->setCellValue('F1', '商品代码');
        $objActSheet->setCellValue('G1', '规格代码');
        $objActSheet->setCellValue('H1', '规格名称');
        $objActSheet->setCellValue('I1', '是否赠品');
        $objActSheet->setCellValue('J1', '数量');
        $objActSheet->setCellValue('K1', '价格');
        $objActSheet->setCellValue('L1', '商品备注');
        $objActSheet->setCellValue('M1', '运费');
        $objActSheet->setCellValue('N1', '买家留言');
        $objActSheet->setCellValue('O1', '收货人');
        $objActSheet->setCellValue('P1', '联系电话');
        $objActSheet->setCellValue('Q1', '联系手机');
        $objActSheet->setCellValue('R1', '收货地址');
        $objActSheet->setCellValue('S1', '省');
        $objActSheet->setCellValue('T1', '市');
        $objActSheet->setCellValue('U1', '区');
        $objActSheet->setCellValue('V1', '邮编');
        $objActSheet->setCellValue('W1', '订单创建时间');
        $objActSheet->setCellValue('X1', '订单付款时间');
        $objActSheet->setCellValue('Y1', '发货时间');
        $objActSheet->setCellValue('Z1', '物流单号');
        $objActSheet->setCellValue('AA1', '物流公司');
        $objActSheet->setCellValue('AB1', '卖家备注');
        $objActSheet->setCellValue('AC1', '发票种类');
        $objActSheet->setCellValue('AD1', '发票类型');
        $objActSheet->setCellValue('AE1', '发票抬头');
        $objActSheet->setCellValue('AF1', '纳税人识别号');
        $objActSheet->setCellValue('AG1', '开户行');
        $objActSheet->setCellValue('AH1', '账号');
        $objActSheet->setCellValue('AI1', '地址');
        $objActSheet->setCellValue('AJ1', '电话');
        $objActSheet->setCellValue('AK1', '是否手机订单');
        $objActSheet->setCellValue('AL1', '是否货到付款');
        $objActSheet->setCellValue('AM1', '支付方式');
        $objActSheet->setCellValue('AN1', '交易号');
        $objActSheet->setCellValue('AO1', '真实姓名');
        $objActSheet->setCellValue('AP1', '身份证号');
        $objActSheet->setCellValue('AQ1', '仓库名称');
        $objActSheet->setCellValue('AR1', '预计发货时间');
        $objActSheet->setCellValue('AS1', '预计送达时间');
        $objActSheet->setCellValue('AT1', '订单类型');
        $objActSheet->setCellValue('AU1', '是否分销');
        $objActSheet->setCellValue('AV1', '业务员');

        $baseRow = 2;
        //默认数据
        $row_num  = count($export_data);
        for ($i = 0; $i < $row_num; $i++) {
            $j = $i + $baseRow;
            $objExcel->getActiveSheet()->setCellValue('B' . $j, $export_data[$i]['order_number']);
            $objExcel->getActiveSheet()->setCellValue('O' . $j, $export_data[$i]['receiver']);
            $objExcel->getActiveSheet()->setCellValue('Q' . $j, $export_data[$i]['phone']);
            $objExcel->getActiveSheet()->setCellValue('R' . $j, $export_data[$i]['address']);
            $objExcel->getActiveSheet()->setCellValue('E' . $j, $export_data[$i]['goods']);
            $objExcel->getActiveSheet()->setCellValue('J' . $j, $export_data[$i]['count']);
        }

        $objExcel->setActiveSheetIndex(0);
        //4、输出
        $objExcel->setActiveSheetIndex();
        return $this->_exportExcel($supplier, $objWriter);
    }

    /**
     * 钟薛高导入模版
     * @param $export_data
     * @param $supplier
     * @param $objExcel
     * @param $objWriter
     * @return string
     */
    private function _supplierExcelExport5($export_data, $supplier, $objExcel, $objWriter) {
        $objActSheet = $objExcel->getActiveSheet(0);
        $objActSheet->setTitle('钟薛高发货总表'); //设置excel的标题
        $objActSheet->setCellValue('A1', '订单号');
        $objActSheet->setCellValue('B1', '收货人');
        $objActSheet->setCellValue('C1', '联系电话');
        $objActSheet->setCellValue('D1', '收货地址');
        $objActSheet->setCellValue('E1', '商品名称');
        $objActSheet->setCellValue('F1', '数量');
        $objActSheet->setCellValue('G1', '备注');

        $baseRow = 2;
        $row_num  = count($export_data);
        //默认数据
        for ($i = 0; $i < $row_num; $i++) {
            $j = $i + $baseRow;
            $objExcel->getActiveSheet()->setCellValue('A' . $j, $export_data[$i]['order_number']);
            $objExcel->getActiveSheet()->setCellValue('B' . $j, $export_data[$i]['receiver']);
            $objExcel->getActiveSheet()->setCellValue('C' . $j, $export_data[$i]['receiver']);
            $objExcel->getActiveSheet()->setCellValue('D' . $j, $export_data[$i]['phone']);
            $objExcel->getActiveSheet()->setCellValue('E' . $j, $export_data[$i]['province']);
            $objExcel->getActiveSheet()->setCellValue('F' . $j, $export_data[$i]['city']);
            $objExcel->getActiveSheet()->setCellValue('G' . $j, $export_data[$i]['area']);
            $objExcel->getActiveSheet()->setCellValue('H' . $j, $export_data[$i]['address']);
            $objExcel->getActiveSheet()->setCellValue('J' . $j, $export_data[$i]['goods']);
            $objExcel->getActiveSheet()->setCellValue('L' . $j, $export_data[$i]['count']);
        }

        $objExcel->setActiveSheetIndex(0);
        //4、输出
        $objExcel->setActiveSheetIndex();
        return $this->_exportExcel($supplier, $objWriter);
    }

    /**
     * 逛家街模版
     * @param $export_data
     * @param $supplier
     * @return string
     */
    private function _supplierExcelExport6($export_data, $supplier, $objExcel, $objWriter) {
        $objActSheet = $objExcel->getActiveSheet(0);
        $objActSheet->setTitle('逛家街发货总表'); //设置excel的标题
        $objActSheet->setCellValue('A1', '订单编号');
        $objActSheet->setCellValue('B1', '客户网名');
        $objActSheet->setCellValue('C1', '收货人');
        $objActSheet->setCellValue('D1', '电话');
        $objActSheet->setCellValue('E1', '州省');
        $objActSheet->setCellValue('F1', '区市');
        $objActSheet->setCellValue('G1', '区县');
        $objActSheet->setCellValue('H1', '地址');
        $objActSheet->setCellValue('I1', '编号');
        $objActSheet->setCellValue('J1', '品名');
        $objActSheet->setCellValue('K1', '条码');
        $objActSheet->setCellValue('L1', '数量');
        $objActSheet->setCellValue('M1', '合计');
        $objActSheet->setCellValue('N1', '订单来源');
        $objActSheet->setCellValue('O1', '店铺');

        $baseRow = 2;
        //默认数据
        $row_num  = count($export_data);
        for ($i = 0; $i < $row_num; $i++) {
            $j = $i + $baseRow;
            $objExcel->getActiveSheet()->setCellValue('A' . $j, $export_data[$i]['order_number']);
            $objExcel->getActiveSheet()->setCellValue('C' . $j, $export_data[$i]['receiver']);
            $objExcel->getActiveSheet()->setCellValue('D' . $j, $export_data[$i]['phone']);
            $objExcel->getActiveSheet()->setCellValue('H' . $j, $export_data[$i]['address']);
            $objExcel->getActiveSheet()->setCellValue('E' . $j, $export_data[$i]['count']);
            $objExcel->getActiveSheet()->setCellValue('F' . $j, $export_data[$i]['remarks']);
            $objExcel->getActiveSheet()->setCellValue('G' . $j, $export_data[$i]['goods']);
        }

        $objExcel->setActiveSheetIndex(0);
        //4、输出
        $objExcel->setActiveSheetIndex();
        return $this->_exportExcel($supplier, $objWriter);
    }
}
