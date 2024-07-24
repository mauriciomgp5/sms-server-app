<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

Route::get('/', function () {
    return redirect('painel-sms');
});

Route::get('/update-project', function () {
    Artisan::call('app:project-update');
    return "Project updated successfully!";
})->middleware('auth');
