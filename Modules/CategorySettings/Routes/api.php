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

// ตัวdefault
// Route::middleware('auth:api')->get('/categorysettings', function (Request $request) {
//     return $request->user();
// });

Route::group(
    ['middleware' => 'auth:api', 'prefix' => 'v1', 'namespace' => 'Api\v1'], function () {
        Route::get('categorysettings/{id}', 'CategorySettingApiController@show')->name('categorysettings.api.show')->middleware('can:settings');
        Route::get('categorysettings', 'CategorySettingApiController@index')->name('categorysettings.api.index')->middleware('can:settings');
        Route::post('categorysettings', 'CategorySettingApiController@save')->name('categorysettings.api.save')->middleware('can:categorysettings_create');//->middleware('can:categorysettings_create')
        // Route::put('categorysettings/{id}', 'CategorySettingApiController@update')->name('categorysettings.api.update')->middleware('can:categorysettings_update');
        // Route::post('categorysettings/change_status', 'CategorySettingApiController@change_status')->name('categorysettings.api.change_status')->middleware('can:categorysettings_update');
        // Route::delete('categorysettings/{id}', 'CategorySettingApiController@delete')->name('categorysettings.api.delete')->middleware('can:categorysettings_delete');
    }
);
