<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use \PHPExcel;
use \PHPExcel_IOFactory;
class Order extends Model
{
    //
    protected $guarded = ['id','created_at','updated_at'];


    public function chooseExcelExport($export_data, $stencil, $supplier) {

        switch ($stencil)
        {
            case 1:
                return $this->_supplierExcelExport1($export_data, $supplier);
                break;
            case 2:
                return $this->_supplierExcelExport2($export_data, $supplier);
                break;
            case 3:
                return $this->_supplierExcelExport3($export_data, $supplier);
                break;
            case 4:
                return $this->_supplierExcelExport4($export_data, $supplier);
                break;
            case 5:
                return $this->_supplierExcelExport5($export_data, $supplier);
                break;
            case 6:
                return $this->_supplierExcelExport6($export_data, $supplier);
                break;
            default:
                return msg(3,'文件模版出错！');
        }
    }

    private function _savePath($objWriter) {

    }

    /**
     * lisa5-14
     * @param $explame_data
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    private function _supplierExcelExport1($explame_data, $supplier) {
        $objExcel = new PHPExcel();
        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
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
        $row_num  = count($explame_data);
        for ($i = 0; $i < $row_num; $i++) {
            $j = $i + $baseRow;
            $objExcel->getActiveSheet()->setCellValue('B' . $j, $explame_data[$i]['order_number']);
            $objExcel->getActiveSheet()->setCellValue('C' . $j, $explame_data[$i]['receiver']);
            $objExcel->getActiveSheet()->setCellValue('D' . $j, $explame_data[$i]['phone']);
            $objExcel->getActiveSheet()->setCellValue('E' . $j, $explame_data[$i]['address']);
            $objExcel->getActiveSheet()->setCellValue('F' . $j, $explame_data[$i]['goods']);
            $objExcel->getActiveSheet()->setCellValue('G' . $j, $explame_data[$i]['count']);
        }

        $objExcel->setActiveSheetIndex(0);
        //4、输出
        $objExcel->setActiveSheetIndex();
        header('Content-Type: applicationnd.ms-excel');
        $time = date('Y-m-d');
        header("Content-Disposition: attachment;filename=$supplier" . $time . ".xls");
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }

    /**
     * 歌帝梵 德氏
     * @param $explame_data
     * @param $supplier
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    private function _supplierExcelExport2($explame_data, $supplier) {
        $objExcel = new PHPExcel();
        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
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
        $row_num  = count($explame_data);
        for ($i = 0; $i < $row_num; $i++) {
            $j = $i + $baseRow;
            $objExcel->getActiveSheet()->setCellValue('A' . $j, $explame_data[$i]['order_number']);
            $objExcel->getActiveSheet()->setCellValue('B' . $j, $explame_data[$i]['receiver']);
            $objExcel->getActiveSheet()->setCellValue('C' . $j, $explame_data[$i]['phone']);
            $objExcel->getActiveSheet()->setCellValue('D' . $j, $explame_data[$i]['address']);
            $objExcel->getActiveSheet()->setCellValue('E' . $j, $explame_data[$i]['goods']);
            $objExcel->getActiveSheet()->setCellValue('F' . $j, $explame_data[$i]['count']);
            $objExcel->getActiveSheet()->setCellValue('G' . $j, $explame_data[$i]['count']);
        }

        $objExcel->setActiveSheetIndex(0);
        //4、输出
        $objExcel->setActiveSheetIndex();
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
     * 尊乐模版
     * @param $explame_data
     * @param $supplier
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    private function _supplierExcelExport3($explame_data, $supplier) {
        $objExcel = new PHPExcel();
        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
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
        $model        = new Order();
        $row_num  = count($explame_data);
        for ($i = 0; $i < $row_num; $i++) {
            $j = $i + $baseRow;
            $objExcel->getActiveSheet()->setCellValue('A' . $j, $explame_data[$i]['order_number']);
            $objExcel->getActiveSheet()->setCellValue('B' . $j, $explame_data[$i]['receiver']);
            $objExcel->getActiveSheet()->setCellValue('C' . $j, $explame_data[$i]['phone']);
            $objExcel->getActiveSheet()->setCellValue('D' . $j, $explame_data[$i]['address']);
            $objExcel->getActiveSheet()->setCellValue('E' . $j, $explame_data[$i]['count']);
            $objExcel->getActiveSheet()->setCellValue('F' . $j, $explame_data[$i]['remarks']);
            $objExcel->getActiveSheet()->setCellValue('G' . $j, $explame_data[$i]['goods']);
        }

        $objExcel->setActiveSheetIndex(0);
        //4、输出
        $objExcel->setActiveSheetIndex();
        header('Content-Type: applicationnd.ms-excel');
        $time = date('Y-m-d');
        header("Content-Disposition: attachment;filename=$supplier" . $time . ".xls");
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }

    /**
     * erp订单导入模版
     * @param $explame_data
     * @param $supplier
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    private function _supplierExcelExport4($explame_data, $supplier) {
        $objExcel = new PHPExcel();
        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
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
        $model        = new Order();
        $row_num  = count($explame_data);
        for ($i = 0; $i < $row_num; $i++) {
            $j = $i + $baseRow;
            $objExcel->getActiveSheet()->setCellValue('B' . $j, $explame_data[$i]['order_number']);
            $objExcel->getActiveSheet()->setCellValue('O' . $j, $explame_data[$i]['receiver']);
            $objExcel->getActiveSheet()->setCellValue('Q' . $j, $explame_data[$i]['phone']);
            $objExcel->getActiveSheet()->setCellValue('R' . $j, $explame_data[$i]['address']);
            $objExcel->getActiveSheet()->setCellValue('E' . $j, $explame_data[$i]['goods']);
            $objExcel->getActiveSheet()->setCellValue('J' . $j, $explame_data[$i]['count']);
        }

        $objExcel->setActiveSheetIndex(0);
        //4、输出
        $objExcel->setActiveSheetIndex();
        header('Content-Type: applicationnd.ms-excel');
        $time = date('Y-m-d');
        header("Content-Disposition: attachment;filename=$supplier" . $time . ".xls");
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }

    /**
     * 钟薛高导入模版
     * @param $explame_data
     * @param $supplier
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    private function _supplierExcelExport5($explame_data, $supplier) {
        $objExcel = new PHPExcel();
        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
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
        //默认数据
        $model        = new Order();
        $row_num  = count($explame_data);
        for ($i = 0; $i < $row_num; $i++) {
            $j = $i + $baseRow;
            $objExcel->getActiveSheet()->setCellValue('A' . $j, $explame_data[$i]['order_number']);
            $objExcel->getActiveSheet()->setCellValue('B' . $j, $explame_data[$i]['receiver']);
            $objExcel->getActiveSheet()->setCellValue('C' . $j, $explame_data[$i]['receiver']);
            $objExcel->getActiveSheet()->setCellValue('D' . $j, $explame_data[$i]['phone']);
            $objExcel->getActiveSheet()->setCellValue('E' . $j, $explame_data[$i]['province']);
            $objExcel->getActiveSheet()->setCellValue('F' . $j, $explame_data[$i]['city']);
            $objExcel->getActiveSheet()->setCellValue('G' . $j, $explame_data[$i]['area']);
            $objExcel->getActiveSheet()->setCellValue('H' . $j, $explame_data[$i]['address']);
            $objExcel->getActiveSheet()->setCellValue('J' . $j, $explame_data[$i]['goods']);
            $objExcel->getActiveSheet()->setCellValue('L' . $j, $explame_data[$i]['count']);
        }

        $objExcel->setActiveSheetIndex(0);
        //4、输出
        $objExcel->setActiveSheetIndex();
        header('Content-Type: applicationnd.ms-excel');
        $time = date('Y-m-d');
        header("Content-Disposition: attachment;filename=$supplier" . $time . ".xls");
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }

    /**
     * 逛家街模版
     * @param $explame_data
     * @param $supplier
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     */
    private function _supplierExcelExport6($explame_data, $supplier) {
        $objExcel = new PHPExcel();
        $objWriter = PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');
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
        $model        = new Order();
        $row_num  = count($explame_data);
        for ($i = 0; $i < $row_num; $i++) {
            $j = $i + $baseRow;
            $objExcel->getActiveSheet()->setCellValue('A' . $j, $explame_data[$i]['order_number']);
            $objExcel->getActiveSheet()->setCellValue('C' . $j, $explame_data[$i]['receiver']);
            $objExcel->getActiveSheet()->setCellValue('D' . $j, $explame_data[$i]['phone']);

            $objExcel->getActiveSheet()->setCellValue('H' . $j, $explame_data[$i]['address']);
            $objExcel->getActiveSheet()->setCellValue('E' . $j, $explame_data[$i]['count']);
            $objExcel->getActiveSheet()->setCellValue('F' . $j, $explame_data[$i]['remarks']);
            $objExcel->getActiveSheet()->setCellValue('G' . $j, $explame_data[$i]['goods']);
        }

        $objExcel->setActiveSheetIndex(0);
        //4、输出
        $objExcel->setActiveSheetIndex();
        header('Content-Type: applicationnd.ms-excel');
        $time = date('Y-m-d');
        header("Content-Disposition: attachment;filename=$supplier" . $time . ".xls");
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
    }
}
