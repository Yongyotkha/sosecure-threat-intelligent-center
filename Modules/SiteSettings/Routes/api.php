<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:api')->get('/sitesettings', function (Request $request) {
//     return $request->user();
// });


Route::group(
    ['middleware' => 'auth:api', 'prefix' => 'v1', 'namespace' => 'Api\v1'], function () {
        Route::get('sitesettings/{id}', 'SiteSettingApiController@show')->name('sitesettings.api.show')->middleware('can:settings');
        Route::get('sitesettings', 'SiteSettingApiController@index')->name('sitesettings.api.index')->middleware('can:settings');
        Route::post('sitesettings', 'SiteSettingApiController@save')->name('sitesettings.api.save')->middleware('can:sitesettings_create');//->middleware('can:sitesettings_create')
        Route::put('sitesettings/{id}', 'SiteSettingApiController@update')->name('sitesettings.api.update')->middleware('can:sitesettings_update');
        Route::post('sitesettings/change_status', 'SiteSettingApiController@change_status')->name('sitesettings.api.change_status')->middleware('can:sitesettings_update');
        Route::delete('sitesettings/{id}', 'SiteSettingApiController@delete')->name('sitesettings.api.delete')->middleware('can:sitesettings_delete');
    }
);