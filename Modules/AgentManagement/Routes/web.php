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
		Route::get('/scan_history_threats', 'AgentManagementController@scan_history_threats')->name('agentmanagement.scan_history_threats');
		Route::get('/scan_history_files', 'AgentManagementController@scan_history_files')->name('agentmanagement.scan_history_files');
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
		Route::get('/agent_modal_view_log_data', 'AgentManagementController@agent_modal_view_log_data')->name('agentmanagement.agent_modal_view_log_data');
		Route::post('/update_control_agent', 'AgentManagementController@update_control_agent')->name('agentmanagement.update_control_agent');
		Route::get('/agent_modal_manage_rule/{id}', 'AgentManagementController@agent_modal_manage_rule')->name('agentmanagement.agent_modal_manage_rule');
		
		Route::post('/agent_delete', 'AgentManagementController@agent_delete')->name('agentmanagement.agent_delete');
		
		Route::post('/agent_delete', 'AgentManagementController@agent_delete')->name('agentmanagement.agent_delete');
		Route::post('/updateManageRule', 'AgentManagementController@updateManageRule')->name('agentmanagement.updateManageRule');

		Route::get('/export_tb_alert', 'AgentManagementController@export_excel_tb_alert')->name('agentmanagement.export_excel_tb_alert');
		Route::get('/export_tb_agent', 'AgentManagementController@export_excel_tb_agent')->name('agentmanagement.export_excel_tb_agent');

		Route::get('/agent_releases', 'AgentManagementController@agent_releases')->name('agentmanagement.agent_releases')->middleware('can:menu_items');
		Route::post('/agent_release_upload', 'AgentManagementController@agent_release_upload')->name('agentmanagement.agent_release_upload');
		Route::post('/agent_release_set_target', 'AgentManagementController@agent_release_set_target')->name('agentmanagement.agent_release_set_target');
		Route::post('/agent_release_toggle', 'AgentManagementController@agent_release_toggle')->name('agentmanagement.agent_release_toggle');
		Route::get('/agent_release_events', 'AgentManagementController@agent_release_events')->name('agentmanagement.agent_release_events');
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
		Route::post('/select_rule_all_master', 'AgentManagementController@select_rule_all_master')->name('agentmanagement.select_rule_all_master');
		Route::post('/delete_rule_all_site', 'AgentManagementController@delete_rule_all_site')->name('agentmanagement.delete_rule_all_site');
		Route::post('/check_insert_rule_process', 'AgentManagementController@check_insert_rule_process')->name('agentmanagement.check_insert_rule_process');
		Route::post('/delete_rule_site', 'AgentManagementController@delete_rule_site')->name('agentmanagement.delete_rule_site');
		Route::get('/get_extension_rule_site', 'AgentManagementController@get_extension_rule_site')->name('agentmanagement.get_extension_rule_site');
		Route::post('/update_site_extension', 'AgentManagementController@update_site_extension')->name('agentmanagement.update_site_extension');
		Route::get('/get_ssdeep_site', 'AgentManagementController@get_ssdeep_site')->name('agentmanagement.get_ssdeep_site');
		Route::post('/ssdeep_assign_site', 'AgentManagementController@ssdeep_assign_site')->name('agentmanagement.ssdeep_assign_site');
		Route::post('/ssdeep_unassign_site', 'AgentManagementController@ssdeep_unassign_site')->name('agentmanagement.ssdeep_unassign_site');
		Route::post('/rule_packs_requeue_site', 'AgentManagementController@rule_packs_requeue_site')->name('agentmanagement.rule_packs_requeue_site');
		Route::post('/ssdeep_assign_all_master', 'AgentManagementController@ssdeep_assign_all_master')->name('agentmanagement.ssdeep_assign_all_master');
		Route::post('/ssdeep_delete_all_site', 'AgentManagementController@ssdeep_delete_all_site')->name('agentmanagement.ssdeep_delete_all_site');
		Route::post('/ssdeep_pack_insert', 'AgentManagementController@ssdeep_pack_insert')->name('agentmanagement.ssdeep_pack_insert');
		Route::get('/ssdeep_pack_tbl', 'AgentManagementController@ssdeep_pack_tbl')->name('agentmanagement.ssdeep_pack_tbl');
		Route::post('/ssdeep_pack_status', 'AgentManagementController@ssdeep_pack_status')->name('agentmanagement.ssdeep_pack_status');
		Route::post('/ssdeep_pack_auto_distribute', 'AgentManagementController@ssdeep_pack_auto_distribute')->name('agentmanagement.ssdeep_pack_auto_distribute');
		Route::post('/ssdeep_pack_delete', 'AgentManagementController@ssdeep_pack_delete')->name('agentmanagement.ssdeep_pack_delete');
		Route::post('/ssdeep_pack_cleanup_duplicates', 'AgentManagementController@ssdeep_pack_cleanup_duplicates')->name('agentmanagement.ssdeep_pack_cleanup_duplicates');
		Route::get('/ssdeep_pack_get', 'AgentManagementController@ssdeep_pack_get')->name('agentmanagement.ssdeep_pack_get');
		Route::post('/ssdeep_pack_update', 'AgentManagementController@ssdeep_pack_update')->name('agentmanagement.ssdeep_pack_update');
		Route::post('/ssdeep_pack_bulk_update_sites', 'AgentManagementController@ssdeep_pack_bulk_update_sites')->name('agentmanagement.ssdeep_pack_bulk_update_sites');
		Route::get('/ssdeep_candidate_tbl', 'AgentManagementController@ssdeep_candidate_tbl')->name('agentmanagement.ssdeep_candidate_tbl');
		Route::post('/ssdeep_candidate_cleanup_duplicates', 'AgentManagementController@ssdeep_candidate_cleanup_duplicates')->name('agentmanagement.ssdeep_candidate_cleanup_duplicates');
	
		Route::get('/modal_agent_alert_delete', 'AgentManagementController@modal_agent_alert_delete')->name('agentmanagement.modal_agent_alert_delete');
		Route::post('/alert_delete_id', 'AgentManagementController@alert_delete_id')->name('agentmanagement.alert_delete_id');
	}
);