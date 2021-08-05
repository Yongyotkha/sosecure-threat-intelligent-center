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
	}
);