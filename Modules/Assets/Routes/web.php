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
    ['middleware' => 'web', 'prefix' => 'assets'],
    function () {
        Route::get('/', 'AssetsController@index')->name('assets.index')->middleware('can:menu_items');
        Route::post('table_asset', 'AssetsController@table_asset')->name('assets.table_asset')->middleware('can:menu_items');
        // Modal
        Route::get('assets_add_cpe', 'AssetsController@assets_add_cpe')->name('assets.assets_add_cpe');
    }
);