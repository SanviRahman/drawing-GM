<?php

use App\Http\Controllers\Backoffice\Admin\AdminController;
use App\Http\Controllers\Backoffice\Admin\DashboardController;
use App\Http\Controllers\Backoffice\Admin\FaqController;
use App\Http\Controllers\Backoffice\Admin\GalleryController;
use App\Http\Controllers\Backoffice\Admin\GalleryItemController;
use App\Http\Controllers\Backoffice\Admin\LocationController;
use App\Http\Controllers\Backoffice\Admin\MediaController;
use App\Http\Controllers\Backoffice\Admin\MenuController;
use App\Http\Controllers\Backoffice\Admin\PageController;
use App\Http\Controllers\Backoffice\Admin\PageSectionController;
use App\Http\Controllers\Backoffice\Admin\PermissionController;
use App\Http\Controllers\Backoffice\Admin\PricingAddonController;
use App\Http\Controllers\Backoffice\Admin\PricingItemController;
use App\Http\Controllers\Backoffice\Admin\PricingPackageAddonController;
use App\Http\Controllers\Backoffice\Admin\PricingPackageController;
use App\Http\Controllers\Backoffice\Admin\ProfileController;
use App\Http\Controllers\Backoffice\Admin\RoleController;
use App\Http\Controllers\Backoffice\Admin\SectionDefinitionController;
use App\Http\Controllers\Backoffice\Admin\SectionMediaController;
use App\Http\Controllers\Backoffice\Admin\ServiceController;
use App\Http\Controllers\Backoffice\Admin\ServiceFeatureController;
use App\Http\Controllers\Backoffice\Admin\SettingController;
use App\Http\Controllers\Backoffice\Admin\TestimonialController;
use App\Http\Controllers\Backoffice\Admin\VideoController;
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

        Route::resource('/', MediaController::class)->parameters(['' => 'media']);
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

        Route::resource('/', PageSectionController::class)->parameters(['' => 'pageSection']);
    });

    // Section Media
    Route::group(['prefix' => 'section-media', 'as' => 'section_media.'], function () {
        Route::post('multiple-action', [SectionMediaController::class, 'multipleAction'])->name('multiple_action');
        Route::get('trash', [SectionMediaController::class, 'trash'])->name('trashed');
        Route::post('restore/{sectionMedia}', [SectionMediaController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{sectionMedia}', [SectionMediaController::class, 'forceDelete'])->name('force_delete');

        Route::resource('/', SectionMediaController::class)->parameters(['' => 'sectionMedia']);
    });

    // Services
    Route::group(['prefix' => 'services', 'as' => 'services.'], function () {
        Route::post('multiple-action', [ServiceController::class, 'multipleAction'])->name('multiple_action');
        Route::get('trash', [ServiceController::class, 'trash'])->name('trashed');
        Route::get('list', [ServiceController::class, 'list'])->name('list');
        Route::post('restore/{service}', [ServiceController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{service}', [ServiceController::class, 'forceDelete'])->name('force_delete');
        Route::post('{service}/publish', [ServiceController::class, 'publish'])->name('publish');
        Route::post('{service}/unpublish', [ServiceController::class, 'unpublish'])->name('unpublish');
        Route::post('{service}/duplicate', [ServiceController::class, 'duplicate'])->name('duplicate');

        Route::resource('/', ServiceController::class)->parameters(['' => 'service']);
    });

    // Service Features
    Route::group(['prefix' => 'service-features', 'as' => 'service_features.'], function () {
        Route::post('multiple-action', [ServiceFeatureController::class, 'multipleAction'])->name('multiple_action');
        Route::get('trash', [ServiceFeatureController::class, 'trash'])->name('trashed');
        Route::post('restore/{serviceFeature}', [ServiceFeatureController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{serviceFeature}', [ServiceFeatureController::class, 'forceDelete'])->name('force_delete');
        Route::post('reorder', [ServiceFeatureController::class, 'reorder'])->name('reorder');
        Route::post('{serviceFeature}/toggle', [ServiceFeatureController::class, 'toggle'])->name('toggle');
        Route::post('{serviceFeature}/duplicate', [ServiceFeatureController::class, 'duplicate'])->name('duplicate');

        Route::resource('/', ServiceFeatureController::class)->parameters(['' => 'serviceFeature']);
    });

    // Locations
    Route::group(['prefix' => 'locations', 'as' => 'locations.'], function () {
        Route::post('multiple-action', [LocationController::class, 'multipleAction'])->name('multiple_action');
        Route::get('trash', [LocationController::class, 'trash'])->name('trashed');
        Route::post('restore/{location}', [LocationController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{location}', [LocationController::class, 'forceDelete'])->name('force_delete');
        Route::get('list', [LocationController::class, 'list'])->name('list');
        Route::get('ajax-search', [LocationController::class, 'list'])->name('ajax_search');
        Route::post('{location}/publish', [LocationController::class, 'publish'])->name('publish');
        Route::post('{location}/unpublish', [LocationController::class, 'unpublish'])->name('unpublish');
        Route::post('{location}/duplicate', [LocationController::class, 'duplicate'])->name('duplicate');

        Route::resource('/', LocationController::class)->parameters(['' => 'location']);
    });

    // Pricing Packages
    Route::group(['prefix' => 'pricing-packages', 'as' => 'pricing_packages.'], function () {
        Route::post('multiple-action', [PricingPackageController::class, 'multipleAction'])->name('multiple_action');

        Route::get('trash', [PricingPackageController::class, 'trash'])->name('trashed');
        Route::post('restore/{pricingPackage}', [PricingPackageController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{pricingPackage}', [PricingPackageController::class, 'forceDelete'])->name('force_delete');

        Route::get('list', [PricingPackageController::class, 'list'])->name('list');
        Route::get('ajax-search', [PricingPackageController::class, 'list'])->name('ajax_search');

        Route::post('{pricingPackage}/toggle', [PricingPackageController::class, 'toggle'])->name('toggle');
        Route::post('{pricingPackage}/duplicate', [PricingPackageController::class, 'duplicate'])->name('duplicate');

        Route::resource('/', PricingPackageController::class)->parameters(['' => 'pricingPackage']);
    });

    // Pricing Items
    Route::group(['prefix' => 'pricing-items', 'as' => 'pricing_items.'], function () {
        Route::post('multiple-action', [PricingItemController::class, 'multipleAction'])->name('multiple_action');

        Route::get('trash', [PricingItemController::class, 'trash'])->name('trashed');
        Route::post('restore/{pricingItem}', [PricingItemController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{pricingItem}', [PricingItemController::class, 'forceDelete'])->name('force_delete');

        Route::get('list', [PricingItemController::class, 'list'])->name('list');
        Route::get('ajax-search', [PricingItemController::class, 'list'])->name('ajax_search');

        Route::post('{pricingItem}/toggle', [PricingItemController::class, 'toggle'])->name('toggle');
        Route::post('{pricingItem}/duplicate', [PricingItemController::class, 'duplicate'])->name('duplicate');

        Route::resource('/', PricingItemController::class)->parameters(['' => 'pricingItem']);
    });

    // Pricing Add-on
    Route::group(['prefix' => 'pricing-addons', 'as' => 'pricing_addons.'], function () {
        Route::post('multiple-action', [PricingAddonController::class, 'multipleAction'])->name('multiple_action');
        Route::get('trash', [PricingAddonController::class, 'trash'])->name('trashed');
        Route::post('restore/{pricingAddon}', [PricingAddonController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{pricingAddon}', [PricingAddonController::class, 'forceDelete'])->name('force_delete');
        Route::get('list', [PricingAddonController::class, 'list'])->name('list');
        Route::get('ajax-search', [PricingAddonController::class, 'list'])->name('ajax_search');
        Route::post('{pricingAddon}/toggle', [PricingAddonController::class, 'toggle'])->name('toggle');
        Route::post('{pricingAddon}/duplicate', [PricingAddonController::class, 'duplicate'])->name('duplicate');
        Route::resource('/', PricingAddonController::class)->parameters(['' => 'pricingAddon']);
    });

    // Pricing Package Add-ons
    Route::group(['prefix' => 'pricing-package-addons', 'as' => 'pricing_package_addons.'], function () {
        Route::post('multiple-action', [PricingPackageAddonController::class, 'multipleAction'])->name('multiple_action');
        Route::get('trash', [PricingPackageAddonController::class, 'trash'])->name('trashed');
        Route::post('restore/{pricingPackageAddon}', [PricingPackageAddonController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{pricingPackageAddon}', [PricingPackageAddonController::class, 'forceDelete'])->name('force_delete');
        Route::get('list', [PricingPackageAddonController::class, 'list'])->name('list');
        Route::get('ajax-search', [PricingPackageAddonController::class, 'list'])->name('ajax_search');
        Route::resource('/', PricingPackageAddonController::class)->parameters(['' => 'pricingPackageAddon']);
    });

    // Galleries
    Route::group(['prefix' => 'galleries', 'as' => 'galleries.'], function () {
        Route::post('multiple-action', [GalleryController::class, 'multipleAction'])->name('multiple_action');
        Route::get('trash', [GalleryController::class, 'trash'])->name('trashed');
        Route::post('restore/{gallery}', [GalleryController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{gallery}', [GalleryController::class, 'forceDelete'])->name('force_delete');
        Route::get('list', [GalleryController::class, 'list'])->name('list');
        Route::get('ajax-search', [GalleryController::class, 'list'])->name('ajax_search');
        Route::resource('/', GalleryController::class)->parameters(['' => 'gallery']);

    });

    // Gallery Items
    Route::group(['prefix' => 'gallery-items', 'as' => 'gallery_items.'], function () {
        Route::post('multiple-action', [GalleryItemController::class, 'multipleAction'])->name('multiple_action');
        Route::get('trash', [GalleryItemController::class, 'trash'])->name('trashed');
        Route::post('restore/{galleryItem}', [GalleryItemController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{galleryItem}', [GalleryItemController::class, 'forceDelete'])->name('force_delete');
        Route::get('list', [GalleryItemController::class, 'list'])->name('list');
        Route::get('ajax-search', [GalleryItemController::class, 'list'])->name('ajax_search');
        Route::resource('/', GalleryItemController::class)->parameters(['' => 'galleryItem']);
    });

    // Videos
    Route::group(['prefix' => 'videos', 'as' => 'videos.'], function () {
        Route::post('multiple-action', [VideoController::class, 'multipleAction'])->name('multiple_action');
        Route::get('trash', [VideoController::class, 'trash'])->name('trashed');
        Route::post('restore/{video}', [VideoController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{video}', [VideoController::class, 'forceDelete'])->name('force_delete');
        Route::get('list', [VideoController::class, 'list'])->name('list');
        Route::get('ajax-search', [VideoController::class, 'list'])->name('ajax_search');
        Route::post('reorder', [VideoController::class, 'reorder'])->name('reorder');
        Route::post('{video}/toggle', [VideoController::class, 'toggle'])->name('toggle');
        Route::resource('/', VideoController::class)->parameters(['' => 'video']);
    });

    // Testimonials
    Route::group(['prefix' => 'testimonials', 'as' => 'testimonials.'], function () {
        Route::post('multiple-action', [TestimonialController::class, 'multipleAction'])->name('multiple_action');
        Route::get('trash', [TestimonialController::class, 'trash'])->name('trashed');
        Route::post('restore/{testimonial}', [TestimonialController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{testimonial}', [TestimonialController::class, 'forceDelete'])->name('force_delete');
        Route::get('list', [TestimonialController::class, 'list'])->name('list');
        Route::get('ajax-search', [TestimonialController::class, 'list'])->name('ajax_search');
        Route::resource('/', TestimonialController::class)->parameters(['' => 'testimonial']);
    });
    // FAQs
    Route::group(['prefix' => 'faqs', 'as' => 'faqs.'], function () {
        Route::post('multiple-action', [FaqController::class, 'multipleAction'])->name('multiple_action');
        Route::get('trash', [FaqController::class, 'trash'])->name('trashed');
        Route::post('restore/{faq}', [FaqController::class, 'restore'])->name('restore');
        Route::delete('force-delete/{faq}', [FaqController::class, 'forceDelete'])->name('force_delete');
        Route::get('list', [FaqController::class, 'list'])->name('list');
        Route::get('ajax-search', [FaqController::class, 'list'])->name('ajax_search');
        Route::post('reorder', [FaqController::class, 'reorderMappings'])->name('reorder');
        Route::post('{faq}/toggle', [FaqController::class, 'toggle'])->name('toggle');
        Route::resource('/', FaqController::class)->parameters(['' => 'faq']);
    });

});
