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
    ['middleware' => 'web', 'prefix' => 'scans'],
    function () {
        Route::get('/', 'ScansController@index')->name('scans.index')->middleware('can:menu_items');
        Route::post('data', 'ScansController@tableData')->name('scans.data')->middleware('can:menu_items');
        Route::get('/scans-domain/{tab}/{site_code}', 'ScansController@scan_domain')->name('scans.index');
        Route::get('/scan_command', 'ScansController@scan_command');
        Route::post('/get_referent', 'ScansController@get_referent');
        Route::post('/save_assets', 'ScansController@save_assets');
        Route::post('/save_assets_new', 'ScansController@save_assets_new');
        Route::get('/get_data_type', 'ScansController@get_data_type');
        Route::post('data_scans', 'ScansController@tableDataScans')->name('scans.data_scans')->middleware('can:menu_items');
        Route::post('data_scans_assets', 'ScansController@tableDataScanAssets')->name('scans.data_scans_assets')->middleware('can:menu_items');
        Route::get('scans_assets_delete/{id}/{code}', 'ScansController@scans_assets_delete')->name('scans_assets.delete');
        Route::delete('f_scans_assets_delete/{id}/{code}', 'ScansController@f_scans_assets_delete')->name('f_scans_assets.delete');
    }
);
