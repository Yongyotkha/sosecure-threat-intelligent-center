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
    ['middleware' => 'web', 'prefix' => 'darkweb'],
    function () {
        Route::get('/', 'DarkWebController@index')->name('darkweb.index')->middleware('can:menu_items');
        Route::get('/jqueryLoadMoreNews', 'DarkWebController@jqueryLoadMoreNews')->name('darkweb.jqueryLoadMoreNews');
        Route::get('/bookmark', 'DarkWebController@bookmark')->name('darkweb.bookmark');
        Route::get('/jqueryLoadMoreNewsBookmark', 'DarkWebController@jqueryLoadMoreNewsBookmark')->name('darkweb.jqueryLoadMoreNewsBookmark');
    }
);