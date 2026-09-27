<?php

use App\Http\Controllers\Backoffice\Admin\AdminController;
use App\Http\Controllers\Backoffice\Admin\DashboardController;
use App\Http\Controllers\Backoffice\Admin\PermissionController;
use App\Http\Controllers\Backoffice\Admin\ProfileController;
use App\Http\Controllers\Backoffice\Admin\RoleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:admin')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile', [ProfileController::class, 'profile'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'updateProfile'])->name('update_profile');
    Route::get('/password', [ProfileController::class, 'password'])->name('password');
    Route::post('/password', [ProfileController::class, 'updatePassword'])->name('update_password');

    // Admins
    Route::group(['prefix' => 'admins', 'as' => 'admins.'], function () {
        Route::post('multiple-action', [AdminController::class, 'multipleAction'])->name('multiple_action');
        Route::get('trash', [AdminController::class, 'trash'])->name('trashed');
        Route::post('restore/{admin}', [AdminController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{admin}', [AdminController::class, 'forceDelete'])->name('force_delete');
        Route::get('list', [AdminController::class, 'list'])->name('list');
        Route::get('ajax-search', [AdminController::class, 'list'])->name('ajax_search');
        Route::resource('/', AdminController::class)->parameters(['' => 'admin']);
    });

    // Roles
    Route::group(['prefix' => 'roles', 'as' => 'roles.'], function () {
        Route::post('multiple-action', [RoleController::class, 'multipleAction'])->name('multiple_action');
        Route::get('trash', [RoleController::class, 'trash'])->name('trashed');
        Route::post('restore/{role}', [RoleController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{role}', [RoleController::class, 'forceDelete'])->name('force_delete');
        Route::get('list', [RoleController::class, 'list'])->name('list');
        Route::get('ajax-search', [RoleController::class, 'list'])->name('ajax_search');
        Route::get('get-permissions', [RoleController::class, 'getPermissions'])->name('get_permissions');
        Route::resource('/', RoleController::class)->parameters(['' => 'role']);
    });

    // Permissions
    Route::group(['prefix' => 'permissions', 'as' => 'permissions.'], function () {
        Route::post('multiple-action', [PermissionController::class, 'multipleAction'])->name('multiple_action');
        Route::get('trash', [PermissionController::class, 'trash'])->name('trashed');
        Route::post('restore/{permission}', [PermissionController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{permission}', [PermissionController::class, 'forceDelete'])->name('force_delete');
        Route::get('list', [PermissionController::class, 'list'])->name('list');
        Route::get('ajax-search', [PermissionController::class, 'list'])->name('ajax_search');
        Route::resource('/', PermissionController::class)->parameters(['' => 'permission']);
    });

    

});