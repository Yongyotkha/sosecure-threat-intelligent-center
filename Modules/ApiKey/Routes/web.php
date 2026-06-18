<?php

Route::group(
    ['middleware' => 'web', 'prefix' => 'apikey'],
    function () {
        Route::get('/', 'ApiKeyController@index')->name('apikey.index')->middleware('can:menu_items');
        Route::post('/data', 'ApiKeyController@tableData')->name('apikey.data')->middleware('can:menu_items');
        Route::get('/provider/{type}', 'ApiKeyController@show')->name('apikey.show')->middleware('can:menu_items')->where('type', '.*');
        Route::post('/store', 'ApiKeyController@store')->name('apikey.store')->middleware('can:menu_items');
        Route::put('/provider/{type}', 'ApiKeyController@update')->name('apikey.update')->middleware('can:menu_items')->where('type', '.*');
        Route::delete('/provider/{type}', 'ApiKeyController@destroy')->name('apikey.destroy')->middleware('can:menu_items')->where('type', '.*');
    }
);
