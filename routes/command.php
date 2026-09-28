<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

$redirectWithToast = function (string $type, string $message) {
    $returnTo = trim((string) request()->query('return_to', ''));
    $fallbackUrl = url()->previous() ?: route('admin.dashboard');
    $targetUrl = $fallbackUrl;

    $validRelativeReturn = $returnTo !== ''
        && str_starts_with($returnTo, '/')
        && ! str_starts_with($returnTo, '//');

    if ($validRelativeReturn) {
        $targetUrl = url($returnTo);
    }

    // URL থেকে আগের জমে থাকা toast_type ও toast_message রিমুভ করে ক্লিন করা
    $parsedUrl = parse_url($targetUrl);
    $cleanPath = ($parsedUrl['path'] ?? '/');
    $cleanUrl = url($cleanPath);

    if (isset($parsedUrl['query'])) {
        parse_str($parsedUrl['query'], $queryParams);
        unset($queryParams['toast_type'], $queryParams['toast_message']);
        if (! empty($queryParams)) {
            $cleanUrl .= '?' . http_build_query($queryParams);
        }
    }

    $flashKey = $type === 'error' ? 'error' : 'success';

    return redirect()->to($cleanUrl)->with($flashKey, $message);
};

Route::prefix('admin/command')
    ->name('command.')
    ->middleware(['auth:admin', 'can:system_tools_manage'])
    ->group(function () use ($redirectWithToast) {

        Route::get('/clear-cache', function () use ($redirectWithToast) {
            Artisan::call('cache:clear');

            return $redirectWithToast('success', 'Cache cleared successfully.');
        })->name('clear-cache');

        Route::get('/clear-config', function () use ($redirectWithToast) {
            Artisan::call('config:clear');

            return $redirectWithToast('success', 'Config cleared successfully.');
        })->name('clear-config');

        Route::get('/clear-route', function () use ($redirectWithToast) {
            Artisan::call('route:clear');

            return $redirectWithToast('success', 'Route cache cleared successfully.');
        })->name('clear-route');

        Route::get('/clear-view', function () use ($redirectWithToast) {
            Artisan::call('view:clear');

            return $redirectWithToast('success', 'View cache cleared successfully.');
        })->name('clear-view');

        Route::get('/clear-events', function () use ($redirectWithToast) {
            try {
                Artisan::call('event:clear');

                return $redirectWithToast('success', 'Events cache cleared successfully.');
            } catch (\Throwable $exception) {
                report($exception);

                return $redirectWithToast(
                    'error',
                    'Events cache clear failed: ' . $exception->getMessage()
                );
            }
        })->name('clear-events');

        Route::get('/optimize', function () use ($redirectWithToast) {
            Artisan::call('optimize');

            return $redirectWithToast('success', 'Application optimized successfully.');
        })->name('optimize');

        Route::get('/optimize-clear', function () use ($redirectWithToast) {
            Artisan::call('optimize:clear');

            return $redirectWithToast('success', 'Optimize cache cleared successfully.');
        })->name('optimize-clear');

        Route::get('/migrate', function () use ($redirectWithToast) {
            Artisan::call('migrate', [
                '--force' => true,
            ]);

            return $redirectWithToast('success', 'Database migrated successfully.');
        })->name('migrate');

        Route::get('/seed', function () use ($redirectWithToast) {
            Artisan::call('db:seed', [
                '--force' => true,
            ]);

            return $redirectWithToast('success', 'Database seeded successfully.');
        })->name('seed');

        Route::get('/media-storage-doctor', function () use ($redirectWithToast) {
            try {
                Artisan::call('media:storage-doctor', [
                    '--limit' => 20,
                ]);

                $output = trim(Artisan::output());
                $message = str_contains($output, 'Single public media root is ready.')
                    ? 'Media storage check passed. Admin and public images use one physical folder.'
                    : 'Media storage needs attention. Run Prepare Media Storage first.';

                return $redirectWithToast(
                    str_contains($output, 'Single public media root is ready.') ? 'success' : 'error',
                    $message
                );
            } catch (\Throwable $exception) {
                report($exception);

                return $redirectWithToast(
                    'error',
                    'Media storage check failed: ' . $exception->getMessage()
                );
            }
        })->name('media-storage-doctor');

        Route::get('/migrate-fresh', function () use ($redirectWithToast) {
            if (! app()->environment('local')) {
                return $redirectWithToast(
                    'error',
                    'Fresh migrate is allowed only in local environment.'
                );
            }

            Artisan::call('migrate:fresh', [
                '--force' => true,
            ]);

            return $redirectWithToast('success', 'Database fresh migrated successfully. Please log in again.');
        })->name('migrate-fresh');

        Route::get('/migrate-fresh-seed', function () use ($redirectWithToast) {
            if (! app()->environment('local')) {
                return $redirectWithToast(
                    'error',
                    'Fresh migrate seed is allowed only in local environment.'
                );
            }

            Artisan::call('migrate:fresh', [
                '--seed'  => true,
                '--force' => true,
            ]);

            return $redirectWithToast(
                'success',
                'Database fresh migrated and seeded successfully. Please log in again.'
            );
        })->name('migrate-fresh-seed');
    });