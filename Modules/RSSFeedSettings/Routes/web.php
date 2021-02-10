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
    ['middleware' => ['web', 'permission:news', 'permission:role_center'], 'prefix' => 'rssfeedsettings'],
    function () {
        Route::get('/', 'RSSFeedSettingsController@index')->name('rssfeedsettings.index')->middleware('can:menu_items');
        Route::get('/rss_logs', 'RSSFeedSettingsController@rss_logs')->name('rssfeedsettings.rss_logs')->middleware('can:menu_items');

        Route::get('/rss_data', 'RSSFeedSettingsController@rss_data')->name('rssfeedsettings.rss_data')->middleware('can:menu_items');
        Route::post('rss_data_table', 'RSSFeedSettingsController@tableRssData')->name('rssfeedsettings.rss_data_table')->middleware('can:menu_items');

        Route::post('rss_setting_table', 'RSSFeedSettingsController@tableRssSetting')->name('rssfeedsettings.rss_setting_table')->middleware('can:menu_items');
        
        Route::get('delete-rss_data/{id}', 'RSSFeedSettingsController@rss_data_delete')->name('rssfeedsettings.rss_data_delete')->middleware('can:menu_items');
        Route::get('delete-rss_news/{id}', 'RSSFeedSettingsController@rss_news_delete')->name('rssfeedsettings.rss_news_delete')->middleware('can:menu_items');
        Route::post('delete_checked', 'RSSFeedSettingsController@deleteChecked')->name('RSSFeedSettingsController.delete_checked');
        Route::get('/rss_data/news/create/{code}', 'RSSFeedSettingsController@rss_data_create_news')->name('rssfeedsettings.rss_data_create_news');
        Route::get('/rss_news/news/edit/{code}', 'RSSFeedSettingsController@rss_news_edit_news')->name('rssfeedsettings.rss_news_edit_news');
        Route::get('/rss_news/news/create', 'RSSFeedSettingsController@rss_news_create_news')->name('rssfeedsettings.rss_news_create_news');
        Route::post('rss_data/news/store', 'RSSFeedSettingsController@rss_data_store_news')->name('rssfeedsettings.rss_data_store_news');
        Route::post('/rss_data/tags', 'RSSFeedSettingsController@rss_data_tags')->name('rssfeedsettings.rss_data_tags');
        Route::post('/rss_data/topic', 'RSSFeedSettingsController@rss_data_topics')->name('rssfeedsettings.rss_data_topic');
        Route::delete('rss_data_delete_process/{id}', 'RSSFeedSettingsController@rss_data_delete_process')->name('rssfeedsettings.rss_data_delete_process')->middleware('can:menu_items');
        Route::delete('rss_news_delete_process/{id}', 'RSSFeedSettingsController@rss_news_delete_process')->name('rssfeedsettings.rss_news_delete_process')->middleware('can:menu_items');
        Route::post('rss_news_delete_change', 'RSSFeedSettingsController@rss_news_delete_change')->name('rssfeedsettings.rss_news_delete_change')->middleware('can:menu_items');
        Route::post('rss_feed_seting_delete', 'RSSFeedSettingsController@rss_feed_seting_delete')->name('rssfeedsettings.rss_feed_seting_delete')->middleware('can:menu_items');
        Route::post('/rss_data/news/preview', 'RSSFeedSettingsController@rss_data_preview_news')->name('rssfeedsettings.rss_data_preview_news');
        Route::post('rssfeedsettings/change_status', 'RSSFeedSettingsController@change_status')->name('rssfeedsettings.change_status')->middleware('can:menu_items');
        Route::get('delete-rssfeedsettings/{id}', 'RSSFeedSettingsController@delete')->name('rssfeedsettings.delete')->middleware('can:menu_items');
        Route::get('edit-rssfeedsettings/{id}', 'RSSFeedSettingsController@edit')->name('rssfeedsettings.edit')->middleware('can:menu_items');
        Route::post('rssfeedsettings', 'RSSFeedSettingsController@store')->name('rssfeedsettings.save')->middleware('can:menu_items');
        Route::put('rssfeedsettings/{id}', 'RSSFeedSettingsController@update')->name('rssfeedsettings.update')->middleware('can:menu_items');
        Route::delete('rssfeedsettings/{id}', 'RSSFeedSettingsController@delete_process')->name('rssfeedsettings.delete_process')->middleware('can:menu_items');


        
        Route::get('/setting', 'RSSFeedSettingsController@rss_setting')->name('rssfeedsettings.rss_setting')->middleware('can:menu_items');
        Route::get('/feed-all', 'RSSFeedSettingsController@rss_feed_all')->name('rssfeedsettings.feed_all')->middleware('can:menu_items');
        
        //News
        Route::get('/news', 'RSSFeedSettingsController@rss_news')->name('rssfeedsettings.news')->middleware('can:menu_items');
        Route::post('/news/rss_news_delete_process/{id}', 'RSSFeedSettingsController@rss_news_delete_process')->name('rssfeedsettings.rss_news_delete_process')->middleware('can:menu_items');
        Route::post('/news/rss_news_delete_select', 'RSSFeedSettingsController@rss_news_delete_select')->name('rssfeedsettings.rss_news_delete_select')->middleware('can:menu_items');
        Route::post('rss_news_table', 'RSSFeedSettingsController@tableNews')->name('rssfeedsettings.rss_news_table')->middleware('can:menu_items');
        Route::post('rssfeedsettings/change_status_news', 'RSSFeedSettingsController@change_status_news')->name('rssfeedsettings.change_status_news')->middleware('can:menu_items');
        Route::post('/rss_data/source', 'RSSFeedSettingsController@rss_data_source')->name('rssfeedsettings.rss_data_source');
        Route::post('rss_data/news/store/create', 'RSSFeedSettingsController@rss_data_store_news_create')->name('rssfeedsettings.rss_data_store_news_create');

        Route::post('rss_data/load_top_source', 'RSSFeedSettingsController@load_top_source')->name('rssfeedsettings.load_top_source')->middleware('can:menu_items');
        Route::post('rss_data/load_top_category', 'RSSFeedSettingsController@load_top_category')->name('rssfeedsettings.load_top_category')->middleware('can:menu_items');
    }
);
