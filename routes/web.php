<?php

use Illuminate\Support\Facades\Route;

// Keep public routes separate from the existing /admin area.
require __DIR__.'/user.php';

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