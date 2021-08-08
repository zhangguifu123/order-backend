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
        Route::get('/order/export','Work\DealExcelController@exportCacheExcel');
        Route::post('/file/export',  'Work\DealExcelController@exportBuyerExcel');
        Route::post('/order/add',  'Work\DealExcelController@dealExcel')->middleware('excel.check');
        Route::post('/order/update',  'Work\DealExcelController@updateOrder');

        Route::get('/order/check/all','Work\CheckExcelController@checkAllExcel');
        Route::post('/order/check','Work\CheckExcelController@checkExcel');
        Route::post('/order/check/reback','Work\CheckExcelController@checkExcelReback');
        Route::post('/order/del',  'Work\CheckExcelController@delExcel');

        Route::get('/work/{page}','Work\WeChatController@selectWork');
        Route::post('/reback','Work\DealExcelController@updateOrder');
        Route::post('/association/add','Work\AssociationController@createAssociation');
        Route::get('/association','Work\AssociationController@getAssociation');

    });

});
