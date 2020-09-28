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
Route::group(['prefix' => 'v1'], function () {//, 'middleware' => 'auth:api'
    // Route::post('register', 'Auth\RegisterController@register');
    // Route::post('login', 'Auth\LoginController@login');
    Route::post('login', 'Auth\LoginController@login_api');
});
Route::group(['middleware' => 'jwt.auth','prefix' => 'v1'], function () {//, 'middleware' => 'auth:api'
    // Route::post('register', 'Auth\RegisterController@register');
    // Route::post('login', 'Auth\LoginController@login');
    Route::get('test_api', 'Auth\LoginController@test_api');
});

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
