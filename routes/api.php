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

    Route::post('indicator/events_table', 'Api\ApiGetMongoDB@events_table');
    Route::post('indicator/events_detail_select', 'Api\ApiGetMongoDB@events_detail_select');
    Route::post('indicator/events', 'Api\ApiGetMongoDB@events');
    Route::post('indicator/events_load_attributes_tb', 'Api\ApiGetMongoDB@events_load_attributes_tb');
    Route::post('indicator/events_load_pulse_tb', 'Api\ApiGetMongoDB@events_load_pulse_tb');
    Route::post('indicator/events_count_view', 'Api\ApiGetMongoDB@events_count_view');
    Route::post('indicator/load_relatedPulse', 'Api\ApiGetMongoDB@load_relatedPulse');
    
    Route::post('dashboard/count_asset', 'Api\ApiGetMongoDB@count_asset');
    Route::post('dashboard/count_vulnerability', 'Api\ApiGetMongoDB@count_vulnerability');
    Route::post('dashboard/count_compromised', 'Api\ApiGetMongoDB@count_compromised');
    Route::post('dashboard/count_data_leak', 'Api\ApiGetMongoDB@count_data_leak');
    Route::post('dashboard/count_vulnerability_host', 'Api\ApiGetMongoDB@count_vulnerability_host');
    Route::post('dashboard/chart_indicators', 'Api\ApiGetMongoDB@chart_indicators');
    Route::post('dashboard/load_chart', 'Api\ApiGetMongoDB@load_chart');
    Route::post('dashboard/table_dashboard', 'Api\ApiGetMongoDB@table_dashboard');
    Route::post('dashboard/cve_assets', 'Api\ApiGetMongoDB@cve_assets');
    
    Route::post('asset/table_asset', 'Api\ApiGetMongoDB@table_asset');

    Route::post('news/index_client', 'Api\ApiGetMongoDB@index_client');
    Route::post('news/jqueryLoadMoreNews', 'Api\ApiGetMongoDB@jqueryLoadMoreNews');
    Route::post('news/jqueryLoadMoreNewsBookmark', 'Api\ApiGetMongoDB@jqueryLoadMoreNewsBookmark');
    Route::post('news/url_bookmark', 'Api\ApiGetMongoDB@url_bookmark');
    Route::post('news/url_news_detail_code', 'Api\ApiGetMongoDB@url_news_detail_code');

    Route::post('vulnerabilitys/vulnerabilitys_table', 'Api\ApiGetMongoDB@vulnerabilitys_table');
    Route::post('vulnerabilitys/index', 'Api\ApiGetMongoDB@vulnerabilitys_index');
    Route::post('vulnerabilitys/count', 'Api\ApiGetMongoDB@vulnerabilitys_count');
    Route::post('vulnerabilitys/top_host', 'Api\ApiGetMongoDB@vulnerabilitys_top_host');
    Route::post('vulnerabilitys/fixed', 'Api\ApiGetMongoDB@vulnerabilitys__fixed');
    Route::post('vulnerabilitys/all', 'Api\ApiGetMongoDB@vulnerabilitys_all');
    Route::post('vulnerabilitys/all_asset_data', 'Api\ApiGetMongoDB@vulnerabilitys_all_asset_data');
    Route::post('vulnerabilitys/asset_data_detail', 'Api\ApiGetMongoDB@vulnerabilitys_asset_data_detail');
    Route::post('vulnerabilitys/change_status_detail', 'Api\ApiGetMongoDB@vulnerabilitys_change_status_detail');
    Route::post('vulnerabilitys/change_status', 'Api\ApiGetMongoDB@vulnerabilitys_change_status');
    Route::post('vulnerabilitys/cve_table', 'Api\ApiGetMongoDB@vulnerabilitys_cve_table');
    Route::post('vulnerabilitys/load_cve', 'Api\ApiGetMongoDB@vulnerabilitys_load_cve');

    Route::post('compromised/count_val', 'Api\ApiGetMongoDB@compromised_count_val');
    Route::post('compromised/table', 'Api\ApiGetMongoDB@compromised_table');
    Route::post('compromised/view', 'Api\ApiGetMongoDB@compromised_view');
    
    Route::post('data_leak/table', 'Api\ApiGetMongoDB@data_leak_table');
    Route::post('data_leak/view', 'Api\ApiGetMongoDB@data_leak_view');
    Route::post('data_leak/count_val', 'Api\ApiGetMongoDB@data_leak_count_val');

    Route::post('web_defacement/load_card', 'Api\ApiGetMongoDB@web_defacement_load_card');
    Route::post('web_defacement/detail', 'Api\ApiGetMongoDB@web_defacement_detail');
    Route::post('web_defacement/update_original', 'Api\ApiGetMongoDB@web_defacement_update_original');
    Route::post('web_defacement/update_original_detail', 'Api\ApiGetMongoDB@web_defacement_update_original_detail');
    Route::post('web_defacement/deface_now', 'Api\ApiGetMongoDB@web_defacement_deface_now');
    Route::post('web_defacement/deface_now_detail', 'Api\ApiGetMongoDB@web_defacement_deface_now_detail');
    Route::post('web_defacement/update_image', 'Api\ApiGetMongoDB@web_defacement_update_image');

    Route::post('check/log_site', 'Api\TransactionLogsite@transaction_log_site');
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
