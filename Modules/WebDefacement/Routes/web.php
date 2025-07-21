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
    ['middleware' => ['web', 'permission:web_defacement'], 'prefix' => 'webdefacement'],
    function () {
        Route::get('/', 'WebDefacementController@index')->name('webdefacement.index')->middleware('can:menu_items');
        Route::post('/', 'WebDefacementController@tbl_server')->name('webdefacement.tbl_server')->middleware('can:menu_items');
        Route::get('/detail/{code}', 'WebDefacementController@detail')->name('webdefacement.detail')->middleware('can:menu_items');
        Route::post('/load_card', 'WebDefacementController@load_card')->name('webdefacement.load_card')->middleware('can:menu_items');
        Route::post('/detail/change_status', 'WebDefacementController@change_status')->name('webdefacement.change_status')->middleware('can:menu_items');
        Route::post('/detail/update_original', 'WebDefacementController@update_original')->name('webdefacement.update_original')->middleware('can:menu_items');
        Route::post('/detail/update_image', 'WebDefacementController@update_image')->name('webdefacement.update_image')->middleware('can:menu_items');
        Route::post('/detail/update_original_detail', 'WebDefacementController@update_original_detail')->name('webdefacement.update_original_detail')->middleware('can:menu_items');

        Route::post('/detail/deface_now', 'WebDefacementController@deface_now')->name('webdefacement.deface_now')->middleware('can:menu_items');
        Route::post('/detail/deface_now_detail', 'WebDefacementController@deface_now_detail')->name('webdefacement.deface_now_detail')->middleware('can:menu_items');

        Route::post('webdefacement.get_code_site', 'WebDefacementController@get_code_site')->name('webdefacement.get_code_site')->middleware('can:menu_items');
    }

);
