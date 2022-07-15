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

Route::prefix('brandabuse')->group(function() {
    Route::get('/index', 'BrandAbuseController@index');
    Route::post('/socialdatas/change_status_brandabusedata', 'BrandAbuseController@change_status_brandabusedata')->name('brandabuse_socialdatas.change_status_brandabusedata');

    Route::get('/brandabusefeedsocial', 'BrandAbuseController@brandabusefeed')->name('brandabusefeed.index')->middleware(['can:menu_items','permission:data_leak','permission:role_center']);
    Route::post('/brandabusefeedsocial/datatables', 'BrandAbuseController@brandabusefeedsocial_datatables')->name('brandabusefeed.brandabusefeedsocial_datatables');
    Route::post('/brandabusefeedsocial/approve/dataFeed', 'BrandAbuseController@approve_data_feed')->name('brandabusefeed.approve_data_feed');
    Route::post('/brandabusefeedsocial/cancle/dataFeed', 'BrandAbuseController@cancle_data_feed')->name('brandabusefeed.cancle_data_feed');
    
    Route::post('/count_val', 'BrandAbuseController@count_val')->name('brandabuse.count_val')->middleware('can:menu_items');
    Route::post('/count_icon', 'BrandAbuseController@count_icon')->name('brandabuse.count_icon')->middleware('can:menu_items');
});

Route::get('/brandabuse', 'BrandAbuseController@socialdatas_all_site')->name('brandabuse.index_all_site')->middleware(['can:menu_items','permission:data_leak']);
Route::post('/brandabuse/socialdatas_all_site_tb', 'BrandAbuseController@socialdatas_all_site_tb')->name('brandabuse.socialdatas_all_site_tb')->middleware(['can:menu_items','permission:data_leak']);

Route::get('/brandabuse/create', 'BrandAbuseController@create_brandabuse')->name('brandabuse.create')->middleware('can:menu_items');
Route::post('/brandabuse/create/add_brandabuse', 'BrandAbuseController@add_brandabuse')->name('brandabuse.add_brandabuse')->middleware('can:menu_items');
Route::get('/brandabuse/edit_brandabuse_modal/{code}', 'BrandAbuseController@edit_brandabuse_modal')->middleware('can:menu_items');
Route::post('/brandabuse/edit_brandabuse', 'BrandAbuseController@edit_brandabuse')->name('brandabuse.edit_brandabuse')->middleware('can:menu_items');
Route::get('/brandabuse/delete_brandabusedata_modal/{code}', 'BrandAbuseController@delete_brandabusedata_modal')->name('brandabuse_socialdatas.delete_brandabusedata_modall');
Route::get('/brandabuse/delete_brandabusedata/{code}', 'BrandAbuseController@delete_brandabusedata')->name('brandabuse_socialdatas.delete_brandabusedata');
Route::post('/brandabuse/delete_brandabusedata/{code}', 'BrandAbuseController@delete_brandabusedata')->name('brandabuse_socialdatas.delete_brandabusedata');

Route::post('/brandabuse/change_delete_brandabusedata', 'BrandAbuseController@change_delete_brandabusedata')->name('brandabuse_socialdatas.change_delete_brandabusedata');

Route::get('/brandabuse/activity_modal/{code}', 'BrandAbuseController@activity_brandabuse_modal')->name('brandabuse_socialdatas.activity_brandabuse_modal')->middleware(['can:menu_items','permission:data_leak']);
Route::post('/brandabuse/activity_save', 'BrandAbuseController@activity_save')->name('brandabuse.activity_save')->middleware('can:menu_items');
Route::get('/brandabuse/activity_history_reload', 'BrandAbuseController@activity_history_reload')->name('brandabuse.activity_history_reload')->middleware('can:menu_items');
Route::get('/brandabuse/activity_get_edit_data', 'BrandAbuseController@activity_get_edit_data')->name('brandabuse.activity_get_edit_data')->middleware('can:menu_items');
Route::delete('/brandabuse/activity_delete', 'BrandAbuseController@activity_delete')->name('brandabuse.activity_delete')->middleware('can:menu_items');
