<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')
    ->middleware('lte_context:admin')
    ->group(function () {
        Auth::routes([
            'register' => false,
        ]);

        require __DIR__.'/command.php';

        Route::name('admin.')->group(function () {
            require __DIR__.'/admin.php';
        });
    });