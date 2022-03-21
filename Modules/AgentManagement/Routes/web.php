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
	['middleware' => 'web', 'prefix' => 'agent_management'],
	function () {
		Route::get('/', 'AgentManagementController@index')->name('agentmanagement.index')->middleware('can:menu_items');
		Route::get('/view_txt', 'AgentManagementController@view_txt')->name('agentmanagement.view_txt')->middleware('can:menu_items');
		Route::post('/tb_alert', 'AgentManagementController@tb_alert')->name('agentmanagement.tb_alert');
		Route::post('/tb_agent', 'AgentManagementController@tb_agent')->name('agentmanagement.tb_agent');
		Route::post('/tb_schedule', 'AgentManagementController@tb_schedule')->name('agentmanagement.tb_schedule');
		Route::post('/dudit_log_feed', 'AgentManagementController@dudit_log_feed')->name('agentmanagement.dudit_log_feed');
		Route::post('/count_head', 'AgentManagementController@count_head')->name('agentmanagement.count_head');
		Route::post('/data_chart_platform', 'AgentManagementController@data_chart_platform')->name('agentmanagement.data_chart_platform');
		Route::post('/data_chart_incident', 'AgentManagementController@data_chart_incident')->name('agentmanagement.data_chart_incident');
		Route::post('/data_chart_severity', 'AgentManagementController@data_chart_severity')->name('agentmanagement.data_chart_severity');
		Route::post('/data_chart_rule', 'AgentManagementController@data_chart_rule')->name('agentmanagement.data_chart_rule');
		Route::post('/data_chart_timeline', 'AgentManagementController@data_chart_timeline')->name('agentmanagement.data_chart_timeline');
		Route::post('/update_status_agent', 'AgentManagementController@update_status_agent')->name('agentmanagement.update_status_agent');
		Route::get('/agent_delete_modal', 'AgentManagementController@agent_delete_modal')->name('agentmanagement.agent_delete_modal');
		Route::post('/agent_delete', 'AgentManagementController@agent_delete')->name('agentmanagement.agent_delete');
		
		Route::post('/agent_delete', 'AgentManagementController@agent_delete')->name('agentmanagement.agent_delete');
	}
);

Route::group(
	['middleware' => 'web'],
	function () {
		Route::get('/agent_rule', 'AgentManagementController@agent_rule')->name('agentmanagement.agent_rule');
		Route::get('/agent_rule_tbl_all_rule', 'AgentManagementController@agent_rule_tbl_all_rule')->name('agentmanagement.agent_rule_tbl_all_rule');
		Route::post('/agent_rule_insert', 'AgentManagementController@agent_rule_insert')->name('agentmanagement.agent_rule_insert');
		Route::post('/agent_rule_get_zip', 'AgentManagementController@agent_rule_get_zip')->name('agentmanagement.agent_rule_get_zip');
	}
);