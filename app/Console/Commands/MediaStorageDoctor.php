<?php

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaStorageDoctor extends Command
{
    protected $signature = 'media:storage-doctor
                            {--limit=20 : Number of recent public media rows to inspect}';

    protected $description =
        'Check that public media storage, public/storage, and Spatie media use one public storage root.';

    public function handle(): int
    {
        $limit = min(
            max((int) $this->option('limit'), 1),
            100
        );

        $errors = [];
        $warnings = [];

        $expectedRoot = storage_path('app/public');

        $configuredRoot = (string) config(
            'filesystems.disks.public.root'
        );

        $mediaDisk = (string) config(
            'media-library.disk_name',
            'public'
        );

        $conversionsDisk = config(
            'media-library.conversions_disk_name'
        );

        $this->components->info(
            'Checking media storage configuration...'
        );

        /*
        |--------------------------------------------------------------------------
        | Spatie Media Disk
        |--------------------------------------------------------------------------
        */

        if ($mediaDisk !== 'public') {
            $errors[] =
                "Spatie Media Library disk is [{$mediaDisk}], expected [public].";
        }

        /*
        |--------------------------------------------------------------------------
        | Conversion Disk
        |--------------------------------------------------------------------------
        */

        if (
            $conversionsDisk !== null &&
            $conversionsDisk !== '' &&
            $conversionsDisk !== 'public'
        ) {
            $errors[] =
                "Media conversions disk is [{$conversionsDisk}], expected [public] or null.";
        }

        /*
        |--------------------------------------------------------------------------
        | Public Filesystem Root
        |--------------------------------------------------------------------------
        */

        if (
            $this->normalizePath($configuredRoot) !==
            $this->normalizePath($expectedRoot)
        ) {
            $errors[] =
                "Public disk root is [{$configuredRoot}], expected [{$expectedRoot}].";
        }

        /*
        |--------------------------------------------------------------------------
        | Storage Directory
        |--------------------------------------------------------------------------
        */

        if (
            ! is_dir($expectedRoot) &&
            ! @mkdir($expectedRoot, 0775, true) &&
            ! is_dir($expectedRoot)
        ) {
            $errors[] =
                "Public storage directory does not exist and could not be created: {$expectedRoot}";
        }

        /*
        |--------------------------------------------------------------------------
        | Public Storage Link
        |--------------------------------------------------------------------------
        */

        $publicLink = public_path('storage');

        if (
            ! file_exists($publicLink) &&
            ! is_link($publicLink)
        ) {
            $errors[] =
                'public/storage is missing. Run: php artisan storage:link';
        }

        /*
        |--------------------------------------------------------------------------
        | Real Write / Read Test
        |--------------------------------------------------------------------------
        */

        if ($errors === []) {
            $probe =
                '.media-storage-doctor/' .
                Str::uuid() .
                '.txt';

            try {
                Storage::disk('public')->put(
                    $probe,
                    'media-storage-doctor'
                );

                if (
                    ! Storage::disk('public')->exists($probe)
                ) {
                    $errors[] =
                        'The public disk is not writable/readable.';
                }

                $publicProbe = public_path(
                    'storage/' .
                    str_replace('\\', '/', $probe)
                );

                if (! file_exists($publicProbe)) {
                    $errors[] =
                        'public/storage does not expose the same physical public disk. Recreate the storage link.';
                }
            } catch (\Throwable $exception) {
                $errors[] =
                    'Public disk probe failed: ' .
                    $exception->getMessage();
            } finally {
                try {
                    Storage::disk('public')->delete($probe);
                } catch (\Throwable) {
                    //
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Recent Media Files
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('media')) {
            $missing = [];

            Media::withTrashed()
                ->where('disk', 'public')
                ->latest('id')
                ->limit($limit)
                ->get()
                ->each(
                    function (Media $media) use (&$missing): void {
                        try {
                            if (
                                ! Storage::disk('public')->exists(
                                    $media->getPathRelativeToRoot()
                                )
                            ) {
                                $missing[] =
                                    "#{$media->id} {$media->file_name}";
                            }
                        } catch (\Throwable) {
                            $missing[] =
                                "#{$media->id} {$media->file_name}";
                        }
                    }
                );

            if ($missing !== []) {
                $warnings[] =
                    'Recent media rows with missing physical files: ' .
                    implode(', ', $missing);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Output
        |--------------------------------------------------------------------------
        */

        foreach ($warnings as $warning) {
            $this->components->warn($warning);
        }

        if ($errors !== []) {
            foreach ($errors as $error) {
                $this->components->error($error);
            }

            $this->newLine();

            $this->error(
                'Media storage needs attention.'
            );

            return self::FAILURE;
        }

        $this->newLine();

        /*
         * IMPORTANT:
         * routes/command.php checks this exact text.
         */
        $this->info(
            'Single public media root is ready.'
        );

        return self::SUCCESS;
    }

    private function normalizePath(string $path): string
    {
        return rtrim(
            str_replace('\\', '/', $path),
            '/'
        );
    }
}