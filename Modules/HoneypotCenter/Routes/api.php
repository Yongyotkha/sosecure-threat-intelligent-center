<?php

Route::post('v1/honeypot/ingest', 'HoneypotIngestController@store')
    ->middleware([
        'honeypot.agent',
        'throttle:' . config('honeypot.api.rate_limit', 120) . ',1',
    ]);
