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
    ['middleware' => ['web', 'permission:indicators'], 'prefix' => 'indicators'],
    function () {
        Route::get('/detail', 'IndicatorsController@show_detail_indicator')->name('indicators.detail_indicator')->middleware('can:menu_items');

        Route::get('/events', 'IndicatorsController@events')->name('indicators.events')->middleware('can:menu_items');
        Route::get('/events/events_detail', 'IndicatorsController@events_detail')->name('indicators.events_detail')->middleware('can:menu_items');
        Route::get('/events/events_detail/{id}', 'IndicatorsController@events_detail_select')->name('indicators.events_detail_select')->middleware('can:menu_items');


        Route::get('/detail_malware', 'IndicatorsController@show_detail_malware')->name('indicators.detail_malware')->middleware('can:menu_items');
        Route::get('/detail_adversary/{adversary_uuid}', 'IndicatorsController@show_detail_adversary')->name('indicators.detail_adversary')->middleware('can:menu_items');

        Route::post('/related_event', 'IndicatorsController@related_event')->name('indicators.related_event');
        

        Route::get('/attributes', 'IndicatorsController@attributes')->name('indicators.attributes')->middleware('can:menu_items');
        // Route::get('/details', 'IndicatorsController@show_detail_indicators')->name('indicators.detail_indicators')->middleware('can:menu_items');
        Route::get('/LoadMoreOTX', 'IndicatorsController@LoadMoreOTX')->name('LoadMoreOTX');

        Route::get('/load/general', 'IndicatorsController@load_general')->name('indicators.load_general');
        Route::post('/load/pulses', 'IndicatorsController@load_relatedPulse')->name('indicators.load_pulses');
        Route::get('/load/pulses_tb', 'IndicatorsController@load_relatedPulse_tb')->name('indicators.load_pulses_tb');
        Route::post('events_table', 'IndicatorsController@datatableEvent')->name('indicators.events_table')->middleware('can:menu_items');
        Route::get('table_summary', 'IndicatorsController@table_summary')->name('indicators.table_summary');
        Route::get('table_summary_export', 'IndicatorsController@table_summary_export')->name('indicators.table_summary_export');
        Route::post('events_attributes_table', 'IndicatorsController@load_attributes_tb')->name('indicators.events_attributes_table')->middleware('can:menu_items');
        // Route::post('events_pulse_table', 'IndicatorsController@events_pulse_table')->name('indicators.events_pulse_table')->middleware('can:menu_items');
        Route::post('events_pulse_table', 'IndicatorsController@load_pulse_tb')->name('indicators.events_pulse_table')->middleware('can:menu_items');
        Route::post('load_adversary_tb', 'IndicatorsController@load_adversary_tb')->name('indicators.load_adversary_tb')->middleware('can:menu_items');
        Route::post('load_malware_tb', 'IndicatorsController@load_malware_tb')->name('indicators.load_malware_tb')->middleware('can:menu_items');
        Route::post('events_table_search', 'IndicatorsController@datatableEvent_search')->name('indicators.events_table_search')->middleware('can:menu_items');
        Route::get('/count_view_event', 'IndicatorsController@count_view')->name('indicators.count_view_event');

        Route::get('/groups/{id}', 'IndicatorsController@link_group')->name('indicators.link_group')->middleware('can:menu_items');
        Route::post('/groups/table_groups', 'IndicatorsController@table_groups')->name('indicators.table_groups')->middleware('can:menu_items');

        Route::get('/tags/{id}', 'IndicatorsController@link_tags')->name('indicators.link_tags')->middleware('can:menu_items');
        Route::post('/tags/table_tags', 'IndicatorsController@table_tags')->name('indicators.table_tags')->middleware('can:menu_items');
        Route::post('/tags/save_table_tags', 'IndicatorsController@save_table_tags')->name('indicators.save_table_tags')->middleware('can:menu_items');
        Route::post('/select_techniques', 'IndicatorsController@select_techniques')->name('indicators.select_techniques')->middleware('can:menu_items');
        Route::get('/industries', 'IndicatorsController@indicator_industries')->name('indicators.industries')->middleware('can:menu_items');
        Route::get('/group', 'IndicatorsController@indicator_group')->name('indicators.indicator_group')->middleware('can:menu_items');

        Route::get('/indicator_insert_tag', 'IndicatorsController@insert_tag')->name('indicators.modal_tag')->middleware('can:menu_items');
        
        Route::get('/adversaries', 'IndicatorsController@adversaries')->name('indicators.adversaries');
        Route::get('/malware', 'IndicatorsController@malware')->name('indicators.malware');

        Route::get('/iframe', 'IndicatorsController@iframe')->name('indicators.iframe');


        Route::get('/newGroups', 'IndicatorsController@new_link_group')->middleware('can:menu_items');
        Route::get('/newTags', 'IndicatorsController@new_link_tags')->middleware('can:menu_items');
    }
);
