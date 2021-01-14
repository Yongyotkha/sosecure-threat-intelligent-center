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
    Route::get('/verify_success', 'ReauthenticateController@verify_success')->name('reauth.verify_success');
    Route::get('/{token}', 'ReauthenticateController@verify_site_user')->name('reauth.verify_site_user');
    Route::put('/verify_update_pass/{id}', 'ReauthenticateController@verify_update_pass')->name('reauth.verify_update_pass');
});

Route::get('/reauthenticate-settings', 'ReauthenticateController@index')->name('reauth.reauthenticate-settings');