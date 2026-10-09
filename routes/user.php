<?php

use App\Http\Controllers\Website\HomeController;
use App\Http\Controllers\Website\QuoteController;
use App\Http\Controllers\Website\ContentController;
use Illuminate\Support\Facades\Route;

// Public website routes are loaded by routes/web.php within the web middleware.
Route::get('/', [HomeController::class, 'index'])->name('website.home');
Route::post('/enquiries', [QuoteController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('website.enquiries.store');

// Only public CMS destinations have this route; /admin is never captured.
Route::get('/blog/{slug}', [ContentController::class, 'article'])->name('website.article');
Route::get('/services/{slug}', [ContentController::class, 'service'])->name('website.service');
Route::get('/{slug}', [ContentController::class, 'page'])
    ->where('slug', 'plastering|hacking|false-ceiling|pricing|contact|blog|cost-calculator|about|gallery|service-areas|request-quote')
    ->name('website.page');
