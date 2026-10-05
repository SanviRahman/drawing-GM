<?php
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
$redirectWithToast = function (string $type, string $message) {
    $returnTo = trim((string) request()->input('return_to', request()->query('return_to', '')));
    $fallbackUrl = route('command.index');
    $targetUrl = $fallbackUrl;
    $validRelativeReturn = $returnTo !== '' && str_starts_with($returnTo, '/') && ! str_starts_with($returnTo, '//');
    if ($validRelativeReturn) {
        $targetUrl = url($returnTo);
    }
    $parsedUrl = parse_url($targetUrl);
    $cleanPath = $parsedUrl['path'] ?? '/';
    $cleanUrl = url($cleanPath);
    if (! empty($parsedUrl['query'])) {
        parse_str($parsedUrl['query'], $queryParams);
        unset($queryParams['toast_type'], $queryParams['toast_message']);
        if (! empty($queryParams)) {
            $cleanUrl .= '?' . http_build_query($queryParams);
        }
    }
    $flashKey = $type === 'error' ? 'error' : 'success';
    return redirect()->to($cleanUrl)->with($flashKey, $message);
};
Route::prefix('command')
    ->name('command.')
    ->middleware(['auth:admin', 'can:system_tools_manage'])
    ->group(function () use ($redirectWithToast) {
        Route::get('/', function () {
            $title = 'System Commands';
            $breadcrumb = [
                ['text' => 'System Tools', 'url' => null],
                ['text' => 'System Commands', 'url' => null],
            ];
            $isLocal = app()->environment('local');
            $artisanCommands = Artisan::all();
            $hasMediaStorageDoctor = array_key_exists('media:storage-doctor', $artisanCommands);
            return view('backoffice.admin.commands.index', compact('title', 'breadcrumb', 'isLocal', 'hasMediaStorageDoctor'));
        })->name('index');
        Route::post('/clear-cache', function () use ($redirectWithToast) {
            try {
                Artisan::call('cache:clear');
                return $redirectWithToast('success', 'Cache cleared successfully.');
            } catch (\Throwable $exception) {
                report($exception);
                return $redirectWithToast('error', 'Cache clear failed: '.$exception->getMessage());
            }
        })->name('clear-cache');
        Route::post('/clear-config', function () use ($redirectWithToast) {
            try {
                Artisan::call('config:clear');
                return $redirectWithToast('success', 'Config cleared successfully.');
            } catch (\Throwable $exception) {
                report($exception);
                return $redirectWithToast('error', 'Config clear failed: '.$exception->getMessage());
            }
        })->name('clear-config');
        Route::post('/clear-route', function () use ($redirectWithToast) {
            try {
                Artisan::call('route:clear');
                return $redirectWithToast('success', 'Route cache cleared successfully.');
            } catch (\Throwable $exception) {
                report($exception);
                return $redirectWithToast('error', 'Route cache clear failed: '.$exception->getMessage());
            }
        })->name('clear-route');
        Route::post('/clear-view', function () use ($redirectWithToast) {
            try {
                Artisan::call('view:clear');
                return $redirectWithToast('success', 'View cache cleared successfully.');
            } catch (\Throwable $exception) {
                report($exception);
                return $redirectWithToast('error', 'View cache clear failed: '.$exception->getMessage());
            }
        })->name('clear-view');
        Route::post('/clear-events', function () use ($redirectWithToast) {
            try {
                Artisan::call('event:clear');
                return $redirectWithToast('success', 'Events cache cleared successfully.');
            } catch (\Throwable $exception) {
                report($exception);
                return $redirectWithToast('error', 'Events cache clear failed: '.$exception->getMessage());
            }
        })->name('clear-events');
        Route::post('/optimize', function () use ($redirectWithToast) {
            try {
                Artisan::call('optimize');
                return $redirectWithToast('success', 'Application optimized successfully.');
            } catch (\Throwable $exception) {
                report($exception);
                return $redirectWithToast('error', 'Application optimization failed: '.$exception->getMessage());
            }
        })->name('optimize');
        Route::post('/optimize-clear', function () use ($redirectWithToast) {
            try {
                Artisan::call('optimize:clear');
                return $redirectWithToast('success', 'Optimize cache cleared successfully.');
            } catch (\Throwable $exception) {
                report($exception);
                return $redirectWithToast('error', 'Optimize cache clear failed: '.$exception->getMessage());
            }
        })->name('optimize-clear');
        Route::post('/migrate', function () use ($redirectWithToast) {
            try {
                Artisan::call('migrate', ['--force' => true]);
                return $redirectWithToast('success', 'Database migrated successfully.');
            } catch (\Throwable $exception) {
                report($exception);
                return $redirectWithToast('error', 'Database migration failed: '.$exception->getMessage());
            }
        })->name('migrate');
        Route::post('/seed', function () use ($redirectWithToast) {
            try {
                Artisan::call('db:seed', ['--force' => true]);
                return $redirectWithToast('success', 'Database seeded successfully.');
            } catch (\Throwable $exception) {
                report($exception);
                return $redirectWithToast('error', 'Database seeding failed: '.$exception->getMessage());
            }
        })->name('seed');
        Route::post('/media-storage-doctor', function () use ($redirectWithToast) {
            try {
                $artisanCommands = Artisan::all();
                if (! array_key_exists('media:storage-doctor', $artisanCommands)) {
                    return $redirectWithToast('error', 'Media Storage Doctor command is not installed in this project.');
                }
                Artisan::call('media:storage-doctor', ['--limit' => 20]);
                $output = trim(Artisan::output());
                $storageReady = str_contains($output, 'Single public media root is ready.');
                if ($storageReady) {
                    return $redirectWithToast('success', 'Media storage check passed. Admin and public images use one physical folder.');
                }
                return $redirectWithToast('error', 'Media storage needs attention.');
            } catch (\Throwable $exception) {
                report($exception);
                return $redirectWithToast('error', 'Media storage check failed: '.$exception->getMessage());
            }
        })->name('media-storage-doctor');
        Route::post('/migrate-fresh', function () use ($redirectWithToast) {
            if (! app()->environment('local')) {
                return $redirectWithToast('error', 'Fresh migrate is allowed only in local environment.');
            }
            try {
                Artisan::call('migrate:fresh', ['--force' => true]);
                return redirect()->route('admin.login')->with('success', 'Database fresh migrated successfully. Please log in again.');
            } catch (\Throwable $exception) {
                report($exception);
                return $redirectWithToast('error', 'Fresh migration failed: '.$exception->getMessage());
            }
        })->name('migrate-fresh');
        Route::post('/migrate-fresh-seed', function () use ($redirectWithToast) {
            if (! app()->environment('local')) {
                return $redirectWithToast('error', 'Fresh migrate seed is allowed only in local environment.');
            }
            try {
                Artisan::call('migrate:fresh', ['--seed' => true, '--force' => true]);
                return redirect()->route('admin.login')->with('success', 'Database fresh migrated and seeded successfully. Please log in again.');
            } catch (\Throwable $exception) {
                report($exception);
                return $redirectWithToast('error', 'Fresh migration and seeding failed: '.$exception->getMessage());
            }
        })->name('migrate-fresh-seed');
    });