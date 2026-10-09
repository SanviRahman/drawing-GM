<?php

use App\Http\Controllers\Website\HomeController;
use App\Http\Controllers\Website\QuoteController;
use Illuminate\Support\Facades\Route;

// Public website routes are loaded by routes/web.php within the web middleware.
Route::get('/', [HomeController::class, 'index'])->name('website.home');
Route::post('/enquiries', [QuoteController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('website.enquiries.store');
