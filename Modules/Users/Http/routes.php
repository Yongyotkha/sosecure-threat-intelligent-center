<?php

Route::group(
    ['middleware' => ['web', 'permission:manage_users'], 'prefix' => 'users', 'namespace' => 'Modules\Users\Http\Controllers'],
    function () {
        Route::get('/', 'UserCustomController@index')->name('users.index')->middleware('can:menu_items');

        Route::get('test', 'UserCustomController@test')->name('users.test')->middleware('can:menu_items');
        Route::get('edit/{user}', 'UserCustomController@edit')->name('users.edit')->middleware('can:menu_items');
        Route::get('edit_new_modal/{user}', 'UserCustomController@edit_new_modal')->name('users.edit_new_modal')->middleware('can:menu_items');
        Route::get('delete_new_modal/{user}', 'UserCustomController@delete_new_modal')->name('users.delete_new_modal')->middleware('can:menu_items');

        Route::get('permissions/{user}', 'UserCustomController@permissions')->name('users.permissions')->middleware('can:menu_items');
        Route::post('permissions/{user}', 'UserCustomController@changePermission')->name('users.changePermission')->middleware('can:menu_items');

        Route::get('permissions', 'PermController@index')->name('users.perm')->middleware('can:menu_items');
        Route::get('create-permission', 'PermController@create')->name('users.perm.create')->middleware('can:menu_items');
        Route::post('save-permission', 'PermController@save')->name('users.perm.save')->middleware('can:menu_items');
        Route::get('edit-permission/{perm}', 'PermController@edit')->name('users.perm.edit')->middleware('can:menu_items');
        Route::put('edit-permission/{perm}', 'PermController@update')->name('users.perm.update')->middleware('can:menu_items');
        Route::get('delete-permission/{perm}', 'PermController@delete')->name('users.perm.delete')->middleware('can:menu_items');
        Route::delete('destroy-permission/{perm}', 'PermController@destroy')->name('users.perm.destroy')->middleware('can:menu_items');

        Route::get('roles', 'RoleController@index')->name('users.roles')->middleware('can:menu_items');
        Route::get('create-role', 'RoleController@create')->name('users.roles.create')->middleware('can:menu_items');
        Route::get('edit-role/{role}', 'RoleController@edit')->name('users.roles.edit')->middleware('can:menu_items');
        Route::get('role-permission_role/{role}', 'RoleController@permission_role')->name('users.roles.permission_custom')->middleware('can:menu_items');
        Route::get('role-permission/{role}', 'RoleController@permission')->name('users.roles.permission')->middleware('can:menu_items');
        Route::post('role-permission_custom/{role}', 'RoleController@changePermission_custom')->name('users.roles.changePerm_custom')->middleware('can:menu_items');
        Route::post('role-permission/{role}', 'RoleController@changePermission')->name('users.roles.changePerm')->middleware('can:menu_items');
        Route::get('delete-role/{role}', 'RoleController@delete')->name('users.roles.delete')->middleware('can:menu_items');
        Route::post('roles', 'RoleController@save')->name('roles.save')->middleware('can:menu_items');
        Route::put('roles/{id}', 'RoleController@update')->name('roles.update')->middleware('can:menu_items');
        Route::delete('roles/{id}', 'RoleController@destroy')->name('roles.destroy')->middleware('can:menu_items');
        Route::post('roles/data_table', 'RoleController@data_table')->name('roles.data_table');

        Route::get('data', 'UserCustomController@tableData')->name('users.data')->middleware('can:menu_items');
        Route::post('del_user', 'UserCustomController@del_user')->name('users.del_user')->middleware('can:menu_items');
        Route::get('create', 'UserCustomController@create')->name('users.create')->middleware('can:menu_items');
        Route::post('get_site', 'UserCustomController@get_manu')->name('users.get_site')->middleware('can:menu_items');
        Route::get('view/{user}/{tab?}', 'UserCustomController@view')->name('users.view');
        Route::get('suspend/{user}', 'UserCustomController@suspend')->name('users.suspend')->middleware('can:menu_items');
        Route::get('delete/{user}', 'UserCustomController@delete')->name('users.delete')->middleware('can:menu_items');
        Route::post('bulk-delete', 'UserCustomController@bulkDelete')->name('users.bulk.delete')->middleware('can:menu_items');

        Route::get('export', 'UserCustomController@export')->name('users.export')->middleware('can:menu_items');

        Route::get('reminders', 'ProfileCustomController@reminders')->middleware('can:menu_items');

        Route::get('announcements', 'AnnouncementCustomController@index')->name('announcements.index')->middleware(['can:announcements_create']);
        Route::get('announcements/{id}', 'AnnouncementCustomController@edit')->name('announcements.update')->middleware(['can:announcements_update']);

        Route::get('/impersonate/{id}', 'UserCustomController@impersonate')->name('users.impersonate');
        Route::get('/stop', 'UserCustomController@stopImpersonate')->name('users.stopimpersonate');

        Route::get('reauthenticate', 'ReauthenticateController@reauthenticate')->name('users.reauthenticate')->middleware('can:menu_items');
        Route::post('reauthenticate', 'ReauthenticateController@processReauthenticate')->name('users.reauthenticate.process')->middleware('can:menu_items');

        Route::get('/new-token', 'UserCustomController@regenerateKey')->name('users.token');

        Route::get('/pin/{entity}/{module}', 'UserCustomController@pin')->name('users.pin');

        Route::get('gdpr-export', 'UserCustomController@gdprExport')->name('users.gdpr.export');
        Route::get('api-setup', 'ProfileCustomController@apiSetup')->name('users.api');

        Route::get('holiday/{status}', 'UserCustomController@holiday')->name('users.holiday')->middleware(['can:users_update']);

        Route::get('2fa/complete/{secret}', 'TwoFactorCustomController@complete')->name('users.2fa.complete')->middleware(['can:menu_items']);
        Route::get('2fa/disable', 'TwoFactorCustomController@disable')->name('users.2fa.disable')->middleware(['can:menu_items']);
        Route::get('2fa', 'TwoFactorCustomController@twoFactor')->name('users.2fa');

        Route::get('oauth/create-client', 'OauthController@oauthClient')->name('oauth.create.client');
        Route::get('oauth/new-token', 'OauthController@oauthToken')->name('oauth.refresh.token');
        Route::post('oauth/new-token', 'OauthController@tokenRecreate')->name('oauth.recreate.token');
        Route::get('oauth/edit-client/{id}', 'OauthController@oauthUpdateClient')->name('oauth.update.client');
        Route::get('oauth/delete-client/{id}', 'OauthController@oauthDeleteClient')->name('oauth.delete.client')->middleware(['can:menu_items']);

        Route::get('notifications/clear', 'NotificationCustomController@clearAlerts')->name('users.notifications.clear');
        Route::get('notifications', 'NotificationCustomController@notifications')->name('users.notifications');
        Route::get('preferences', 'NotificationCustomController@preferences')->name('notifications.preferences');
        Route::post('preferences', 'NotificationCustomController@savePreferences')->name('notifications.preferences.save')->middleware('can:menu_items');
    }
);

Route::group(
    ['middleware' => ['web'], 'prefix' => 'users', 'namespace' => 'Modules\Users\Http\Controllers'],
    function () {
        Route::get('profile', 'ProfileCustomController@profile')->name('users.profile')->middleware('can:menu_items');
        Route::post('change', 'ProfileCustomController@change')->name('users.change')->middleware('can:menu_items');
    }
);