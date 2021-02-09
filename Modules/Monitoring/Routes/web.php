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

        Route::get('/view_message/{id}', 'MonitoringController@view_message_modal')->name('monitoring.view_message_modal')->middleware('can:menu_items');

        Route::get('/monitor_logs', 'MonitoringController@monitor_logs')->name('monitoring.monitor_logs')->middleware('can:menu_items');
        Route::post('/table_monitor_logs', 'MonitoringController@table_monitor_logs')->name('monitoring.table_monitor_logs')->middleware('can:menu_items');
        Route::post('/delete_logs', 'MonitoringController@delete_logs')->name('monitoring.delete_logs')->middleware('can:menu_items');
    }
);