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

Route::prefix('websitesalepage')->group(function() {
    Route::get('/', 'WebsiteSalePageController@th')->name('web.th');
    Route::get('/th', 'WebsiteSalePageController@th')->name('web.th');
    Route::get('/en', 'WebsiteSalePageController@en')->name('web.en');
});
