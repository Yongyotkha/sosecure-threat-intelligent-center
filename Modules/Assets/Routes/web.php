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
        Route::get('all', 'AssetsController@index_all_asset')->name('assets.index_setting')->middleware('can:menu_items');
        Route::get('/', 'AssetsController@index')->name('assets.index')->middleware('can:menu_items');
        Route::post('table_asset', 'AssetsController@table_asset')->name('assets.table_asset')->middleware('can:menu_items');
        // Modal
        Route::get('assets_redirect_add', 'AssetsController@assets_redirect_add')->name('assets.assets_redirect_add_modal');
        Route::get('assets_add_cpe/{id}', 'AssetsController@assets_add_cpe')->name('assets.assets_add_cpe');
        // Route::get('assets_add_cpe', 'AssetsController@assets_add_cpe')->name('assets.assets_add_cpe');
        Route::get('selectData', 'AssetsController@get_selected_filter')->name('assets.get_selected_filter');
        Route::post('assets_add_user', 'AssetsController@web_server_add_user')->name('assets.assets_add_user');
        Route::post('assets_add_data', 'AssetsController@assets_add_data')->name('assets.assets_add_data');
    }
);