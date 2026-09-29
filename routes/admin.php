<?php

use App\Http\Controllers\Backoffice\Admin\AdminController;
use App\Http\Controllers\Backoffice\Admin\DashboardController;
use App\Http\Controllers\Backoffice\Admin\MediaController;
use App\Http\Controllers\Backoffice\Admin\MenuController;
use App\Http\Controllers\Backoffice\Admin\PageController;
use App\Http\Controllers\Backoffice\Admin\PageSectionController;
use App\Http\Controllers\Backoffice\Admin\PermissionController;
use App\Http\Controllers\Backoffice\Admin\ProfileController;
use App\Http\Controllers\Backoffice\Admin\RoleController;
use App\Http\Controllers\Backoffice\Admin\SectionDefinitionController;
use App\Http\Controllers\Backoffice\Admin\SectionMediaController;
use App\Http\Controllers\Backoffice\Admin\SettingController;
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

    // Site Settings
    Route::group(['prefix' => 'settings', 'as' => 'settings.'], function () {
        Route::post('multiple-action', [SettingController::class, 'multipleAction'])->name('multiple_action');
        Route::get('trash', [SettingController::class, 'trash'])->name('trashed');
        Route::post('restore/{setting}', [SettingController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{setting}', [SettingController::class, 'forceDelete'])->name('force_delete');
        Route::get('list', [SettingController::class, 'list'])->name('list');
        Route::get('ajax-search', [SettingController::class, 'list'])->name('ajax_search');
        Route::resource('/', SettingController::class)->parameters(['' => 'setting']);
    });

    // Global Media Management / Picker
    Route::group(['prefix' => 'media-management', 'as' => 'media.'], function () {
        Route::get('list', [MediaController::class, 'list'])->name('list');
        Route::post('multiple-action', [MediaController::class, 'multipleAction'])->name('multiple_action');
        Route::get('trash', [MediaController::class, 'trash'])->name('trashed');
        Route::post('restore/{media}', [MediaController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{media}', [MediaController::class, 'forceDelete'])->name('force_delete');
        Route::get('{media}/download', [MediaController::class, 'download'])->name('download');
        Route::get('/', [MediaController::class, 'index'])->name('index');
        Route::get('create', [MediaController::class, 'create'])->name('create');
        Route::post('/', [MediaController::class, 'store'])->name('store');
        Route::get('{media}', [MediaController::class, 'show'])->name('show');
        Route::get('{media}/edit', [MediaController::class, 'edit'])->name('edit');
        Route::put('{media}', [MediaController::class, 'update'])->name('update');
        Route::delete('{media}', [MediaController::class, 'destroy'])->name('destroy');
    });

    // Menus
    Route::group(['prefix' => 'menus', 'as' => 'menus.'], function () {
        Route::post('multiple-action', [MenuController::class, 'multipleAction'])->name('multiple_action');
        Route::get('trash', [MenuController::class, 'trash'])->name('trashed');
        Route::post('restore/{menu}', [MenuController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{menu}', [MenuController::class, 'forceDelete'])->name('force_delete');
        Route::get('list', [MenuController::class, 'list'])->name('list');
        Route::get('ajax-search', [MenuController::class, 'list'])->name('ajax_search');

        // Menu items (nested, scoped to their parent menu)
        Route::scopeBindings()->group(function () {
            Route::get('{menu}/items/create', [MenuController::class, 'itemCreate'])->name('item_create');
            Route::post('{menu}/items', [MenuController::class, 'itemStore'])->name('item_store');
            Route::get('{menu}/items/{item}/edit', [MenuController::class, 'itemEdit'])->name('item_edit');
            Route::put('{menu}/items/{item}', [MenuController::class, 'itemUpdate'])->name('item_update');
            Route::delete('{menu}/items/{item}', [MenuController::class, 'itemDestroy'])->name('item_destroy');
        });

        Route::resource('/', MenuController::class)->parameters(['' => 'menu']);
    });

    // Pages
    Route::group(['prefix' => 'pages', 'as' => 'pages.'], function () {
        Route::post('multiple-action', [PageController::class, 'multipleAction'])->name('multiple_action');
        Route::get('trash', [PageController::class, 'trash'])->name('trashed');
        Route::post('restore/{page}', [PageController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{page}', [PageController::class, 'forceDelete'])->name('force_delete');
        Route::get('list', [PageController::class, 'list'])->name('list');
        Route::get('ajax-search', [PageController::class, 'list'])->name('ajax_search');
        Route::post('{page}/publish', [PageController::class, 'publish'])->name('publish');
        Route::post('{page}/unpublish', [PageController::class, 'unpublish'])->name('unpublish');
        Route::post('{page}/duplicate', [PageController::class, 'duplicate'])->name('duplicate');
        Route::resource('/', PageController::class)->parameters(['' => 'page']);
    });

    // Section Definitions
    Route::group(['prefix' => 'section-definitions', 'as' => 'section_definitions.'], function () {
        Route::post('multiple-action', [SectionDefinitionController::class, 'multipleAction'])->name('multiple_action');
        Route::get('trash', [SectionDefinitionController::class, 'trash'])->name('trashed');
        Route::post('restore/{sectionDefinition}', [SectionDefinitionController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{sectionDefinition}', [SectionDefinitionController::class, 'forceDelete'])->name('force_delete');
        Route::get('list', [SectionDefinitionController::class, 'list'])->name('list');
        Route::get('ajax-search', [SectionDefinitionController::class, 'list'])->name('ajax_search');
        Route::post('{sectionDefinition}/toggle', [SectionDefinitionController::class, 'toggle'])->name('toggle');
        Route::resource('/', SectionDefinitionController::class)->parameters(['' => 'sectionDefinition']);
    });

    // Page Sections
    Route::group(['prefix' => 'page-sections', 'as' => 'page_sections.'], function () {
        Route::post('multiple-action', [PageSectionController::class, 'multipleAction'])->name('multiple_action');
        Route::get('trash', [PageSectionController::class, 'trash'])->name('trashed');
        Route::post('restore/{pageSection}', [PageSectionController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{pageSection}', [PageSectionController::class, 'forceDelete'])->name('force_delete');
        Route::post('{pageSection}/toggle', [PageSectionController::class, 'toggle'])->name('toggle');
        Route::get('/', [PageSectionController::class, 'index'])->name('index');
        Route::get('create', [PageSectionController::class, 'create'])->name('create');
        Route::post('/', [PageSectionController::class, 'store'])->name('store');
        Route::get('{pageSection}', [PageSectionController::class, 'show'])->name('show');
        Route::get('{pageSection}/edit', [PageSectionController::class, 'edit'])->name('edit');
        Route::put('{pageSection}', [PageSectionController::class, 'update'])->name('update');
        Route::delete('{pageSection}', [PageSectionController::class, 'destroy'])->name('destroy');
    });

    Route::group(['prefix' => 'section-media', 'as' => 'section_media.'], function () {
        Route::post('multiple-action', [SectionMediaController::class, 'multipleAction'])->name('multiple_action');
        Route::get('trash', [SectionMediaController::class, 'trash'])->name('trashed');
        Route::post('restore/{sectionMedia}', [SectionMediaController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{sectionMedia}', [SectionMediaController::class, 'forceDelete'])->name('force_delete');

        Route::get('/', [SectionMediaController::class, 'index'])->name('index');
        Route::get('create', [SectionMediaController::class, 'create'])->name('create');
        Route::post('/', [SectionMediaController::class, 'store'])->name('store');
        Route::get('{sectionMedia}', [SectionMediaController::class, 'show'])->name('show');
        Route::get('{sectionMedia}/edit', [SectionMediaController::class, 'edit'])->name('edit');
        Route::put('{sectionMedia}', [SectionMediaController::class, 'update'])->name('update');
        Route::delete('{sectionMedia}', [SectionMediaController::class, 'destroy'])->name('destroy');
    });

});
