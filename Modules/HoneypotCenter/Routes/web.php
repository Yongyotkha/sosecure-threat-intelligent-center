<?php

Route::group([
    'middleware' => ['installed', 'auth', 'verified', '2fa'],
], function () {
    Route::get('/honeypot', 'HoneypotDashboardController@index')
        ->name('honeypotcenter.dashboard')
        ->middleware('can:menu_items');

    Route::get('/honeypot/data', 'HoneypotDashboardController@data')
        ->name('honeypotcenter.dashboard.data')
        ->middleware('can:menu_items');

    Route::get('/honeypot/data/{section}', 'HoneypotDashboardController@section')
        ->name('honeypotcenter.dashboard.section')
        ->where('section', 'stats|hourly|daily-trend|threat-types|top-paths|top-attackers|recent-logs')
        ->middleware('can:menu_items');

    Route::get('/honeypot/agents', 'HoneypotDashboardController@agents')
        ->name('honeypotcenter.agents')
        ->middleware('can:menu_items');

    Route::get('/honeypot/geoip/{ip}', 'HoneypotDashboardController@geoip')
        ->name('honeypotcenter.geoip')
        ->middleware('can:menu_items');
});
