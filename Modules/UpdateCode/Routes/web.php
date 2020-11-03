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
    ['middleware' => 'web', 'prefix' => 'updatecode'],
    function () {
        Route::get('/', 'UpdateCodeController@index')->name('updatecode.index')->middleware('can:menu_items');
        Route::get('/updatecode_version', 'UpdateCodeController@upcode_version')->name('updatecode.upcode_version')->middleware('can:menu_items');
        Route::get('/updatecode_site_version', 'UpdateCodeController@upcode_site_version')->name('updatecode.upcode_site_version')->middleware('can:menu_items');
    }
);