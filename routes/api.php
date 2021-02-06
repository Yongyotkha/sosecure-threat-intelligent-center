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
Route::group(['prefix' => 'v1/{mode}/{code}'], function () {
    //, 'middleware' => 'auth:api'
    // Route::post('register', 'Auth\RegisterController@register');
    // Route::post('login', 'Auth\LoginController@login');
    Route::post('login', 'Api\AuthController@login');
    Route::post('register_site', 'Api\RegisterSiteController@register_site');
    Route::post('site_request_version', 'Api\RegisterSiteController@site_request_version');
    Route::post('save_deploy_history', 'Api\RegisterSiteController@save_deploy_history');
    Route::post('save_deploy_system', 'Api\RegisterSiteController@save_deploy_system');
    Route::post('test_get', 'Api\ExampleApiController@test_get');
    Route::post('tranferTransactionUpdateCode', 'Api\ApiTransactionUpdateCodeController@tranfer_transaction_update_code');
    Route::post('tranferUser', 'Api\RegisterSiteController@tranferUser');
    Route::post('tranferUserSite', 'Api\RegisterSiteController@tranferUserSite');
    Route::post('tranferSite', 'Api\RegisterSiteController@tranferSite');
    Route::post('tranferProfile', 'Api\RegisterSiteController@tranferProfile');
    Route::post('tranferModelHasRoles', 'Api\RegisterSiteController@tranferModelHasRoles');
    Route::post('tranferPermissions', 'Api\RegisterSiteController@tranferPermissions');
    Route::post('tranferRoles', 'Api\RegisterSiteController@tranferRoles');
    Route::post('tranferRolePermissions', 'Api\RegisterSiteController@tranferRolePermissions');

    Route::post('check/transaction_job_clients/wait', 'Api\TransactionJobClients@transaction_job_clients');
    Route::post('check/transaction_job_clients/tranfer_db', 'Api\TransactionJobClients@tranfer_db');
});

Route::group(['prefix' => 'v1/client-transfer'], function () {
    Route::post('getTranferData', 'Api\ApiTransferClients@getTranferData');
    Route::get('get_encode/{site_id}', 'Api\ApiTransferClients@get_encode');
    Route::post('checkwebserverIP', 'Api\ApiTransferClients@checkWebserverIP');
   
});

Route::group(['prefix' => 'v1/centerinto-transfer'], function () {
    Route::post('updateTFBatchJob', 'Api\ApiTransferCenterInsert@updateBatchJob');
    Route::post('insertToNoRef', 'Api\ApiTransferCenterInsert@insertToNoRef');
    Route::post('insertToRef', 'Api\ApiTransferCenterInsert@insertToRef');
    Route::post('updateIsFix_datacve_mapping', 'Api\ApiTransferCenterInsert@updateIsFix_fx_data_datacve_mapping');
});

Route::group(['prefix' => 'v1/clientinto-transfer'], function () {
    Route::post('insertToNoRef', 'Api\ApiTransferClientInsert@insertToNoRef');
    Route::post('insertToNoRefWithID', 'Api\ApiTransferClientInsert@insertToNoRefWithID');
    Route::post('insertToRef', 'Api\ApiTransferClientInsert@insertToRef');
});

Route::group(['middleware' => 'api','prefix' => 'v1'], function ($router) {//, 'middleware' => 'auth:api'
    $router->get('test_api', 'Auth\LoginController@test_api');
});

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', 'AuthController@login');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('show_data', 'AuthController@show_data');
    Route::post('payload', 'AuthController@payload');

});



// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
