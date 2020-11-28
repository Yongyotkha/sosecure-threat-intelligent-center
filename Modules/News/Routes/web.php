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
	['middleware' => 'web', 'prefix' => 'news'],
	function () {
		Route::get('/', 'NewsController@index')->name('news.index')->middleware('can:menu_items');
		Route::get('/detail', 'NewsController@news_detail')->name('news.news_detail')->middleware('can:menu_items');

		Route::get('/test', 'NewsController@test')->name('news.test')->middleware('can:menu_items');
		Route::get('/jqueryLoadMoreNews', 'NewsController@jqueryLoadMoreNews')->name('jqueryLoadMoreNews');
		Route::get('/jqueryLoadMoreNewsTopic', 'NewsController@jqueryLoadMoreNewsTopic')->name('jqueryLoadMoreNewsTopic');
		Route::get('/bookmark', 'NewsController@bookmark')->name('bookmark');
		
	}
);

Route::get('/public/news/detail', 'NewsController@public_detail')->name('news.public_detail');