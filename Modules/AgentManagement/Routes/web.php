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
		
		Route::get('/agent_modal_control_agent', 'AgentManagementController@agent_modal_control_agent')->name('agentmanagement.agent_modal_control_agent');
		Route::get('/agent_modal_manage_rule', 'AgentManagementController@agent_modal_manage_rule')->name('agentmanagement.agent_modal_manage_rule');
		
		Route::post('/agent_delete', 'AgentManagementController@agent_delete')->name('agentmanagement.agent_delete');
		
		Route::post('/agent_delete', 'AgentManagementController@agent_delete')->name('agentmanagement.agent_delete');
	}
);

Route::group(
	['middleware' => 'web'],
	function () {
		Route::get('/tbl_category_rule', 'AgentManagementController@tbl_category_rule')->name('agentmanagement.tbl_category_rule');
		Route::get('/tbl_extention_rule', 'AgentManagementController@tbl_extention_rule')->name('agentmanagement.tbl_extention_rule');
		Route::post('/extension_insert', 'AgentManagementController@extension_insert')->name('agentmanagement.extension_insert');
		Route::post('/category_insert', 'AgentManagementController@category_insert')->name('agentmanagement.category_insert');
		Route::get('/category_edit', 'AgentManagementController@category_edit')->name('agentmanagement.category_edit');
		Route::post('/category_update', 'AgentManagementController@category_update')->name('agentmanagement.category_update');
		Route::get('/modal_category_delete', 'AgentManagementController@modal_category_delete')->name('agentmanagement.modal_category_delete');
		Route::post('/category_delete', 'AgentManagementController@category_delete')->name('agentmanagement.category_delete');
		Route::post('/status_category_update', 'AgentManagementController@status_category_update')->name('agentmanagement.status_category_update');
		Route::post('/status_rule_update', 'AgentManagementController@status_rule_update')->name('agentmanagement.status_rule_update');
		Route::get('/get_select_category_rule', 'AgentManagementController@get_select_category_rule')->name('agentmanagement.get_select_category_rule');
		Route::get('/agent_rule', 'AgentManagementController@agent_rule')->name('agentmanagement.agent_rule');
		Route::get('/agent_rule_tbl_all_rule', 'AgentManagementController@agent_rule_tbl_all_rule')->name('agentmanagement.agent_rule_tbl_all_rule');
		Route::post('/agent_rule_insert', 'AgentManagementController@agent_rule_insert')->name('agentmanagement.agent_rule_insert');
		Route::get('/agent_rule_edit', 'AgentManagementController@agent_rule_edit')->name('agentmanagement.agent_rule_edit');
		Route::post('/agent_rule_update', 'AgentManagementController@agent_rule_update')->name('agentmanagement.agent_rule_update');
		Route::post('/agent_rule_get_zip', 'AgentManagementController@agent_rule_get_zip')->name('agentmanagement.agent_rule_get_zip');
		Route::post('/add_new_category', 'AgentManagementController@add_new_category')->name('agentmanagement.add_new_category');

		Route::get('/get_rule_site', 'AgentManagementController@get_rule_site')->name('agentmanagement.get_rule_site');
		Route::post('/check_insert_rule_process', 'AgentManagementController@check_insert_rule_process')->name('agentmanagement.check_insert_rule_process');
		Route::post('/delete_rule_site', 'AgentManagementController@delete_rule_site')->name('agentmanagement.delete_rule_site');
	}
);