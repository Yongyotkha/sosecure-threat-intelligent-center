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

// Route::middleware('auth:api')->get('/indicators', function (Request $request) {
//     return $request->user();
// });

Route::group(['prefix' => 'v1/indicators'], function () {
    Route::post('events_table', 'IndicatorsController@datatableEvent')->name('indicators.events_table');
});