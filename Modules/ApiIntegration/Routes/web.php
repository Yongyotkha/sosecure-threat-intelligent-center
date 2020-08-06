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
    ['middleware' => 'web', 'prefix' => 'apiintegration'],
    function () {
        Route::get('/', 'ApiIntegrationController@index')->name('apiintegration.index')->middleware('can:menu_items');
        Route::get('create', 'ApiIntegrationController@create')->name('apiintegration.create')->middleware('can:apiintegration_create');
    }
);
