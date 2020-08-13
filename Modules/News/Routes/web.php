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
		Route::get('/news_details', 'NewsController@news_details')->name('news.news_details')->middleware('can:menu_items');
	}
);

Route::get('/public_details', 'NewsController@public_details')->name('news.public_details');