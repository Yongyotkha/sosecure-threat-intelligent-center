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
		Route::post('/tb_alert', 'AgentManagementController@tb_alert')->name('agentmanagement.tb_alert');
		Route::post('/dudit_log_feed', 'AgentManagementController@dudit_log_feed')->name('agentmanagement.dudit_log_feed');
		Route::post('/count_head', 'AgentManagementController@count_head')->name('agentmanagement.count_head');
	}
);