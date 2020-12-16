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
        Route::get('/detail', 'IndicatorsController@show_detail_indicator')->name('indicators.detail_indicator')->middleware('can:menu_items');
        Route::get('/events', 'IndicatorsController@events')->name('indicators.events')->middleware('can:menu_items');
        Route::get('/events/events_detail', 'IndicatorsController@events_detail')->name('indicators.events_detail')->middleware('can:menu_items');
        // Route::get('/events/events_detail', 'IndicatorsController@events_detail')->name('indicators.events_detail')->middleware('can:menu_items');

        Route::get('/attributes', 'IndicatorsController@attributes')->name('indicators.attributes')->middleware('can:menu_items');
        // Route::get('/details', 'IndicatorsController@show_detail_indicators')->name('indicators.detail_indicators')->middleware('can:menu_items');
        Route::get('/LoadMoreOTX', 'IndicatorsController@LoadMoreOTX')->name('LoadMoreOTX');
       
        Route::get('/load/general', 'IndicatorsController@load_general')->name('indicators.load_general');
        Route::post('/load/url_list', 'IndicatorsController@load_url_list')->name('indicators.load_url_list');
        Route::post('events_table', 'IndicatorsController@tableEvents')->name('indicators.events_table')->middleware('can:menu_items');
    }
);