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

// Route::prefix('sitesettings')->group(function() {
    // Route::get('/', 'SiteSettingsController@index');

Route::group(
    ['middleware' => ['web', 'permission:role_center', 'permission:settings'], 'prefix' => 'sitesettings'],
    function () {
        Route::get('/', 'SiteSettingsController@index')->name('sitesettings.index')->middleware('can:menu_items');
        Route::get('/test_mongo', 'SiteSettingsController@test_mongo')->name('sitesettings.test_mongo')->middleware('can:menu_items');
        Route::get('/test_mongo2', 'SiteSettingsController@test_mongo2')->name('sitesettings.test_mongo2')->middleware('can:menu_items');
        Route::get('/phpinfo', 'SiteSettingsController@phpinfo')->name('sitesettings.phpinfo')->middleware('can:menu_items');
        Route::post('data', 'SiteSettingsController@tableData')->name('sitesettings.data')->middleware('can:menu_items');
        Route::get('create', 'SiteSettingsController@create')->name('sitesettings.create')->middleware('can:settings');
        Route::post('bulk-delete', 'SiteSettingsController@bulkDelete')->name('sitesettings.bulk.delete')->middleware(['can:sitesettings_delete']);//->middleware(['can:sitesettings_delete', 'demo']);
        Route::post('change_status', 'SiteSettingsController@change_status')->name('sitesettings.change_status')->middleware(['can:sitesettings_update']);
        Route::get('edit-sitesettings/{id}', 'SiteSettingsController@edit')->name('sitesettings.edit')->middleware('can:sitesettings_update');
        Route::get('delete-sitesettings/{id}', 'SiteSettingsController@delete')->name('sitesettings.delete')->middleware('can:sitesettings_delete');
        Route::post('sitesettings', 'SiteSettingsController@store')->name('sitesettings.save')->middleware('can:sitesettings_create');
        Route::put('sitesettings/{id}', 'SiteSettingsController@update')->name('sitesettings.update.settings')->middleware('can:sitesettings_update');
        Route::post('sitesettings/change_status', 'SiteSettingsController@change_status')->name('sitesettings.change_status.settings')->middleware('can:sitesettings_update');
        Route::post('sitesettings/sitesettings_delete', 'SiteSettingsController@sitesettings_delete')->name('sitesettings.sitesettings_delete');
        // Route::get('/test', 'SiteSettingsController@test')->name('sitesettings.test')->middleware('can:menu_items');
        Route::post('sitesettings/artisan_call', 'SiteSettingsController@artisan_call')->name('sitesettings.artisan_call')->middleware('can:menu_items');
        Route::get('/systemsetting/{id}', 'SystemSettingsController@systemsetting')->name('systemsetting.index')->middleware('can:menu_items');

        Route::get('/data-setting/{id}', 'DataSettingsController@datasetting')->name('datasettings.index')->middleware('can:menu_items');
        Route::put('data-setting/{id}', 'DataSettingsController@update')->name('datasettings.update.settings')->middleware('can:sitesettings_update');
        Route::get('get/uuid', function(){
            
            return generator_uuid();
        })->name('datasettings.get.uuid');

        Route::get('/users-setting/{id}', 'UsersSettingsController@users_settings')->name('userssettings.index')->middleware('can:menu_items');
        Route::get('/domain-setting/{id}', 'DomainSettingsController@domain_setting')->name('domain.index')->middleware('can:menu_items');
        Route::get('/domain-setting/detail/{tab?}', 'DomainSettingsController@domain_detail')->name('domain_detail.index');
        Route::get('/domain-setting/domain/create/{code}', 'DomainSettingsController@create')->name('domain.create')->middleware('can:menu_items');//->middleware('can:categorysettings_create')
        Route::get('/keyword-setting/keyword/create/{code}', 'KeywordSettingController@create')->name('keyword.create')->middleware('can:menu_items');//->middleware('can:categorysettings_create')
        Route::post('/domain-setting/domain/save/{code}', 'DomainSettingsController@save')->name('domain.save')->middleware('can:categorysettings_create');//->middleware('can:categorysettings_create')
        Route::post('/keyword-setting/keyword/save/{code}', 'KeywordSettingController@save')->name('keyword.save')->middleware('can:menu_items');//->middleware('can:categorysettings_create')
        Route::get('/domain-setting/domain/redo/{id}', 'DomainSettingsController@redo')->name('domainsettings.redo')->middleware('can:categorysettings_update');
        Route::post('/domain-setting/domain/change_status', 'DomainSettingsController@change_status')->name('domainsettings.change_status')->middleware(['can:categorysettings_update']);
        Route::post('/domain-setting/domain/del_domain_select', 'DomainSettingsController@del_domain_select')->name('domainsettings.del_domain_select')->middleware(['can:categorysettings_delete']);
        Route::post('/keyword-setting/keyword/change_status', 'KeywordSettingController@change_status')->name('keyword.change_status')->middleware(['can:menu_items']);
        Route::post('/domain-setting/domain/bulk-delete', 'DomainSettingsController@bulkDelete')->name('domainsettings.bulk.delete')->middleware(['can:categorysettings_delete']);//->middleware(['can:categorysettings_delete', 'demo']);
        Route::post('/domain-setting/domain/data', 'DomainSettingsController@tableData')->name('domainsettings.data')->middleware('can:menu_items');
        // Route::post('/dataleak/social/data', 'KeywordsController@tableData')->name('KeywordsController.data')->middleware('can:menu_items');
        Route::get('/domain-setting/domain/test', 'DomainSettingsController@test')->name('domainsettings.test')->middleware('can:menu_items');
        Route::get('/domain-setting/domain/edit/{id}', 'DomainSettingsController@edit')->name('domainsettings.edit')->middleware('can:categorysettings_update');
        // Route::get('/dataleak/social/edit/{id}', 'KeywordsController@edit')->name('KeywordsController.edit')->middleware('can:menu_items');
        // Route::get('/dataleak/social/delete/{id}', 'KeywordsController@delete')->name('KeywordsController.delete')->middleware('can:menu_items');
        Route::get('/domain-setting/domain/delete/{id}', 'DomainSettingsController@delete')->name('domainsettings.delete')->middleware('can:categorysettings_delete');
        Route::put('/domain-setting/domain/update/{id}', 'DomainSettingsController@update')->name('domainsettings.update')->middleware('can:sitesettings_update');
        Route::put('/keyword-setting/keyword/update/{id}', 'KeywordSettingController@update')->name('keyword.update')->middleware('can:menu_items');
        Route::delete('/domain-setting/domain/delete_process/{id}', 'DomainSettingsController@delete_process')->name('domainsettings.delete_process')->middleware('can:categorysettings_delete');
        Route::put('/domain-setting/domain/redo_process/{id}', 'DomainSettingsController@redo_process')->name('domainsettings.redo_process')->middleware('can:sitesettings_update');

        Route::get('/user-setting/user/create/{code}', 'UsersSettingsController@create')->name('user.create')->middleware('can:menu_items');//->middleware('can:categorysettings_create')
        Route::post('/user-setting/user/save/{code}', 'UsersSettingsController@save')->name('user.save')->middleware('can:categorysettings_create');//->middleware('can:categorysettings_create')
        Route::post('/user-setting/user/change_status', 'UsersSettingsController@change_status')->name('user.change_status')->middleware(['can:categorysettings_update']);
        Route::post('/user-setting/user/bulk-delete', 'UsersSettingsController@bulkDelete')->name('user.bulk.delete')->middleware(['can:categorysettings_delete']);//->middleware(['can:categorysettings_delete', 'demo']);
        Route::get('/user-setting/user/data', 'UsersSettingsController@tableData')->name('user.data')->middleware('can:menu_items');
        Route::get('/user-setting/user/test', 'UsersSettingsController@test')->name('user.test')->middleware('can:menu_items');
        Route::get('/user-setting/user/edit/{id}', 'UsersSettingsController@edit')->name('user.edit')->middleware('can:categorysettings_update');
        Route::get('/user-setting/user/edit_gen_pass/{id}', 'UsersSettingsController@edit_gen_pass')->name('user.edit_gen_pass')->middleware('can:categorysettings_update');
        Route::post('/user-setting/user/process_gen_pass', 'UsersSettingsController@process_gen_pass')->name('user.process_gen_pass')->middleware(['can:categorysettings_update']);
        Route::get('/user-setting/user/delete/{id}', 'UsersSettingsController@delete')->name('user.delete2')->middleware('can:categorysettings_delete');
        Route::put('/user-setting/user/update/{id}', 'UsersSettingsController@update')->name('user.update')->middleware('can:sitesettings_update');
        Route::delete('/user-setting/user/delete_process/{id}', 'UsersSettingsController@delete_process')->name('user.delete_process')->middleware('can:categorysettings_delete');
        Route::post('/user-setting/user/delete_process_change', 'UsersSettingsController@delete_process_change')->name('user.delete_process_change');
   
        Route::put('/vulnerability_logs/upsert/{id}', 'VulnerabilityController@upsert_VulnerabilityLogs')->name('vulsetting.upsert')->middleware('can:menu_items');
        Route::put('/vulnerability_logs/upsertSysFormat/{id}', 'VulnerabilityController@upsert_VulnerabilitySysFormat')->name('vulsetting.upsertSys')->middleware('can:menu_items');
        Route::put('/vulnerability_logs/cve_sent_log/{id}', 'VulnerabilityController@cve_sent_log')->name('vulsetting.cve_sent_log')->middleware('can:menu_items');
        Route::get('/vulnerability_logs/{id}', 'VulnerabilityController@index')->name('vulsetting.vul_logs')->middleware('can:menu_items');
        Route::post('/vulnerability_logs/vendor/change_status', 'VulnerabilityController@change_status')->name('vulsetting.change_status')->middleware(['can:menu_items']);


        Route::get('/indicators_logs/{id}', 'IndicatorsSettingController@index')->name('indisetting.indi_logs')->middleware('can:menu_items');
        Route::put('/indicators_logs/upsert/{id}', 'IndicatorsSettingController@upsert_IndicatorsLogs')->name('indisetting.upsert')->middleware('can:menu_items');
        Route::put('/indicators_logs/upsertSysFormat/{id}', 'IndicatorsSettingController@upsert_IndicatorsSysFormat')->name('indisetting.upsertSys')->middleware('can:menu_items');
        Route::put('/indicators_logs/indicator_log/{id}', 'IndicatorsSettingController@indicator_log')->name('indisetting.indicator_log')->middleware('can:menu_items');



        Route::get('/vulnerability_assets/{id}', 'VulnerabilityController@vulassets')->name('vulsetting.vul_assets')->middleware('can:menu_items');
        Route::get('/vulnerability_assets/cve_assets/data', 'VulnerabilityController@tableData')->name('cve_assets.data')->middleware('can:menu_items');
        Route::get('/vulnerability_assets/create/{code}', 'VulnerabilityController@create')->name('vul_assets.create')->middleware('can:menu_items');
        Route::post('/vulnerability_assets/save/{code}', 'VulnerabilityController@saveCveAsset')->name('vul_assets.save')->middleware('can:menu_items');//->middleware('can:categorysettings_create')
        Route::post('/vulnerability_assets/vulsetting_delete', 'VulnerabilityController@vulsetting_delete')->name('vul_assets.vulsetting_delete')->middleware('can:menu_items');

        Route::get('/keywordsetting/{id}', 'DataLeakController@keyword')->name('keyword.index')->middleware('can:menu_items');
       
        Route::get('/assets/{id}', 'AssetsSiteController@assets')->name('assetssite.index')->middleware('can:menu_items');
        Route::post('/assets/get_domain', 'AssetsSiteController@get_domain')->name('assetssite.get_domain')->middleware('can:menu_items');

        Route::get('/socialdatas/{id}', 'DataLeakController@socialdatas')->name('socialdatas.index')->middleware('can:menu_items');
        Route::get('/darkweb_datas/{id}', 'DataLeakController@darkweb_datas')->name('darkweb_datas.index')->middleware('can:menu_items');
        Route::post('/socialdatas/datatables', 'DataLeakController@socialdatas_datatables')->name('socialdatas.socialdatas_datatables')->middleware('can:menu_items');
        Route::post('/socialdatas/change_status', 'DataLeakController@change_status')->name('socialdatas.change_status');
        Route::get('/socialdatas/delete_socialdatas/{code}', 'DataLeakController@delete_socialdatas')->name('socialdatas.delete');
        Route::delete('/socialdatas/delete_socialdata/{code}', 'DataLeakController@delete_socialdata')->name('socialdatas.delete_socialdata');
        Route::post('/socialdatas/socialdatas_change_delete', 'DataLeakController@socialdatas_change_delete')->name('socialdatas.socialdatas_change_delete')->middleware('can:menu_items');


    }
);


// WebDefacement
Route::group(
    ['middleware' => ['web', 'permission:role_center', 'permission:web_defacement']],
    function () {
        Route::get('/WebDefacement-website/{id}', 'WebDefacementController@webdefacement_website')->name('webdefacement_website.index')->middleware('can:menu_items');
        Route::post('/WebDefacement-website/load_card_by_site', 'WebDefacementController@load_card_by_site')->name('webdefacement.load_card_by_site')->middleware('can:menu_items');
        Route::post('/WebDefacement-website/delete_websefacement_process', 'WebDefacementController@delete_websefacement_process')->name('webdefacement.delete_websefacement_process');
        Route::get('/edit-image/{site_id}/{id}', 'WebDefacementController@edit_image')->name('webdefacement_website.edit_image')->middleware('can:menu_items');
        Route::post('/edit-image/get_update_image_screenshot', 'WebDefacementController@get_update_image_screenshot')->name('webdefacement.get_update_image_screenshot')->middleware('can:menu_items');
        Route::post('/WebDefacement-website/get_check_site', 'WebDefacementController@get_check_site')->name('webdefacement.get_check_site')->middleware('can:menu_items');
        Route::post('/WebDefacement-website/get_create_open_md_site_url', 'WebDefacementController@get_create_open_md_site_url')->name('webdefacement.get_create_open_md_site_url')->middleware('can:menu_items');
        Route::post('/WebDefacement-website/get_check_image_screenshot', 'WebDefacementController@get_check_image_screenshot')->name('webdefacement.get_check_image_screenshot')->middleware('can:menu_items');
        Route::post('/WebDefacement-website/save_data', 'WebDefacementController@WebDefacement_create_data')->name('webdefacement.create_data')->middleware('can:menu_items');
        Route::get('/WebDefacement-website/edit_data/{id}', 'WebDefacementController@WebDefacement_edit_data')->name('webdefacement.edit_data')->middleware('can:menu_items');
        Route::post('/WebDefacement-website/update_data', 'WebDefacementController@WebDefacement_update_data')->name('webdefacement.update_data')->middleware('can:menu_items');
        Route::get('/WebDefacement-server/{id}', 'WebDefacementController@webdefacement_server')->name('webdefacement_server.index')->middleware('can:menu_items');
        Route::post('/WebDefacement-save_item', 'WebDefacementController@save_item')->name('webdefacement_website.save_item')->middleware('can:menu_items');
        Route::post('/WebDefacement-get_image_data', 'WebDefacementController@get_image_data')->name('webdefacement_website.get_image_data')->middleware('can:menu_items');
        Route::post('/WebDefacement-remove_item', 'WebDefacementController@remove_item')->name('webdefacement_website.remove_item')->middleware('can:menu_items');
        Route::post('/WebDefacement-update_item_top_left', 'WebDefacementController@update_item_top_left')->name('webdefacement_website.update_item_top_left')->middleware('can:menu_items');
        Route::post('/WebDefacement-update_item_width_height', 'WebDefacementController@update_item_width_height')->name('webdefacement_website.update_item_width_height')->middleware('can:menu_items');
        // Route::post('/webdefacement/detail/update_original', 'WebDefacementController@update_original')->name('webdefacement.update_original')->middleware('can:menu_items');
        // Route::post('/webdefacement/detail/update_image', 'WebDefacementController@update_image')->name('webdefacement.update_image')->middleware('can:menu_items');
    }
);

// Compromised
Route::group(
    ['middleware' => ['web', 'permission:role_center', 'permission:compromised']],
    function () {
        Route::get('/compromised_feed/{code}', 'DataLeakController@compromised_feed')->name('compromised_feed.index')->middleware('can:menu_items');
        Route::get('/compromised_data/{code}', 'DataLeakController@compromised_data')->name('compromised_data.index')->middleware('can:menu_items');
        Route::post('/compromised_feed/compromised_feed_datatables', 'DataLeakController@compromised_feed_datatables')->name('compromised_feed.compromised_feed_datatables')->middleware('can:menu_items');
        Route::post('/compromised_feed/approve/dataFeed', 'DataLeakController@compromised_feed_approve_data_feed')->name('compromised_feed.approve_data_feed');
        Route::post('/compromised_feed/cancle/dataFeed', 'DataLeakController@compromised_feed_cancle_data_feed')->name('compromised_feed.cancle_data_feed');
        Route::post('/compromised_feed/compromised_feed_darkweb_all_site_tb', 'DataLeakController@compromised_feed_darkweb_all_site_tb')->name('compromised_feed.darkweb_all_site_tb');
        Route::post('compromised_feed/compromised_feed_change_status', 'DataLeakController@compromised_feed_change_status')->name('compromised_feed.change_status')->middleware('can:menu_items');
        Route::post('/compromised_feed/compromised_feed_delete_select', 'DataLeakController@compromised_feed_delete_select')->name('compromised_feed.delete_select_process');
        Route::get('/compromised_feed/delete_compromised_feed_modal/{code}', 'DataLeakController@delete_compromised_feed_modal')->name('darkweb.delete_compromised_feed_modal');
        Route::post('/compromised_feed/delete_compromised_feed_process/{code}', 'DataLeakController@delete_compromised_feed_process')->name('compromised_feed.delete_compromised_feed_process');
        Route::get('/compromised_web_server/{code}', 'DataLeakController@compromised_web_server')->name('compromised_web_server.index')->middleware('can:menu_items');
        Route::post('/compromised_web_server/table_web_server', 'DataLeakController@table_web_server')->name('compromised_web_server.table_web_server')->middleware('can:menu_items');
        Route::post('/compromised_web_server/web_server_delete', 'DataLeakController@web_server_delete')->name('compromised_web_server.web_server_delete')->middleware('can:menu_items');
        Route::get('/compromised_web_server/web_server_edit_modal/{code}', 'DataLeakController@web_server_edit_modal')->name('compromised_web_server.web_server_edit_modal')->middleware('can:menu_items');
        Route::post('/compromised_web_server/web_server_edit/{id}', 'DataLeakController@web_server_edit')->name('compromised_web_server.web_server_edit')->middleware('can:menu_items');
        Route::post('/compromised_web_server/web_server_create', 'DataLeakController@web_server_create')->name('compromised_web_server.web_server_create')->middleware('can:menu_items');
        Route::post('/compromised_web_server/web_server_change_status', 'DataLeakController@web_server_change_status')->name('compromised_web_server.web_server_change_status')->middleware('can:menu_items');

        Route::post('/compromised_web_server_ip/checkwebserverIP', 'DataLeakController@checkWebserverIP')->name('compromised_web_server.checkWebserverIP')->middleware('can:menu_items');
        Route::post('/compromised_web_server/web_server_add_user', 'DataLeakController@web_server_add_user')->name('compromised_web_server.web_server_add_user')->middleware('can:menu_items');
        Route::post('/compromised_web_server_ip/load_data_connection', 'DataLeakController@load_data_connection')->name('compromised_web_server.load_data_connection')->middleware('can:menu_items');
    }
);

// credentials
Route::group(
    ['middleware' => ['web', 'permission:role_center']],
    function () {
        Route::post('modal_create_credentials', 'SiteSettingsController@modal_create_credentials')->name('SiteSettingsController.modal_create_credentials')->middleware('can:menu_items');
        Route::get('/Credentials/{id}', 'CredentialsController@index')->name('credentials.index')->middleware('can:menu_items');
        Route::post('/Credentials/create_credentials', 'CredentialsController@create_credentials')->name('credentials.create_credentials')->middleware('can:menu_items');
        Route::post('/Credentials/table_credentials', 'CredentialsController@table_credentials')->name('credentials.table_credentials');
        Route::post('/Credentials/credentials_delete', 'CredentialsController@credentials_delete')->name('credentials.credentials_delete')->middleware('can:menu_items');
        Route::get('/Credentials/credentials_edit_modal/{code}', 'CredentialsController@credentials_edit_modal')->name('credentials.credentials_edit_modal')->middleware('can:menu_items');
        Route::post('/Credentials/credentials_edit', 'CredentialsController@credentials_edit')->name('credentials.credentials_edit')->middleware('can:menu_items');
        Route::post('/Credentials/credentials_change_status', 'CredentialsController@credentials_change_status')->name('credentials.credentials_change_status')->middleware('can:menu_items');  
    }
);

// dataleak
Route::group(
    ['middleware' => ['web', 'permission:role_center', 'permission:data_leak']],
    function () {
        Route::get('/datafeedsocial', 'DataLeakController@datafeed')->name('datafeed.index')->middleware(['can:menu_items','permission:data_leak','permission:role_center']);
        Route::post('/datafeedsocial/datatables', 'DataLeakController@datafeedsocial_datatables')->name('socialdatas.datafeedsocial_datatables');
        Route::post('/datafeedsocial/approve/dataFeed', 'DataLeakController@approve_data_feed')->name('socialdatas.approve_data_feed');
        Route::post('/datafeedsocial/cancle/dataFeed', 'DataLeakController@cancle_data_feed')->name('socialdatas.cancle_data_feed');
        Route::get('/socialdatas/delete_dataleakdata_modal/{code}', 'DataLeakController@delete_dataleakdata_modal')->name('socialdatas.delete_dataleakdata_modall');
        Route::get('/socialdatas/delete_dataleakdata/{code}', 'DataLeakController@delete_dataleakdata')->name('socialdatas.delete_dataleakdata');
        Route::post('/socialdatas/delete_dataleakdata/{code}', 'DataLeakController@delete_dataleakdata')->name('socialdatas.delete_dataleakdata');
        Route::post('/socialdatas/change_status_dataleakdata', 'DataLeakController@change_status_dataleakdata')->name('socialdatas.change_status_dataleakdata');
        Route::post('/socialdatas/change_delete_dataleakdata', 'DataLeakController@change_delete_dataleakdata')->name('socialdatas.change_delete_dataleakdata');
    }
);
// Compromise
Route::group(
    ['middleware' => ['web', 'permission:role_center', 'permission:compromised']],
    function () {
        Route::get('/datafeed_darkweb', 'DataLeakController@datafeed_darkweb')->name('datafeed.darkweb_index')->middleware(['can:menu_items','permission:compromised','permission:role_center']);
        Route::post('/datafeed_darkweb/get_data_feed', 'DataLeakController@get_data_feed')->name('socialdatas.get_data_feed');
        Route::post('/datafeed_darkweb/approve_compromised_feed', 'DataLeakController@approve_compromised_feed')->name('socialdatas.approve_compromised_feed');
        Route::post('/datafeed_darkweb/dark_web_datatables', 'DataLeakController@dark_web_datatables')->name('socialdatas.dark_web_datatables');
        Route::post('/datafeed_darkweb/datatables', 'DataLeakController@datafeed_darkweb_datatables')->name('datafeed.darkweb_datatables');
        Route::get('/darkweb_data/delete_darkwebdata_modal/{code}', 'DataLeakController@delete_darkwebdata_modal')->middleware(['can:menu_items','permission:compromised']);
        Route::post('/darkweb/delete_darkwebdata_process/{code}', 'DataLeakController@delete_darkwebdata_process')->name('darkweb.delete_darkwebdata_process');
        Route::post('/darkweb/delete_select_process', 'DataLeakController@delete_darkweb_select_process')->name('darkweb.delete_select_process');
        Route::post('sitesettings/darkweb_data_change_status', 'DataLeakController@darkweb_data_change_status')->name('DataLeakController.darkweb_data_change_status')->middleware(['can:menu_items','permission:compromised']);
    }
);

Route::get('/socialdatas', 'DataLeakController@socialdatas_all_site')->name('socialdatas.index_all_site')->middleware(['can:menu_items','permission:data_leak']);
Route::get('/darkweb-datas', 'DataLeakController@darkweb_datas_all_site')->name('darkweb.index_all_site')->middleware(['can:menu_items','permission:compromised']);
Route::post('socialdatas_all_site_tb', 'DataLeakController@socialdatas_all_site_tb')->name('socialdatas.socialdatas_all_site_tb')->middleware(['can:menu_items','permission:data_leak']);
Route::post('darkweb_all_site_tb', 'DataLeakController@darkweb_all_site_tb')->name('socialdatas.darkweb_all_site_tb')->middleware(['can:menu_items','permission:compromised']);

Route::get('/vulnerability_assets/getSelectedVendor', 'VulnerabilityController@get_selected_vendor_detail')->name('vul_assets.selected_vendor')->middleware('can:menu_items');
Route::get('/vulnerability_assets/detail', 'VulnerabilityController@vulassets_details')->name('vulsetting.detail')->middleware('can:menu_items');

// View Content
Route::get('/socialdatas/view_content/{code}', 'DataLeakController@view_dataleak_modal')->name('socialdatas.view_content_dataleak')->middleware(['can:menu_items','permission:data_leak']);
Route::get('/darkweb_data/view_content/{code}', 'DataLeakController@view_compromise_modal')->name('socialdatas.view_content_compromise')->middleware(['can:menu_items','permission:compromised']);

Route::get('/check_cookie_site', 'SystemSettingsController@check_cookie_site')->name('systemsetting.check_cookie_site')->middleware('can:menu_items');