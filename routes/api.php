<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});
Route::namespace('Api')->group(function (){
    Route::post('/login','Manager\LoginController@check');
    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('/logout','Manager\LoginController@update');
        Route::get('/info','Manager\LoginController@info');

        Route::group(['middleware' => 'manager.check'], function () {
            //发布推送任务
            Route::get('/order/export','Work\DealExcelController@exportCacheExcel');
            //导出团长订单
            Route::post('/file/export',  'Work\DealExcelController@exportBuyerExcel');
            //添加团长订单
            Route::post('/order/add',  'Work\DealExcelController@dealExcel')->middleware('excel.check');
            //查看团长订单缓存列表
            Route::get('/order/check/all','Work\CheckExcelController@checkAllExcel');
            //检查缓存详细内容
            Route::post('/order/check','Work\CheckExcelController@checkExcel');
            //删除缓存订单
            Route::post('/order/del',  'Work\CheckExcelController@delExcel');
            //清空缓存订单
            Route::post('/order/del/all',  'Work\CheckExcelController@delAllExcel');
            //重新执行微信推送
            Route::get('/work/repeat','Work\WeChatController@repeatWork');
            //更新供货表
            Route::post('/association/add','Work\AssociationController@createAssociation');
            //获取供应商关系
            Route::get('/association','Work\AssociationController@getAssociation');
            //拉取推送任务列表
            Route::get('/work/{page}','Work\WeChatController@selectWork');
            //管理员检索
            Route::get('/work/search',  'Work\WeChatController@search');
        });

        //供应商回单
        Route::post('/order/update',  'Work\DealExcelController@updateOrder');
        Route::group(['middleware' => 'supplier.check'], function () {
            //供应商检索
            Route::get('/work/supplier/search',  'Work\WeChatController@supplierSearch');
            //拉取推送任务列表
            Route::get('/work/supplier/{page}','Work\WeChatController@selectWork');
        });


    });

});
