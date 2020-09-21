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
Route::group(
    ['middleware' => 'web', 'prefix' => 'categorysettings'],
    function () {
        Route::get('/', 'CategorySettingsController@index')->name('categorysettings.index')->middleware('can:menu_items');
        Route::get('data', 'CategorySettingsController@tableData')->name('categorysettings.data')->middleware('can:menu_items');
        Route::get('create', 'CategorySettingsController@create')->name('categorysettings.create')->middleware('can:settings');
        Route::post('bulk-delete', 'CategorySettingsController@bulkDelete')->name('categorysettings.bulk.delete')->middleware(['can:categorysettings_delete']);//->middleware(['can:categorysettings_delete', 'demo']);
        Route::post('change_status', 'CategorySettingsController@change_status')->name('categorysettings.change_status')->middleware(['can:categorysettings_update']);
        Route::get('edit-categorysettings/{id}', 'CategorySettingsController@edit')->name('categorysettings.edit')->middleware('can:categorysettings_update');
        Route::get('delete-categorysettings/{id}', 'CategorySettingsController@delete')->name('categorysettings.delete')->middleware('can:categorysettings_delete');
        // Route::get('/test', 'CategorySettingsController@test')->name('categorysettings.test')->middleware('can:menu_items');
    }
);
