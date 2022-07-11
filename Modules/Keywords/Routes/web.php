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
        Route::post('/dataleak/social/save_add_keyword_main', 'KeywordsController@save_add_keyword_main')->name('KeywordsController.save_add_keyword_main');
        Route::post('/dataleak/social/get_keyword_main', 'KeywordsController@get_keyword_main')->name('KeywordsController.get_keyword_main');
        Route::post('/dataleak/social/get_keyword_sub', 'KeywordsController@get_keyword_sub')->name('KeywordsController.get_keyword_sub');
        Route::post('/dataleak/social/edit_keyword_process', 'KeywordsController@edit_keyword_process')->name('KeywordsController.edit_keyword_process');
        Route::post('/dataleak/social/del_keyword_process', 'KeywordsController@del_keyword_process')->name('KeywordsController.del_keyword_process');
        Route::post('/dataleak/social/check_insert_keyword_process', 'KeywordsController@check_insert_keyword_process')->name('KeywordsController.check_insert_keyword_process');
     
      
    }
);

Route::group(
    ['middleware' => 'web'],
    function () {
        Route::get('/show_keywords', 'KeywordsController@show_keyword')->name('show_keywords')->middleware('can:menu_items');
    }
);
