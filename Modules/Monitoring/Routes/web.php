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
    ['middleware' => ['web', 'permission:role_center', 'permission:monitoring'], 'prefix' => 'monitoring'],
    function () {
        Route::get('/', 'MonitoringController@index')->name('monitoring.index')->middleware('can:menu_items');
        Route::get('/dashboard', 'MonitoringController@dashboard')->name('monitoring.dashboard')->middleware('can:menu_items');
        Route::get('/batchjob', 'MonitoringController@batchjob')->name('monitoring.batchjob')->middleware('can:menu_items');
        Route::post('/tableMonitor', 'MonitoringController@tableMonitor')->name('monitoring.tableMonitor')->middleware('can:menu_items');

        Route::get('/view_message/{id}', 'MonitoringController@view_message_modal')->name('monitoring.view_message_modal')->middleware('can:menu_items');

        Route::get('/monitor_logs', 'MonitoringController@monitor_logs')->name('monitoring.monitor_logs')->middleware('can:menu_items');
        Route::post('/table_monitor_logs', 'MonitoringController@table_monitor_logs')->name('monitoring.table_monitor_logs')->middleware('can:menu_items');
        Route::post('/delete_logs', 'MonitoringController@delete_logs')->name('monitoring.delete_logs')->middleware('can:menu_items');
        Route::get('/send_logs', 'MonitoringController@send_logs')->name('monitoring.send_logs')->middleware('can:menu_items');
        Route::post('/table_send_logs', 'MonitoringController@table_send_logs')->name('monitoring.table_send_logs')->middleware('can:menu_items');
        Route::post('/delete_send_logs', 'MonitoringController@delete_send_logs')->name('monitoring.delete_send_logs')->middleware('can:menu_items');
        Route::post('/load_card', 'MonitoringController@load_card')->name('monitoring.load_card')->middleware('can:menu_items');
        Route::post('/load_category', 'MonitoringController@load_category')->name('monitoring.load_category')->middleware('can:menu_items');
        Route::post('/load_status', 'MonitoringController@load_status')->name('monitoring.load_status')->middleware('can:menu_items');

        Route::post('social_setting_table', 'MonitoringController@tableSocailMonitoring')->name('socialfeedsettings.rss_setting_table')->middleware('can:menu_items');
        Route::get('/social_feel', 'MonitoringController@social_feel')->name('monitoring.social_feel')->middleware('can:menu_items');
        Route::post('social_feel_table', 'MonitoringController@tablesocial_feel')->name('monitoring.social_feel_table')->middleware('can:menu_items');


        Route::get('/darkweb', 'MonitoringController@monitor_darkweb')->name('monitoring.monitor_darkweb')->middleware('can:menu_items');
        Route::post('/search_darkweb', 'MonitoringController@monitor_search_darkweb')->name('monitoring.monitor_search_darkweb')->middleware('can:menu_items');

        Route::post('/rss_feel_table_data', 'MonitoringController@tablerss_feel_data')->name('monitoring.rss_feel_table_data')->middleware('can:menu_items');
        Route::post('/social_feel_table_data', 'MonitoringController@tablesocial_feel_data')->name('monitoring.social_feel_table_data')->middleware('can:menu_items');
    }
);
Route::group(
    ['middleware' => ['web', 'permission:role_center', 'permission:monitoring'], 'prefix' => 'tools'],
    function () {
 
        Route::get('/darkweb', 'MonitoringController@monitor_darkweb')->name('monitoring.monitor_darkweb')->middleware('can:menu_items');
      

    }
);