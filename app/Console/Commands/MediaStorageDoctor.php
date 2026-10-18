<?php

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Diagnoses public media storage without deleting or relocating uploaded files.
 */
class MediaStorageDoctor extends Command
{
    protected $signature = 'media:storage-doctor
                            {--limit=20 : Number of recent public media rows to inspect}';

    protected $description = 'Check public disk, symlink, write/read access, and recent media paths.';

    public function handle(): int
    {
        $limit = min(max((int) $this->option('limit'), 1), 100);
        $errors = [];
        $warnings = [];

        $root = (string) config('filesystems.disks.public.root', '');
        $url = (string) config('filesystems.disks.public.url', '');
        $link = (string) config('filesystems.public_media_link', public_path('storage'));
        $links = (array) config('filesystems.links', []);
        $linkTarget = (string) ($links[$link] ?? '');
        $standardRoot = storage_path('app/public');
        $storageBase = storage_path();
        $mediaDisk = (string) config('media-library.disk_name', 'public');
        $conversionDisk = config('media-library.conversions_disk_name');

        $this->info('Media Storage Doctor');
        $this->line('Public disk root: ' . $root);
        $this->line('Public disk URL: ' . $url);
        $this->line('Configured public URL filesystem location: ' . $link);
        $this->line('Configured symlink target: ' . $linkTarget);
        $this->line('Laravel public_path (not necessarily webroot): ' . public_path());

        if ($mediaDisk !== 'public') {
            $errors[] = 'Spatie disk must be [public], currently [' . $mediaDisk . '].';
        }
        if ($conversionDisk !== null && $conversionDisk !== '' && $conversionDisk !== 'public') {
            $errors[] = 'Spatie conversion disk must be [public] or null, currently [' . $conversionDisk . '].';
        }

        if ($root === '' || (DIRECTORY_SEPARATOR !== '\\' && ! str_starts_with($root, '/'))) {
            $errors[] = 'The public disk root must be an absolute path.';
        }

        // NEVER expose the Laravel project, storage base, or private framework folders.
        // Resolve existing paths as well to detect symlink aliases of sensitive directories.
        $normalizedRoot = $this->normalizePath(realpath($root) ?: $root);
        $normalizedStorage = $this->normalizePath(realpath($storageBase) ?: $storageBase);
        $normalizedPrivate = $this->normalizePath(storage_path('app/private'));
        $normalizedLogs = $this->normalizePath(storage_path('logs'));
        $normalizedFramework = $this->normalizePath(storage_path('framework'));
        if (
            $normalizedRoot === '' ||
            $normalizedRoot === '/' ||
            $normalizedRoot === $this->normalizePath(base_path()) ||
            $normalizedRoot === $normalizedStorage ||
            str_starts_with($normalizedStorage . '/', $normalizedRoot . '/') ||
            $this->isInside($normalizedRoot, $normalizedPrivate) ||
            $this->isInside($normalizedRoot, $normalizedLogs) ||
            $this->isInside($normalizedRoot, $normalizedFramework)
        ) {
            $errors[] = 'UNSAFE PUBLIC ROOT: Never use the entire Laravel storage or project folder. '
                . 'Use ' . $standardRoot . ' (the storage/app/public directory), not ' . $storageBase . '.';
        }

        if ($url === '' || ! str_starts_with($url, 'https://') && ! str_starts_with($url, 'http://')) {
            $errors[] = 'Public media URL is invalid. Configure PUBLIC_MEDIA_URL with https://your-domain/storage.';
        }
        if (str_starts_with($url, 'http://') && app()->environment('production')) {
            $warnings[] = 'Public media URL is HTTP on production; use HTTPS to prevent mixed-content issues.';
        }

        if ($link === '' || ! str_starts_with($link, '/')) {
            $errors[] = 'PUBLIC_MEDIA_LINK must be an absolute Linux path.';
        }
        if ($this->normalizePath($root) === $this->normalizePath($link)) {
            $errors[] = 'Public disk root must NOT be the same path as PUBLIC_MEDIA_LINK; use a private-app media directory and a public symlink.';
        }
        if ($this->normalizePath($root) !== $this->normalizePath($linkTarget)) {
            $errors[] = 'Public disk root and filesystems.links target are different.';
        }

        // Avoid creating directories on misconfigured/unsafe roots.
        if ($errors === []) {
            if (! is_dir($root) && ! @mkdir($root, 0775, true) && ! is_dir($root)) {
                $errors[] = 'Public media directory does not exist and cannot be created: ' . $root;
            }
        }

        if (! is_link($link)) {
            if (file_exists($link)) {
                $errors[] = 'Public media URL location exists but is a REAL folder, not a symlink: ' . $link . '. Inspect/backup existing files, relocate them if required, and rename the folder before creating the link.';
            } else {
                $errors[] = 'Public webroot storage symlink is missing at ' . $link . '. After validating the root and existing files, run php artisan storage:link.';
            }
        } elseif (realpath($link) === false) {
            $errors[] = 'Webroot storage symlink is broken at ' . $link . '. Check its target in cPanel.';
        } elseif (realpath($root) !== false && realpath($link) !== realpath($root)) {
            $errors[] = 'Webroot storage symlink points to a DIFFERENT folder: ' . (string) readlink($link);
        }

        // For HTTP-triggered checks, verify that the configured storage URL
        // link actually lives underneath the webserver's effective document root.
        $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? null;
        if (is_string($documentRoot) && $documentRoot !== '') {
            $resolvedDocumentRoot = realpath($documentRoot);
            $configuredLinkParent = realpath(dirname($link));
            $this->line('HTTP DOCUMENT_ROOT: ' . $documentRoot);
            if ($resolvedDocumentRoot && $configuredLinkParent && $resolvedDocumentRoot !== $configuredLinkParent) {
                $errors[] = 'PUBLIC_MEDIA_LINK must be located in the actual HTTP DOCUMENT_ROOT ('
                    . $documentRoot . '), not under Laravel public_path().';
            }
        }

        if ($errors === []) {
            $probe = '.media-storage-doctor/' . Str::uuid() . '.txt';
            try {
                if (! Storage::disk('public')->put($probe, 'media-storage-doctor')) {
                    $errors[] = 'Failed to write to the public disk (permissions or disk configuration).';
                } elseif (! Storage::disk('public')->exists($probe)) {
                    $errors[] = 'Media probe was written but cannot be read from the public disk.';
                } else {
                    $publicProbe = $link . '/' . $probe;
                    if (! is_file($publicProbe)) {
                        $errors[] = 'Configured webroot storage link does not expose the same physical files as public disk.';
                    }
                }
            } catch (\Throwable $exception) {
                $errors[] = 'Public disk write/read failed: ' . $exception->getMessage();
            } finally {
                try {
                    Storage::disk('public')->delete($probe);
                } catch (\Throwable) {
                    // Never let cleanup mask a diagnostic result.
                }
            }
        }

        try {
            if (Schema::hasTable('media')) {
                $missing = [];
                Media::withTrashed()
                    ->where('disk', 'public')
                    ->latest('id')
                    ->limit($limit)
                    ->get()
                    ->each(function (Media $media) use (&$missing): void {
                        try {
                            if (! Storage::disk('public')->exists($media->getPathRelativeToRoot())) {
                                $missing[] = '#' . $media->id . ' ' . $media->file_name;
                            }
                        } catch (\Throwable) {
                            $missing[] = '#' . $media->id . ' ' . $media->file_name;
                        }
                    });
                if ($missing !== []) {
                    $warnings[] = 'Recent media records whose original files are missing: ' . implode(', ', $missing)
                        . '. Restore the files from the original deployment/backup; do not re-seed the DB.';
                }
            } else {
                $warnings[] = 'Media database table does not exist. Check migrations without running migrate:fresh.';
            }
        } catch (\Throwable $exception) {
            $warnings[] = 'Unable to inspect media database records: ' . $exception->getMessage();
        }

        foreach ($warnings as $warning) {
            $this->warn('WARNING: ' . $warning);
        }
        if ($errors !== []) {
            foreach ($errors as $error) {
                $this->error('ERROR: ' . $error);
            }
            $this->error('Media storage needs attention.');
            return self::FAILURE;
        }

        // routes/command.php uses the exit code; retain this text for existing integrations.
        $this->info('Single public media root is ready.');
        return self::SUCCESS;
    }

    private function normalizePath(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }

    private function isInside(string $path, string $parent): bool
    {
        return $path === $parent || str_starts_with($path, $parent . '/');
    }
}
