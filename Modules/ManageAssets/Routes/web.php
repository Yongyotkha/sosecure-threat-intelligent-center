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
    ['middleware' => 'web', 'prefix' => 'manageassets'],
    function () {
        Route::get('/', 'ManageAssetsController@index')->name('manageassets.index')->middleware('can:menu_items');
    }
);
