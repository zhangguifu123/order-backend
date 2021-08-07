<?php

namespace App\Services;

use Illuminate\Http\Request;
use PHPExcel_IOFactory;
class ExcelService
{
    public function readExcel(Request $request) {
        date_default_timezone_set('Asia/Shanghai');
        header("content-type:text/html;charset=utf-8");
        //上传excel文件
        $file = $request->file('file');
        //获取上传到后台的文件名
        $fileName = $file->getClientOriginalName();
        //将文件保存到storage目录下面
        $info = $file->move(storage_path('app/public/buyer/'),$fileName);
        if ($info) {
            //获取文件路径
            $filePath = config("app.url")."/storage/buyer/$fileName";
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
        $result = [
            'file'  => $file,
            'excel' => $excel
        ];
        return $result;
    }
}
