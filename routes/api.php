<?php

use App\Http\Controllers\SendSmsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SmsWebhookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/send-sms', [SendSmsController::class, 'handle'])->middleware('auth:sanctum');
Route::post('/sms-webhook', [SmsWebhookController::class, 'handle']);
