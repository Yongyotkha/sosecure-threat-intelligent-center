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
    ['middleware' => 'web', 'prefix' => 'keywords'],
    function () {
        Route::get('/', 'KeywordsController@index')->name('keywordsController.index')->middleware('can:menu_items');

        Route::post('/dataleak/social/data', 'KeywordsController@tableData')->name('KeywordsController.data')->middleware('can:menu_items');
        Route::get('/dataleak/social/edit/{id}', 'KeywordsController@edit')->name('KeywordsController.edit')->middleware('can:menu_items');
        Route::get('/dataleak/social/delete/{id}', 'KeywordsController@delete')->name('KeywordsController.delete')->middleware('can:menu_items');
        Route::delete('/dataleak/social/delete_process/{id}', 'KeywordsController@delete_process')->name('KeywordsController.delete_process');
        Route::post('/dataleak/social/delete_checked', 'KeywordsController@delete_checked')->name('KeywordsController.delete_checked');
    }
);
