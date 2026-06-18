<?php

use Illuminate\Http\Request;

Route::middleware('auth:api')->get('/apikey', function (Request $request) {
    return $request->user();
});
