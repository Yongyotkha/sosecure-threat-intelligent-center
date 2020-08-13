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
    ['middleware' => 'web', 'prefix' => 'alert'],
    function () {
        Route::get('/', 'AlertController@index')->name('alert.index')->middleware('can:menu_items');
        Route::get('/alert_details', 'AlertController@alert_details')->name('alert.alert_details')->middleware('can:menu_items');
    }
);
