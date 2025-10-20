<?php

use Illuminate\Support\Facades\Route;

Route::post('/protected', function () {
    return response()->json(['message' => 'Access granted']);
})->middleware('personal-token');

Route::post('/protected-typed', function () {
    return response()->json(['message' => 'Typed access granted']);
})->middleware('personal-token:test-type');
