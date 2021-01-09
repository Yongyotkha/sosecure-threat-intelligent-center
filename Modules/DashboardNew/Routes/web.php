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
    ['middleware' => ['web', 'installed'], 'prefix' => 'dashboardnew'], function () {
        Route::get('/', 'DashboardNewController@index')->name('dashboardnew.index')->middleware('can:menu_items');
        Route::post('load_chart', 'DashboardNewController@load_chart')->name('dashboardnew.load_chart')->middleware('can:menu_items');
        Route::post('count_asset', 'DashboardNewController@count_asset')->name('dashboardnew.count_asset')->middleware('can:menu_items');
        Route::post('count_vulnerability', 'DashboardNewController@count_vulnerability')->name('dashboardnew.count_vulnerability')->middleware('can:menu_items');
        Route::post('count_compromised', 'DashboardNewController@count_compromised')->name('dashboardnew.count_compromised')->middleware('can:menu_items');
        Route::post('count_data_leak', 'DashboardNewController@count_data_leak')->name('dashboardnew.count_data_leak')->middleware('can:menu_items');
        Route::post('count_vulnerability_host', 'DashboardNewController@count_vulnerability_host')->name('dashboardnew.count_vulnerability_host')->middleware('can:menu_items');
        Route::post('chart_indicators', 'DashboardNewController@chart_indicators')->name('dashboardnew.chart_indicators')->middleware('can:menu_items');
        Route::post('cve_assets', 'DashboardNewController@cve_assets')->name('dashboardnew.cve_assets')->middleware('can:menu_items');
    }
);

Route::get('/view_detail_asset', 'DashboardNewController@detail_asset')->name('detail_asset.index')->middleware('can:menu_items');
