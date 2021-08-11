<?php

namespace App\Http\Controllers\Api\Work;

use App\Http\Controllers\Controller;
use App\Model\Association;
use App\Services\ExcelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PHPExcel_IOFactory;
class AssociationController extends Controller
{
    //
    public function getAssociation(Request $request) {
        $model  = new Association();
        $result = $model::query()->get()->toArray();
        return msg(0, $result);
    }

    public function createAssociation(Request $request){
        //读取表
        $excelService = new ExcelService();
        //上传excel文件
        $file = $request->file('file');
        $excel = $excelService->readExcel($file);
        if ($excel === 1) {
            return msg(1, '数据解析失败');
        }
        //读取第一张表
        $sheet  = $excel->getSheet(0);
        $check  = $sheet->getCell("A1")->getValue();
        if ($check == '供应商群名称') {
            $highestRow = $sheet->getHighestRow(); //行数
            //获取上传到后台的文件名
            $import_data = []; //数组形式获取表格数据
            for ($i = 2; $i <= $highestRow; $i++) {
                //一个供应商最多八列
                for ($j = 'B'; $j != 'H';$j++) {
                    if ($sheet->getCell($j . $i)->getValue() == null) {
                        continue;
                    }
                    $import_data[$j.$i]['supplier']    = $sheet->getCell('A' . $i)->getValue();
                    $import_data[$j.$i]['goods']       = $sheet->getCell($j . $i)->getValue();
                    $import_data[$j.$i]['stencil']     = $sheet->getCell('I' . $i)->getValue();
                    $import_data[$j.$i]['wx_id']       = $sheet->getCell('J' . $i)->getValue();
                    $import_data[$j.$i]['created_at']  = date('Y-m-d H:i:s');
                    $import_data[$j.$i]['updated_at']  = date('Y-m-d H:i:s');
                }

            }
            DB::table('associations')->truncate();
            $result = DB::table('associations')->insert($import_data);
            if ($result){
                return msg(0, $import_data);
            }
        }
        return msg(7, __LINE__);
    }
}
