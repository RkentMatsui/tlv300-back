<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\whoisController;
Route::get('/', function () {
    return view('welcome');
});
Route::get('/whois', [whoisController::class, 'lookup']);
