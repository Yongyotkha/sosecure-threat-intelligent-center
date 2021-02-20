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
    ['middleware' => 'web', 'prefix' => 'social'],
    function () {
        Route::get('/', 'SocialController@index')->name('social.index')->middleware('can:menu_items');
        Route::get('/jqueryLoadMoreNewsBookmark', 'SocialController@jqueryLoadMoreNewsBookmark')->name('social.jqueryLoadMoreNewsBookmark');
        Route::get('/jqueryLoadMoreNews', 'SocialController@jqueryLoadMoreNews')->name('social.jqueryLoadMoreNews');
        Route::get('/bookmark', 'SocialController@bookmark')->name('social.bookmark');
        Route::get('/add_read', 'SocialController@add_read')->name('social.add_read');
        Route::post('/count_val', 'SocialController@count_val')->name('social.count_val')->middleware('can:menu_items');
        Route::post('/count_icon', 'SocialController@count_icon')->name('social.count_icon')->middleware('can:menu_items');
    }
);
