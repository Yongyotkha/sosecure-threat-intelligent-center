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
    ['middleware' => ['web', 'permission:news', 'permission:role_center'], 'prefix' => 'rssfeedsettings'],
    function () {
        Route::get('/certificate', 'CertificateController@index')->name('certificate.index')->middleware('can:menu_items');
    }
);
