<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/timer-sync', function () {
    return response()->json([
        'server_time' => now()->timestamp * 1000 // milliseconds
    ]);
});