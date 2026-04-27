<?php

use Illuminate\Http\Request;

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

Route::group(['prefix' => 'v1'], function() {
    // API สำหรับดึงข้อมูล IoC เป็นไฟล์ CSV
    Route::get('/ioc-feed/{category}.csv', 'IocFeedController@exportCsv');
});

Route::middleware('auth:api')->get('/iocfeed', function (Request $request) {
    return $request->user();
});