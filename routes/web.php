<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name'    => 'Note Taking API',
        'version' => 'v1',
        'docs'    => '/api/v1',
    ]);
});
