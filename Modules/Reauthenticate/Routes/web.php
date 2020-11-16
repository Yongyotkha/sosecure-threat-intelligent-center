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

Route::prefix('reauthenticate')->group(function() {
    Route::get('/', 'ReauthenticateController@index');
});

Route::get('/reauthenticate-settings', 'ReauthenticateController@index')->name('reauth.reauthenticate-settings');