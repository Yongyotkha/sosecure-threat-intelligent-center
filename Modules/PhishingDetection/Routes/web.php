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
	['middleware' => 'web', 'prefix' => 'phishing_detection'],
	function () {
		Route::get('/', 'PhishingDetectionController@index')->name('phishing_detection.index')->middleware('can:menu_items');
		Route::post('/datatable', 'PhishingDetectionController@datatable')->name('phishing_detection.datatable');
		Route::get('/create', 'PhishingDetectionController@create_phishing_detection')->name('phishing_detection.create')->middleware('can:menu_items');
		Route::get('/edit', 'PhishingDetectionController@edit_phishing_detection')->name('phishing_detection.edit')->middleware('can:menu_items');
		Route::get('/view', 'PhishingDetectionController@view_phishing_detection')->name('phishing_detection.view')->middleware('can:menu_items');
		Route::get('/delete', 'PhishingDetectionController@delete_phishing_detection')->name('phishing_detection.delete')->middleware('can:menu_items');
		
		Route::post('/save_phishing', 'PhishingDetectionController@save_phishing')->name('phishing_detection.save_phishing');
		Route::post('/update_status_phishing', 'PhishingDetectionController@update_status_phishing')->name('phishing_detection.update_status_phishing');
		Route::get('/delete_phishing', 'PhishingDetectionController@delete_phishing')->name('phishing_detection.delete_phishing');
		Route::post('/delete_phishing', 'PhishingDetectionController@delete_phishing')->name('phishing_detection.delete_phishing');
		
		Route::get('/data_chart_timeline', 'PhishingDetectionController@data_chart_timeline')->name('phishing_detection.data_chart_timeline');
		Route::get('/data_chart_circle', 'PhishingDetectionController@data_chart_circle')->name('phishing_detection.data_chart_circle');
	}
);