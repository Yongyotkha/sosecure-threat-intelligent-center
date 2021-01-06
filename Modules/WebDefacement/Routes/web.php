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
    ['middleware' => 'web', 'prefix' => 'webdefacement'],
    function () {
        Route::get('/', 'WebDefacementController@index')->name('webdefacement.index')->middleware('can:menu_items');
        Route::get('/detail', 'WebDefacementController@detail')->name('webdefacement.detail')->middleware('can:menu_items');
    }
);