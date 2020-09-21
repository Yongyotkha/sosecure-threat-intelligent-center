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
        Route::get('data', 'SiteSettingsController@tableData')->name('sitesettings.data')->middleware('can:menu_items');
        Route::get('create', 'SiteSettingsController@create')->name('sitesettings.create')->middleware('can:settings');
        Route::post('bulk-delete', 'SiteSettingsController@bulkDelete')->name('sitesettings.bulk.delete')->middleware(['can:sitesettings_delete']);//->middleware(['can:sitesettings_delete', 'demo']);
        Route::post('change_status', 'SiteSettingsController@change_status')->name('sitesettings.change_status')->middleware(['can:sitesettings_update']);
        Route::get('edit-sitesettings/{id}', 'SiteSettingsController@edit')->name('sitesettings.edit')->middleware('can:sitesettings_update');
        Route::get('delete-sitesettings/{id}', 'SiteSettingsController@delete')->name('sitesettings.delete')->middleware('can:sitesettings_delete');
        // Route::get('/test', 'SiteSettingsController@test')->name('sitesettings.test')->middleware('can:menu_items');
    }
);


