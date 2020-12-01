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
    ['middleware' => 'web', 'prefix' => 'indicators'],
    function () {
        Route::get('/', 'IndicatorsController@index')->name('indicators.index')->middleware('can:menu_items');
        Route::get('/details', 'IndicatorsController@show_detail_indicators')->name('indicators.detail_indicators')->middleware('can:menu_items');
        Route::get('/', 'IndicatorsController@index')->name('indicators.index')->middleware('can:menu_items');
    }
);