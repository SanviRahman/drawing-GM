<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;
use RuntimeException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

/**
 * cPanel-safe, permission-gated webroot storage repair.
 * This service has no user-supplied paths and never deletes media.
 */
final class MediaStorageWebrootRepair
{
    private const SCAN_LIMIT = 3000;
    private const BATCH_FILES = 40;
    private const BATCH_BYTES = 32 * 1024 * 1024;

    private const PUBLIC_EXTENSIONS = [
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'bmp', 'ico',
        'pdf', 'mp4', 'webm', 'mov', 'mp3', 'wav', 'ogg', 'm4a',
    ];

    public function inspect(): string
    {
        [$root, $link, $backupParent] = $this->validatedPaths();
        $lines = [
            'Media Storage Repair — inspection only (no changes)',
            'Media root: ' . $root,
            'Webroot link: ' . $link,
            'Backup directory (outside webroot): ' . $backupParent,
        ];

        if (is_link($link)) {
            $lines[] = realpath($link) === realpath($root)
                ? 'STATUS: Existing storage symlink is correct. No repair needed.'
                : 'ERROR: Existing symlink targets a different location; manual investigation required.';
            return implode("\n", $lines);
        }
        if (file_exists($link) && ! is_dir($link)) {
            throw new RuntimeException('Webroot storage path is a file, not a folder. Inspect it in File Manager.');
        }
        if (! is_dir($link)) {
            $lines[] = 'STATUS: Webroot storage path does not exist. It can be linked after confirming the canonical root.';
            $lines[] = is_dir($root) ? 'Canonical media root already exists.' : 'Canonical media root must be created first (Stage button can create it).';
            return implode("\n", $lines);
        }

        $files = $this->inventory($link);
        $ready = 0;
        $missing = 0;
        $conflicts = 0;
        $totalBytes = 0;
        $examples = [];
        foreach ($files as $relative => $source) {
            $totalBytes += filesize($source) ?: 0;
            $dest = $root . '/' . $relative;
            if (is_link($dest) || $this->hasSymlinkAncestor($root, $relative)) {
                ++$conflicts;
            } elseif (! is_file($dest)) {
                ++$missing;
            } elseif (filesize($source) === filesize($dest)) {
                // Finalize performs SHA-256 validation; this is only a size estimate.
                ++$ready;
            } else {
                ++$conflicts;
            }
            if (count($examples) < 8) {
                $examples[] = $relative;
            }
        }
        $lines[] = 'Existing webroot storage folder: REAL directory, not symlink.';
        $lines[] = 'Safe public media files found: ' . count($files);
        $lines[] = 'Total bytes: ' . $totalBytes;
        $lines[] = 'Files missing from canonical media root: ' . $missing;
        $lines[] = 'Same-size destination candidates (not yet hash-verified): ' . $ready;
        $lines[] = 'Conflicts requiring review: ' . $conflicts;
        if ($examples !== []) {
            $lines[] = 'Example relative media paths: ' . implode(', ', $examples);
        }
        $lines[] = $missing > 0
            ? 'NEXT: Click Stage Existing Public Media. Repeat until no missing files remain.'
            : 'NEXT: Click Activate Storage Link after reviewing this report; finalization independently verifies hashes.';
        $lines[] = 'NOTE: Unknown extensions, symlinks inside old storage, or conflicting file contents block automatic repair.';
        return implode("\n", $lines);
    }

    public function stage(): string
    {
        return $this->locked(function (): string {
            [$root, $link] = $this->validatedPaths();
            if (is_link($link)) {
                throw new RuntimeException('Already a symlink. Run Media Storage Doctor instead.');
            }
            if (file_exists($link) && ! is_dir($link)) {
                throw new RuntimeException('Webroot storage path is not a directory.');
            }
            if (! is_dir($root) && ! @mkdir($root, 0755, true) && ! is_dir($root)) {
                throw new RuntimeException('Cannot create canonical media root. Check cPanel permissions.');
            }
            if (is_link($root)) {
                throw new RuntimeException('Canonical media root must be a real folder, not a symbolic link.');
            }
            if (! is_dir($link)) {
                return 'Canonical media root exists. Old webroot storage does not exist; you may Activate Storage Link.';
            }

            $files = $this->inventory($link); // Validate entire tree before copying anything.
            $copied = 0;
            $verified = 0;
            $usedBytes = 0;
            $remaining = 0;

            foreach ($files as $relative => $source) {
                $destination = $root . '/' . $relative;
                if ($this->hasSymlinkAncestor($root, $relative) || is_link($destination)) {
                    throw new RuntimeException('Symlink inside destination is not permitted: ' . $relative);
                }
                if (file_exists($destination)) {
                    if (! is_file($destination) || !$this->sameContent($source, $destination)) {
                        throw new RuntimeException('Conflicting existing file; no overwrite performed: ' . $relative);
                    }
                    ++$verified;
                    continue;
                }

                $bytes = filesize($source);
                if ($bytes === false) {
                    throw new RuntimeException('Unable to stat source media: ' . $relative);
                }
                // Reject single huge files; upload using cPanel File Manager instead.
                if ($bytes > self::BATCH_BYTES) {
                    throw new RuntimeException('File too large for admin HTTP copy; use File Manager: ' . $relative);
                }
                if ($copied >= self::BATCH_FILES || $usedBytes + $bytes > self::BATCH_BYTES) {
                    ++$remaining;
                    continue;
                }
                $parent = dirname($destination);
                if (! is_dir($parent) && ! @mkdir($parent, 0755, true) && ! is_dir($parent)) {
                    throw new RuntimeException('Unable to create destination folder for: ' . $relative);
                }
                if ($this->hasSymlinkAncestor($root, $relative)) {
                    throw new RuntimeException('Unsafe destination symlink detected: ' . $relative);
                }
                $temp = $parent . '/.media-stage-' . bin2hex(random_bytes(8)) . '.tmp';
                try {
                    if (! @copy($source, $temp) || !$this->sameContent($source, $temp)) {
                        throw new RuntimeException('Media copy/verification failed: ' . $relative);
                    }
                    if (file_exists($destination) || ! @rename($temp, $destination)) {
                        throw new RuntimeException('Unable to finalize copied media: ' . $relative);
                    }
                } finally {
                    if (is_file($temp)) {
                        @unlink($temp); // Only our unique temporary copy, never existing media.
                    }
                }
                ++$copied;
                $usedBytes += $bytes;
            }
            return "Media staging completed (no existing files overwritten).\n"
                . "New copies: {$copied}; existing exact matches: {$verified}; files awaiting another Stage click: {$remaining}.\n"
                . ($remaining > 0
                    ? 'NEXT: Click Stage Existing Public Media again.'
                    : 'NEXT: Run Inspect, then Activate Storage Link when ready.');
        });
    }

    public function activate(): string
    {
        return $this->locked(function (): string {
            [$root, $link, $backupParent] = $this->validatedPaths();
            if (! is_dir($root) || is_link($root)) {
                throw new RuntimeException('Canonical storage/app/public directory is missing or is a symlink. Run Stage first.');
            }
            if (is_link($link)) {
                if (realpath($link) === realpath($root)) {
                    return 'Storage symlink is already correct. Nothing changed.';
                }
                throw new RuntimeException('Existing symlink has an unexpected target. Manual inspection required; not overwriting it.');
            }
            if (file_exists($link) && ! is_dir($link)) {
                throw new RuntimeException('Existing public storage path is not a directory. Not modifying it.');
            }

            $backupPath = null;
            if (is_dir($link)) {
                $files = $this->inventory($link);
                foreach ($files as $relative => $source) {
                    $destination = $root . '/' . $relative;
                    if ($this->hasSymlinkAncestor($root, $relative) || ! is_file($destination) || !$this->sameContent($source, $destination)) {
                        throw new RuntimeException('Repair BLOCKED. Stage or manually resolve media first: ' . $relative);
                    }
                }

                // Backup MUST be outside the HTTP document root, and never overwrite an old backup.
                if (! is_dir($backupParent) && ! @mkdir($backupParent, 0700, true) && ! is_dir($backupParent)) {
                    throw new RuntimeException('Cannot create private backup directory. No changes made.');
                }
                if (is_link($backupParent) || ! is_writable($backupParent)) {
                    throw new RuntimeException('Private backup location is unsafe or not writable. No changes made.');
                }
                $backupPath = $backupParent . '/storage-' . date('Ymd-His') . '-' . bin2hex(random_bytes(5));
                if (file_exists($backupPath) || is_link($backupPath) || ! @rename($link, $backupPath)) {
                    throw new RuntimeException('Unable to move existing real folder into private backup. No link created.');
                }
            }

            // Uses Laravel's configured filesystems.links; never --force / never delete target.
            try {
                $status = Artisan::call('storage:link');
                if ($status !== 0 || ! is_link($link) || realpath($link) !== realpath($root)) {
                    throw new RuntimeException('Laravel storage:link did not create the expected link. Host may forbid symlinks.');
                }
            } catch (\Throwable $exception) {
                // It did not exist before this action. Only remove a link created
                // in this attempt, never remove or overwrite a preexisting file/folder.
                if (is_link($link)) {
                    @unlink($link);
                }
                if ($backupPath !== null && ! file_exists($link) && ! is_link($link)) {
                    if (! @rename($backupPath, $link)) {
                        throw new RuntimeException('Symlink failed AND automatic rollback failed. Restore from private backup: '
                            . $backupPath . '. Original problem: ' . $exception->getMessage(), 0, $exception);
                    }
                }
                throw new RuntimeException('Activation aborted; original folder restored where possible. ' . $exception->getMessage(), 0, $exception);
            }

            return 'SUCCESS: Webroot storage is now a symlink to the canonical public media folder.'
                . ($backupPath !== null ? "\nOriginal webroot folder safely backed up OUTSIDE public webroot: " . $backupPath : '')
                . "\nNEXT: Run Media Storage Doctor and test actual image URLs / uploads.";
        });
    }

    /** @return array{string,string,string} */
    private function validatedPaths(): array
    {
        $root = $this->normalize((string) config('filesystems.disks.public.root', ''));
        $link = $this->normalize((string) config('filesystems.public_media_link', ''));
        $expectedRoot = $this->normalize(storage_path('app/public'));
        $docroot = realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? ''));
        $links = (array) config('filesystems.links', []);

        if (!$docroot || $root !== $expectedRoot || $link === '' || $link[0] !== '/'
            || $link !== $this->normalize($docroot . '/storage')
            || $this->normalize((string) ($links[$link] ?? '')) !== $root
            || $root === $link || ! is_dir(dirname($root)) || ! is_writable(dirname($root))) {
            throw new RuntimeException('Unsafe/mismatched paths. Confirm PUBLIC_MEDIA_ROOT=Laravel storage/app/public, PUBLIC_MEDIA_LINK=actual DOCUMENT_ROOT/storage, and click Clear Config. No changes made.');
        }
        if (! is_writable($docroot)) {
            throw new RuntimeException('Website document root is not writable by PHP. Ask host to fix ownership/permissions; do not use chmod 777.');
        }
        if (is_link(dirname($link))) {
            throw new RuntimeException('Document root may not be a symlink for automatic repair.');
        }
        $backupParent = $this->normalize(dirname($docroot) . '/.favouritecolour-media-backups');
        if (str_starts_with($backupParent . '/', $this->normalize($docroot) . '/')) {
            throw new RuntimeException('Private backup location would be exposed by the website. Aborting.');
        }
        return [$root, $link, $backupParent];
    }

    /** @return array<string,string> relative_path => full_source_path */
    private function inventory(string $directory): array
    {
        if (is_link($directory)) {
            throw new RuntimeException('Source storage is a symlink; not scanning.');
        }
        $result = [];
        $walker = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        foreach ($walker as $item) {
            $path = $item->getPathname();
            $relative = substr($path, strlen(rtrim($directory, '/')) + 1);
            $relative = str_replace('\\', '/', $relative);
            if ($relative === '.gitignore' || $relative === '.gitkeep'
                || str_starts_with($relative, '.media-storage-doctor/')) {
                continue;
            }
            if ($item->isLink() || !$item->isFile()) {
                throw new RuntimeException('Unsafe entry inside existing storage: ' . $relative);
            }
            if (preg_match('~(^|/)[.]~', $relative)) {
                throw new RuntimeException('Hidden file inside existing storage must be reviewed: ' . $relative);
            }
            $extension = strtolower(pathinfo($relative, PATHINFO_EXTENSION));
            if (! in_array($extension, self::PUBLIC_EXTENSIONS, true)) {
                throw new RuntimeException('Non-media file inside existing storage (manual review needed): ' . $relative);
            }
            $result[$relative] = $path;
            if (count($result) > self::SCAN_LIMIT) {
                throw new RuntimeException('More than ' . self::SCAN_LIMIT . ' old storage files. Use cPanel File Manager or host support for a bulk migration.');
            }
        }
        ksort($result, SORT_STRING);
        return $result;
    }

    private function hasSymlinkAncestor(string $root, string $relative): bool
    {
        $path = $root;
        $parts = explode('/', $relative);
        foreach ($parts as $part) {
            $path .= '/' . $part;
            if (is_link($path)) {
                return true;
            }
        }
        return false;
    }

    private function sameContent(string $a, string $b): bool
    {
        return is_file($a) && is_file($b) && filesize($a) === filesize($b)
            && hash_file('sha256', $a) === hash_file('sha256', $b);
    }

    private function normalize(string $path): string
    {
        return rtrim(str_replace('\\', '/', $path), '/');
    }

    private function locked(callable $action): string
    {
        $lockfile = storage_path('framework/media-storage-repair.lock');
        $handle = @fopen($lockfile, 'c');
        if (!$handle) {
            throw new RuntimeException('Cannot create repair lock. Check Laravel storage/framework permissions.');
        }
        try {
            if (! flock($handle, LOCK_EX | LOCK_NB)) {
                throw new RuntimeException('Another media repair action is already running. Try again later.');
            }
            return $action();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}
