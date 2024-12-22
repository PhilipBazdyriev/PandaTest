<?php

use App\Http\Controllers\OlxTrackingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
*/

Route::post('olx-tracking/subscribe-product', [OlxTrackingController::class, 'subscribeProduct']);
