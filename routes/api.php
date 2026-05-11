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
    
    Route::post('get/users', 'Api\AuthController@get_user');
    Route::post('update/users', 'Api\AuthController@update_user');
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

    Route::post('indicator/events_table', 'Api\ApiIndicatorController@events_table');
    Route::post('indicator/events_detail_select', 'Api\ApiIndicatorController@events_detail_select');
    Route::post('indicator/events', 'Api\ApiIndicatorController@events');
    Route::post('indicator/events_load_attributes_tb', 'Api\ApiIndicatorController@events_load_attributes_tb');
    Route::post('indicator/events_load_pulse_tb', 'Api\ApiIndicatorController@events_load_pulse_tb');
    Route::post('indicator/events_count_view', 'Api\ApiIndicatorController@events_count_view');
    Route::post('indicator/load_relatedPulse', 'Api\ApiIndicatorController@load_relatedPulse');
    Route::post('indicator/industries', 'Api\ApiIndicatorController@industries');
    Route::post('indicator/group', 'Api\ApiIndicatorController@group');
    Route::post('indicator/table_tags', 'Api\ApiIndicatorController@table_tags');
    Route::post('indicator/table_groups', 'Api\ApiIndicatorController@table_groups');
    Route::post('indicator/adversaries', 'Api\ApiIndicatorController@adversaries');
    Route::post('indicator/malware', 'Api\ApiIndicatorController@malware');
    Route::post('indicator/show_detail_malware', 'Api\ApiIndicatorController@show_detail_malware');
    Route::post('indicator/show_detail_adversary', 'Api\ApiIndicatorController@show_detail_adversary');
    Route::post('indicator/load_adversary_tb', 'Api\ApiIndicatorController@load_adversary_tb');
    Route::post('indicator/load_malware_tb', 'Api\ApiIndicatorController@load_malware_tb');
    Route::post('indicator/table_summary', 'Api\ApiIndicatorController@table_summary');
    Route::post('indicator/table_summary_export', 'Api\ApiIndicatorController@table_summary_export');
    Route::post('indicator/export_events_indicators', 'Api\ApiIndicatorController@export_events_indicators');
    Route::post('indicator/export_event_indicators', 'Api\ApiIndicatorController@export_event_indicators');
    
    Route::post('dashboard/count_asset', 'Api\ApiDashboardController@count_asset');
    Route::post('dashboard/count_vulnerability', 'Api\ApiDashboardController@count_vulnerability');
    Route::post('dashboard/count_compromised', 'Api\ApiDashboardController@count_compromised');
    Route::post('dashboard/count_data_leak', 'Api\ApiDashboardController@count_data_leak');
    Route::post('dashboard/count_vulnerability_host', 'Api\ApiDashboardController@count_vulnerability_host');
    Route::post('dashboard/chart_indicators', 'Api\ApiDashboardController@chart_indicators');
    Route::post('dashboard/load_chart', 'Api\ApiDashboardController@load_chart');
    Route::post('dashboard/table_dashboard', 'Api\ApiDashboardController@table_dashboard');
    Route::post('dashboard/cve_assets', 'Api\ApiDashboardController@cve_assets');
    
    Route::post('asset/table_asset', 'Api\ApiAssetController@table_asset');
    Route::post('asset/count_asset', 'Api\ApiAssetController@asset_count_asset');
    Route::post('asset/get_selected_filter', 'Api\ApiAssetController@get_selected_filter');

    Route::post('news/index_client', 'Api\ApiNewsController@index_client');
    Route::post('news/jqueryLoadMoreNews', 'Api\ApiNewsController@jqueryLoadMoreNews');
    Route::post('news/jqueryLoadMoreNewsBookmark', 'Api\ApiNewsController@jqueryLoadMoreNewsBookmark');
    Route::post('news/url_bookmark', 'Api\ApiNewsController@url_bookmark');
    Route::post('news/url_news_detail_code', 'Api\ApiNewsController@url_news_detail_code');
    Route::post('news/news_load_top_source', 'Api\ApiNewsController@news_load_top_source');
    Route::post('news/news_load_top_category', 'Api\ApiNewsController@news_load_top_category');
    Route::post('news/rss_news_table', 'Api\ApiNewsController@rss_news_table');
    Route::post('news/rss_data_table', 'Api\ApiNewsController@rss_data_table');
    Route::post('news/rss_setting_table', 'Api\ApiNewsController@rss_setting_table');

    Route::post('vulnerabilitys/vulnerabilitys_table', 'Api\ApiCVEController@vulnerabilitys_table');
    Route::post('vulnerabilitys/index', 'Api\ApiCVEController@vulnerabilitys_index');
    Route::post('vulnerabilitys/count', 'Api\ApiCVEController@vulnerabilitys_count');
    Route::post('vulnerabilitys/top_host', 'Api\ApiCVEController@vulnerabilitys_top_host');
    Route::post('vulnerabilitys/fixed', 'Api\ApiCVEController@vulnerabilitys__fixed');
    Route::post('vulnerabilitys/all', 'Api\ApiCVEController@vulnerabilitys_all');
    Route::post('vulnerabilitys/all_asset_data', 'Api\ApiCVEController@vulnerabilitys_all_asset_data');
    Route::post('vulnerabilitys/asset_data_detail', 'Api\ApiCVEController@vulnerabilitys_asset_data_detail');
    Route::post('vulnerabilitys/change_status_detail', 'Api\ApiCVEController@vulnerabilitys_change_status_detail');
    Route::post('vulnerabilitys/change_status', 'Api\ApiCVEController@vulnerabilitys_change_status');
    Route::post('vulnerabilitys/cve_table', 'Api\ApiCVEController@vulnerabilitys_cve_table');
    Route::post('vulnerabilitys/load_cve', 'Api\ApiCVEController@vulnerabilitys_load_cve');
    Route::post('vulnerabilitys/load_cve_assets', 'Api\ApiCVEController@vulnerabilitys_load_cve_assets');
    Route::post('vulnerabilitys/loadbyip', 'Api\ApiCVEController@vulnerabilitys_loadbyip');
    Route::post('vulnerabilitys/load_cve_by_cpe', 'Api\ApiCVEController@vulnerabilitys_load_cve_by_cpe');
    Route::post('vulnerabilitys/load_cpe_by_asset', 'Api\ApiCVEController@vulnerabilitys_load_cpe_by_asset');
    Route::post('vulnerabilitys/get_sites_by_cve', 'Api\ApiCVEController@vulnerabilitys_get_sites_by_cve');
    Route::post('vulnerabilitys/get_assets_by_site', 'Api\ApiCVEController@vulnerabilitys_get_assets_by_site');


    Route::post('compromised/count_val', 'Api\ApiCompromisedController@compromised_count_val');
    Route::post('compromised/table', 'Api\ApiCompromisedController@compromised_table');
    Route::post('compromised/view', 'Api\ApiCompromisedController@compromised_view');
    Route::post('compromised/delete', 'Api\ApiCompromisedController@compromised_delete');
    Route::post('compromised/delete_select', 'Api\ApiCompromisedController@compromised_delete_select');
    Route::post('compromised/delete_change', 'Api\ApiCompromisedController@compromised_delete_change');
    Route::post('compromised/count_keyword_darkweb', 'Api\ApiCompromisedController@count_keyword_darkweb');
    
    Route::post('data_leak/table', 'Api\ApiDataLeakController@data_leak_table');
    Route::post('data_leak/view', 'Api\ApiDataLeakController@data_leak_view');
    Route::post('data_leak/count_val', 'Api\ApiDataLeakController@data_leak_count_val');
    Route::post('data_leak/count_val_dataleak', 'Api\ApiDataLeakController@data_leak_count_val_dataleak');
    Route::post('data_leak/count_icon', 'Api\ApiDataLeakController@count_icon');
    Route::post('data_leak/count_icon_dataleak', 'Api\ApiDataLeakController@count_icon_dataleak');
    Route::post('data_leak/credentialdatas_count_icon', 'Api\ApiDataLeakController@credentialdatas_count_icon');
    Route::post('data_leak/credentialdatas_count_val', 'Api\ApiDataLeakController@credentialdatas_count_val');
    Route::post('data_leak/dataleak_count_icon', 'Api\ApiDataLeakController@dataleak_count_icon');
    Route::post('data_leak/dataleak_count_val', 'Api\ApiDataLeakController@dataleak_count_val');
    Route::post('data_leak/count_keyword', 'Api\ApiDataLeakController@data_leak_count_keyword');
    Route::post('data_leak/delete', 'Api\ApiDataLeakController@data_leak_delete');
    Route::post('data_leak/delete_select', 'Api\ApiDataLeakController@data_leak_delete_select');
    Route::post('data_leak/delete_change', 'Api\ApiDataLeakController@data_leak_delete_change');
    Route::post('data_leak/getDataLeakSocial', 'Api\ApiDataLeakController@getDataLeakSocial');

    Route::post('data_leak/activity_dataleak_modal', 'Api\ApiDataLeakController@activity_dataleak_modal');
    Route::post('data_leak/activity_history_reload', 'Api\ApiDataLeakController@activity_history_reload');
    Route::post('data_leak/activity_get_edit_data', 'Api\ApiDataLeakController@activity_get_edit_data');
    Route::post('data_leak/activity_save', 'Api\ApiDataLeakController@activity_save');
    Route::post('data_leak/activity_delete', 'Api\ApiDataLeakController@activity_delete');

    Route::post('data_leak/compromise_activity_dataleak_modal', 'Api\ApiDataLeakController@compromise_activity_dataleak_modal');
    Route::post('data_leak/compromise_activity_history_reload', 'Api\ApiDataLeakController@compromise_activity_history_reload');
    Route::post('data_leak/compromise_activity_get_edit_data', 'Api\ApiDataLeakController@compromise_activity_get_edit_data');
    Route::post('data_leak/compromise_activity_save', 'Api\ApiDataLeakController@compromise_activity_save');
    Route::post('data_leak/compromise_activity_delete', 'Api\ApiDataLeakController@compromise_activity_delete');

    Route::post('web_defacement/load_card', 'Api\ApiWebdefacementController@web_defacement_load_card');
    Route::post('web_defacement/detail', 'Api\ApiWebdefacementController@web_defacement_detail');
    Route::post('web_defacement/update_original', 'Api\ApiWebdefacementController@web_defacement_update_original');
    Route::post('web_defacement/update_original_detail', 'Api\ApiWebdefacementController@web_defacement_update_original_detail');
    Route::post('web_defacement/deface_now', 'Api\ApiWebdefacementController@web_defacement_deface_now');
    Route::post('web_defacement/deface_now_detail', 'Api\ApiWebdefacementController@web_defacement_deface_now_detail');
    Route::post('web_defacement/update_image', 'Api\ApiWebdefacementController@web_defacement_update_image');
    Route::post('web_defacement/change_status', 'Api\ApiWebdefacementController@web_defacement_change_status');
    Route::post('web_defacement/check_status', 'Api\ApiWebdefacementController@web_defacement_check_status');
    Route::post('web_defacement/alert_to_customer', 'Api\ApiWebdefacementController@web_defacement_alert_to_customer');
    Route::post('web_defacement/show_diff_hash', 'Api\ApiWebdefacementController@web_defacement_show_diff_hash');
    Route::post('web_defacement/export_report', 'Api\ApiWebdefacementController@web_defacement_export_report');

    Route::post('monitor/monitor_system_save', 'Api\ApiMonitorController@monitor_system_save');

    Route::post('check/log_site', 'Api\TransactionLogsite@transaction_log_site');
    
    Route::post('search/searchAll', 'Api\ApiSearchController@searchAll');
    Route::post('search/loadSearchAPI', 'Api\ApiSearchController@loadSearchAPI');

    Route::post('transaction_send_log_error', 'Api\ReciveLogErrorController@recive_log_error');

    //Agent
    Route::post('agentCenter/dataInfo', 'Api\ApiAgentController@dataInfo');
    Route::post('agentCenter/loginAgent', 'Api\ApiAgentController@loginAgent');
    Route::post('agentCenter/checkedAgentApproved', 'Api\ApiAgentController@checkedAgentApproved');
    Route::post('agentCenter/agentOnlineTimestamp', 'Api\ApiAgentController@agentOnlineTimestamp');
    Route::post('agentCenter/updateRuleDownload', 'Api\ApiAgentController@updateRuleDownload');
    Route::post('agentCenter/downloadRule', 'Api\ApiAgentController@downloadRule');
    Route::post('agentCenter/downloadRuleComplete', 'Api\ApiAgentController@downloadRuleComplete');
    Route::post('agentCenter/sendLogYara', 'Api\ApiAgentController@sendLogYara');
    Route::post('agentCenter/sendAgentScanLog', 'Api\ApiAgentController@sendAgentScanLog');

    Route::post('agentCenter/downloadRuleSite', 'Api\ApiAgentController@downloadRuleSite');
    Route::post('agentCenter/downloadRuleSiteComplete', 'Api\ApiAgentController@downloadRuleSiteComplete');
    Route::post('agentCenter/updateConfig', 'Api\ApiAgentController@updateConfig');
    Route::post('agentCenter/getConfig', 'Api\ApiAgentController@getConfig');
    Route::post('agentCenter/getRule', 'Api\ApiAgentController@getRule');
    Route::post('agentCenter/sendHash', 'Api\ApiAgentController@sendHash');
    Route::post('agentCenter/latestVersion', 'Api\ApiAgentController@latestVersion');

    //asset
    Route::post('tranferAsset', 'Api\ApiTransferAssetController@tranferAsset');

     //phishing
     Route::post('phishing/table', 'Api\ApiPhishingController@table');
     Route::post('phishing/create_phishing_detection', 'Api\ApiPhishingController@create_phishing_detection');
     Route::post('phishing/edit_phishing_detection', 'Api\ApiPhishingController@edit_phishing_detection');
     Route::post('phishing/view_phishing_detection', 'Api\ApiPhishingController@view_phishing_detection');

     Route::post('phishing/save_phishing', 'Api\ApiPhishingController@save_phishing');
     Route::post('phishing/update_status_phishing', 'Api\ApiPhishingController@update_status_phishing');
     Route::post('phishing/delete_phishing', 'Api\ApiPhishingController@delete_phishing');
     
     Route::post('phishing/data_chart_timeline', 'Api\ApiPhishingController@data_chart_timeline');
     Route::post('phishing/data_chart_circle', 'Api\ApiPhishingController@data_chart_circle');

     //keyword
     Route::post('show_keywords', 'Api\ApiKeywordController@show_keywords');
     Route::post('keyword/get_keyword_main', 'Api\ApiKeywordController@get_keyword_main');
     Route::post('keyword/get_keyword_sub', 'Api\ApiKeywordController@get_keyword_sub');

     //brandabuse
    Route::post('brandabuse/brand_abuse_count_val', 'Api\ApiBrandabuseController@brand_abuse_count_val');
    Route::post('brandabuse/socialdatas_all_site', 'Api\ApiBrandabuseController@socialdatas_all_site');
    Route::post('brandabuse/socialdatas_all_site_tb', 'Api\ApiBrandabuseController@socialdatas_all_site_tb');
    Route::post('brandabuse/add_brandabuse', 'Api\ApiBrandabuseController@add_brandabuse');
    Route::post('brandabuse/edit_brandabuse_modal', 'Api\ApiBrandabuseController@edit_brandabuse_modal');
    Route::post('brandabuse/edit_brandabuse', 'Api\ApiBrandabuseController@edit_brandabuse');
    Route::post('brandabuse/change_status_brandabusedata', 'Api\ApiBrandabuseController@change_status_brandabusedata');
    Route::post('brandabuse/delete_brandabusedata', 'Api\ApiBrandabuseController@delete_brandabusedata');
    Route::post('brandabuse/change_delete_brandabusedata', 'Api\ApiBrandabuseController@change_delete_brandabusedata');

    Route::post('brandabuse/activity_brandabuse_modal', 'Api\ApiBrandabuseController@activity_brandabuse_modal');
    Route::post('brandabuse/activity_save', 'Api\ApiBrandabuseController@activity_save');
    Route::post('brandabuse/activity_history_reload', 'Api\ApiBrandabuseController@activity_history_reload');
    Route::post('brandabuse/activity_get_edit_data', 'Api\ApiBrandabuseController@activity_get_edit_data');
    Route::post('brandabuse/activity_delete', 'Api\ApiBrandabuseController@activity_delete');
    Route::post('brandabuse/brandabusefeedsocial_datatables', 'Api\ApiBrandabuseController@brandabusefeedsocial_datatables');
    Route::post('brandabuse/approve_data_feed', 'Api\ApiBrandabuseController@approve_data_feed');
    Route::post('brandabuse/cancle_data_feed', 'Api\ApiBrandabuseController@cancle_data_feed');

    Route::post('brandabuse/view', 'Api\ApiBrandabuseController@brandabuse_view');
    Route::post('brandabuse/count_val', 'Api\ApiBrandabuseController@brandabuse_count_val');
    Route::post('brandabuse/delete', 'Api\ApiBrandabuseController@brandabuse_delete');
    Route::post('brandabuse/delete_select', 'Api\ApiBrandabuseController@brandabuse_delete_select');
    Route::post('brandabuse/delete_change', 'Api\ApiBrandabuseController@brandabuse_delete_change');
    Route::post('brandabuse/count_keyword', 'Api\ApiBrandabuseController@count_keyword');
    Route::post('brandabuse/count_icon', 'Api\ApiBrandabuseController@count_icon');
    Route::post('brandabuse/getDataLeakSocial', 'Api\ApiBrandabuseController@getDataLeakSocial');

    Route::post('brandabuse/activity_dataleak_modal', 'Api\ApiBrandabuseController@activity_dataleak_modal');
    Route::post('brandabuse/activity_history_reload', 'Api\ApiBrandabuseController@activity_history_reload');
    Route::post('brandabuse/activity_get_edit_data', 'Api\ApiBrandabuseController@activity_get_edit_data');
    Route::post('brandabuse/activity_save', 'Api\ApiBrandabuseController@activity_save');
    Route::post('brandabuse/activity_delete', 'Api\ApiBrandabuseController@activity_delete');

    Route::post('brandabuse/compromise_activity_dataleak_modal', 'Api\ApiBrandabuseController@compromise_activity_dataleak_modal');
    Route::post('brandabuse/compromise_activity_history_reload', 'Api\ApiBrandabuseController@compromise_activity_history_reload');
    Route::post('brandabuse/compromise_activity_get_edit_data', 'Api\ApiBrandabuseController@compromise_activity_get_edit_data');
    Route::post('brandabuse/compromise_activity_save', 'Api\ApiBrandabuseController@compromise_activity_save');
    Route::post('brandabuse/compromise_activity_delete', 'Api\ApiBrandabuseController@compromise_activity_delete');
    

    // actor
    Route::post('actor/ActorIndex', 'Api\ApiActorController@ActorIndex');
    Route::post('actor/ActorDataTable', 'Api\ApiActorController@ActorDataTable');
    Route::post('actor/CampDataTable', 'Api\ApiActorController@CampDataTable');
    Route::post('actor/TechDataTable', 'Api\ApiActorController@TechDataTable');
    Route::post('actor/chart_actor', 'Api\ApiActorController@chart_actor');
    Route::post('actor/chart_techniques', 'Api\ApiActorController@chart_techniques');
    Route::post('actor/ActorDetailRelatedNew', 'Api\ApiActorController@ActorDetailRelatedNew');
    Route::post('actor/ActorDetailRelatedCVE', 'Api\ApiActorController@ActorDetailRelatedCVE');
    Route::post('actor/ActorDetailRelatedIndi', 'Api\ApiActorController@ActorDetailRelatedIndi');
    Route::post('actor/ActorDetailRelatedCampainge', 'Api\ApiActorController@ActorDetailRelatedCampainge');
    Route::post('actor/ActorDetail', 'Api\ApiActorController@ActorDetail');
    Route::post('actor/CampaingeDetail', 'Api\ApiActorController@CampaingeDetail');
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


Route::group(['prefix' => 'v1/agent'], function () {
    Route::get('connection', 'Api\ApiAgentController@index_client');

});

Route::group(['prefix' => 'v1/saveData/{code}'], function () {
    Route::post('saveCVE', 'Api\ApiNewSaveDataController@saveCVE');
    Route::post('saveIndicator', 'Api\ApiNewSaveDataController@saveIndicator');
    Route::post('saveSetting', 'Api\ApiNewSaveDataController@saveSetting');
    Route::post('getData', 'Api\ApiNewSaveDataController@getData');
    Route::post('getIndicator', 'Api\ApiNewSaveDataController@getIndicator');
});

// Public API for Events & Indicators (with API Token Auth)
Route::group(['prefix' => 'v1/public', 'middleware' => 'api.token'], function () {
    Route::get('events', 'Api\PublicEventApiController@listEvents');
    Route::get('events/feed', 'Api\PublicEventApiController@listEventsWithIndicators');
    Route::get('events/{pulse_id}', 'Api\PublicEventApiController@getEvent');
    Route::get('events/{pulse_id}/indicators', 'Api\PublicEventApiController@getEventIndicators');
    Route::get('indicators', 'Api\PublicEventApiController@listIndicators');
});

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Service API - Receive Events & Indicators (with Service API Token Auth)
Route::group(['prefix' => 'v1/service', 'middleware' => 'api.token.service'], function () {
    Route::post('events', 'Api\ServiceApiController@receiveEvent');
});

// IoC Feed (Graylog Integration)
Route::group(['prefix' => 'v1/ioc-feed', 'namespace' => '\Modules\IocFeed\Http\Controllers', 'middleware' => 'api.token'], function () {
    // Whitelist APIs
    Route::get('whitelist', 'IocWhitelistController@index');
    Route::post('whitelist', 'IocWhitelistController@store');
    Route::post('whitelist/upload', 'IocWhitelistController@upload');
    Route::delete('whitelist', 'IocWhitelistController@bulkDestroy');
    Route::delete('whitelist/{id}', 'IocWhitelistController@destroy');

    Route::get('{category}.csv', 'IocFeedController@exportCsv');
    Route::post('ioc', 'IocFeedController@store');
    Route::put('ioc/{id}', 'IocFeedController@update');
    Route::delete('ioc/{id}', 'IocFeedController@destroy');
});
