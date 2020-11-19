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
    ['middleware' => 'web', 'prefix' => 'sitesettings'],
    function () {
        Route::get('/', 'SiteSettingsController@index')->name('sitesettings.index')->middleware('can:menu_items');
        Route::get('/test_mongo', 'SiteSettingsController@test_mongo')->name('sitesettings.test_mongo')->middleware('can:menu_items');
        Route::get('/test_mongo2', 'SiteSettingsController@test_mongo2')->name('sitesettings.test_mongo2')->middleware('can:menu_items');
        Route::get('/phpinfo', 'SiteSettingsController@phpinfo')->name('sitesettings.phpinfo')->middleware('can:menu_items');
        Route::get('data', 'SiteSettingsController@tableData')->name('sitesettings.data')->middleware('can:menu_items');
        Route::get('create', 'SiteSettingsController@create')->name('sitesettings.create')->middleware('can:settings');
        Route::post('bulk-delete', 'SiteSettingsController@bulkDelete')->name('sitesettings.bulk.delete')->middleware(['can:sitesettings_delete']);//->middleware(['can:sitesettings_delete', 'demo']);
        Route::post('change_status', 'SiteSettingsController@change_status')->name('sitesettings.change_status')->middleware(['can:sitesettings_update']);
        Route::get('edit-sitesettings/{id}', 'SiteSettingsController@edit')->name('sitesettings.edit')->middleware('can:sitesettings_update');
        Route::get('delete-sitesettings/{id}', 'SiteSettingsController@delete')->name('sitesettings.delete')->middleware('can:sitesettings_delete');
        Route::post('sitesettings', 'SiteSettingsController@store')->name('sitesettings.save')->middleware('can:sitesettings_create');
        Route::put('sitesettings/{id}', 'SiteSettingsController@update')->name('sitesettings.update.settings')->middleware('can:sitesettings_update');
        Route::post('sitesettings/change_status', 'SiteSettingsController@change_status')->name('sitesettings.change_status.settings')->middleware('can:sitesettings_update');
        // Route::get('/test', 'SiteSettingsController@test')->name('sitesettings.test')->middleware('can:menu_items');

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
        Route::post('/domain-setting/domain/save/{code}', 'DomainSettingsController@save')->name('domain.save')->middleware('can:categorysettings_create');//->middleware('can:categorysettings_create')
        Route::post('/domain-setting/domain/change_status', 'DomainSettingsController@change_status')->name('domainsettings.change_status')->middleware(['can:categorysettings_update']);
        Route::post('/domain-setting/domain/bulk-delete', 'DomainSettingsController@bulkDelete')->name('domainsettings.bulk.delete')->middleware(['can:categorysettings_delete']);//->middleware(['can:categorysettings_delete', 'demo']);
        Route::get('/domain-setting/domain/data', 'DomainSettingsController@tableData')->name('domainsettings.data')->middleware('can:menu_items');
        Route::get('/domain-setting/domain/test', 'DomainSettingsController@test')->name('domainsettings.test')->middleware('can:menu_items');
        Route::get('/domain-setting/domain/edit/{id}', 'DomainSettingsController@edit')->name('domainsettings.edit')->middleware('can:categorysettings_update');
        Route::get('/domain-setting/domain/delete/{id}', 'DomainSettingsController@delete')->name('domainsettings.delete')->middleware('can:categorysettings_delete');
        Route::put('/domain-setting/domain/update/{id}', 'DomainSettingsController@update')->name('domainsettings.update')->middleware('can:sitesettings_update');
        Route::delete('/domain-setting/domain/delete_process/{id}', 'DomainSettingsController@delete_process')->name('domainsettings.delete_process')->middleware('can:categorysettings_delete');

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
   
        Route::get('/vulnerability_logs/{id}', 'VulnerabilityController@index')->name('vulsetting.vul_logs')->middleware('can:menu_items');

        Route::get('/vulnerability_assets/{id}', 'VulnerabilityController@vulassets')->name('vulsetting.vul_assets')->middleware('can:menu_items');
        Route::get('/vulnerability_assets/cve_assets/data', 'VulnerabilityController@tableData')->name('cve_assets.data')->middleware('can:menu_items');
    }
);

Route::get('/vulnerability_assets/detail', 'VulnerabilityController@vulassets_details')->name('vulsetting.detail')->middleware('can:menu_items');
