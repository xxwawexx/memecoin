<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MemeCoinController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post(
    '/memecoin/generate-name', [MemeCoinController::class, 'generate']
)->middleware(['auth:sanctum', 'throttle:20,1']);
