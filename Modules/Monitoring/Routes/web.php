<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group(
    ['middleware' => 'web', 'prefix' => 'monitoring'],
    function () {
        Route::get('/', 'MonitoringController@index')->name('monitoring.index')->middleware('can:menu_items');
        Route::get('/batchjob', 'MonitoringController@batchjob')->name('monitoring.batchjob')->middleware('can:menu_items');
        Route::post('/tableMonitor', 'MonitoringController@tableMonitor')->name('monitoring.tableMonitor')->middleware('can:menu_items');
    }
);