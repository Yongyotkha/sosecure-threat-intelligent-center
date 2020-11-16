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
        Route::get('data', 'ScansController@tableData')->name('scans.data')->middleware('can:menu_items');
        Route::get('/scans-domain/{tab}', 'ScansController@scan_domain')->name('scans.index');
        Route::get('/scan_command', 'ScansController@scan_command');
    }
);
