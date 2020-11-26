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
    ['middleware' => 'web', 'prefix' => 'rssfeedsettings'],
    function () {
        Route::get('/', 'RSSFeedSettingsController@index')->name('rssfeedsettings.index')->middleware('can:menu_items');
        Route::get('/rss_logs', 'RSSFeedSettingsController@rss_logs')->name('rssfeedsettings.rss_logs')->middleware('can:menu_items');

        Route::get('/rss_data', 'RSSFeedSettingsController@rss_data')->name('rssfeedsettings.rss_data')->middleware('can:menu_items');
        Route::post('rss_data_table', 'RSSFeedSettingsController@tableRssData')->name('rssfeedsettings.rss_data_table')->middleware('can:menu_items');
        Route::get('/rss_data/news/create/{code}', 'RSSFeedSettingsController@rss_data_create_news')->name('rssfeedsettings.rss_data_create_news');
        Route::post('rss_data/news/store', 'RSSFeedSettingsController@rss_data_store_news')->name('rssfeedsettings.rss_data_store_news');
        Route::post('/rss_data/tags', 'RSSFeedSettingsController@rss_data_tags')->name('rssfeedsettings.rss_data_tags');

        Route::get('/setting', 'RSSFeedSettingsController@rss_setting')->name('rssfeedsettings.rss_setting')->middleware('can:menu_items');
        Route::get('/feed-all', 'RSSFeedSettingsController@rss_feed_all')->name('rssfeedsettings.feed_all')->middleware('can:menu_items');
        Route::get('/news', 'RSSFeedSettingsController@rss_news')->name('rssfeedsettings.news')->middleware('can:menu_items');
    }
);
