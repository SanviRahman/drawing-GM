<?php

namespace App\Services\Media;

use App\Models\Admin;
use App\Models\Media;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaLibraryService
{
    public function paginate(array $filters = [], bool $trashed = false, bool $picker = false): LengthAwarePaginator
    {
        $query = $trashed
            ? Media::onlyTrashed()
            : Media::query();

        if ($picker) {
            $query->pickerSafe();
        }

        $this->applyFilters($query, $filters);

        $perPage = min(max((int) ($filters['per_page'] ?? 24), 6), 60);

        return $query
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param array<int, UploadedFile> $files
     * @return Collection<int, Media>
     */
    public function upload(Admin $admin, array $files): Collection
    {
        return collect($files)->map(function (UploadedFile $file) use ($admin): Media {
            $extension = strtolower((string) $file->getClientOriginalExtension());
            $extension = preg_replace('/[^a-z0-9]+/', '', $extension) ?: 'bin';
            $originalName = $file->getClientOriginalName();
            $displayName = trim((string) pathinfo($originalName, PATHINFO_FILENAME)) ?: 'Media file';

            /** @var Media $media */
            $media = $admin
                ->addMedia($file)
                ->usingName(Str::limit($displayName, 180, ''))
                ->usingFileName(Str::uuid() . '.' . $extension)
                ->withCustomProperties([
                    'uploaded_by_admin_id' => $admin->id,
                    'original_name' => $originalName,
                    'source' => 'global_media_picker',
                ])
                ->toMediaCollection('media_library', 'public');

            return $media;
        });
    }

    public function reassignLibraryMedia(Admin $from, Admin $to): void
    {
        if ((int) $from->getKey() === (int) $to->getKey()) {
            return;
        }

        Media::withTrashed()
            ->where('model_type', Admin::class)
            ->where('model_id', $from->getKey())
            ->where('collection_name', 'media_library')
            ->update([
                'model_id' => $to->getKey(),
                'updated_at' => now(),
            ]);
    }

    public function updateMetadata(Media $media, array $data): Media
    {
        $media->name = trim((string) $data['name']);

        $properties = $media->custom_properties ?? [];
        $properties['alt_text'] = trim((string) ($data['alt_text'] ?? ''));
        $properties['caption'] = trim((string) ($data['caption'] ?? ''));

        $media->custom_properties = $properties;
        $media->save();

        return $media->refresh();
    }

    public function trash(Media $media): void
    {
        $media->delete();
    }

    public function restore(int $id): Media
    {
        $media = Media::onlyTrashed()->findOrFail($id);
        $media->restore();

        return $media;
    }

    public function forceDelete(int $id): void
    {
        $media = Media::onlyTrashed()->findOrFail($id);
        $media->forceDelete();
    }

    public function bulk(string $action, array $ids): string
    {
        $ids = collect($ids)->map(fn ($id) => (int) $id)->unique()->values()->all();

        return match ($action) {
            'delete' => $this->bulkDelete($ids),
            'restore' => $this->bulkRestore($ids),
            'force_delete' => $this->bulkForceDelete($ids),
            default => 'No action was performed.',
        };
    }

    /**
     * @return array{total:int,images:int,videos:int,storage_bytes:int,storage_human:string}
     */
    public function stats(): array
    {
        $base = Media::query();
        $storage = (int) (clone $base)->sum('size');

        return [
            'total' => (clone $base)->count(),
            'images' => (clone $base)->where('mime_type', 'like', 'image/%')->count(),
            'videos' => (clone $base)->where('mime_type', 'like', 'video/%')->count(),
            'storage_bytes' => $storage,
            'storage_human' => $this->humanBytes($storage),
        ];
    }

    /**
     * @return array{collections:Collection<int,string>,disks:Collection<int,string>,owners:Collection<int,array{value:string,label:string}>}
     */
    public function filters(bool $picker = false): array
    {
        $query = Media::withTrashed();

        if ($picker) {
            $query->pickerSafe();
        }

        $collections = (clone $query)
            ->whereNotNull('collection_name')
            ->distinct()
            ->orderBy('collection_name')
            ->pluck('collection_name')
            ->values();

        $disks = (clone $query)
            ->whereNotNull('disk')
            ->distinct()
            ->orderBy('disk')
            ->pluck('disk')
            ->values();

        $owners = (clone $query)
            ->select('model_type')
            ->whereNotNull('model_type')
            ->distinct()
            ->orderBy('model_type')
            ->pluck('model_type')
            ->map(fn (string $type) => [
                'value' => $type,
                'label' => class_basename($type),
            ])
            ->values();

        return compact('collections', 'disks', 'owners');
    }

    /**
     * @return array<string, mixed>
     */
    public function serialize(Media $media): array
    {
        $url = null;

        if ($media->isPickerSafe()) {
            try {
                $url = $media->getUrl();
            } catch (\Throwable) {
                $url = null;
            }
        }

        $properties = $media->custom_properties ?? [];

        return [
            'id' => $media->id,
            'name' => $media->name,
            'file_name' => $media->file_name,
            'mime_type' => $media->mime_type,
            'type' => $media->isImage() ? 'image' : ($media->isVideo() ? 'video' : 'file'),
            'collection' => $media->collection_name,
            'disk' => $media->disk,
            'size' => (int) $media->size,
            'size_human' => $this->humanBytes((int) $media->size),
            'owner_type' => class_basename((string) $media->model_type),
            'owner_label' => $this->ownerLabel($media),
            'url' => $url,
            'alt_text' => (string) ($properties['alt_text'] ?? ''),
            'caption' => (string) ($properties['caption'] ?? ''),
            'is_picker_safe' => $media->isPickerSafe(),
            'deleted_at' => optional($media->deleted_at)->toIso8601String(),
            'created_at' => optional($media->created_at)->toIso8601String(),
        ];
    }

    public function download(Media $media)
    {
        $disk = Storage::disk($media->disk);
        $relativePath = $media->getPathRelativeToRoot();

        abort_unless($disk->exists($relativePath), 404, 'Media file was not found on storage.');

        return $disk->download($relativePath, $media->file_name);
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $search = trim((string) ($filters['search'] ?? ''));

        if ($search !== '') {
            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('file_name', 'like', "%{$search}%")
                    ->orWhere('collection_name', 'like', "%{$search}%")
                    ->orWhere('model_type', 'like', "%{$search}%")
                    ->orWhere('mime_type', 'like', "%{$search}%");
            });
        }

        $type = (string) ($filters['type'] ?? '');

        if ($type === 'image') {
            $query->where('mime_type', 'like', 'image/%');
        } elseif ($type === 'video') {
            $query->where('mime_type', 'like', 'video/%');
        } elseif ($type === 'file') {
            $query
                ->where('mime_type', 'not like', 'image/%')
                ->where('mime_type', 'not like', 'video/%');
        }

        if (! empty($filters['collection'])) {
            $query->where('collection_name', (string) $filters['collection']);
        }

        if (! empty($filters['disk'])) {
            $query->where('disk', (string) $filters['disk']);
        }

        if (! empty($filters['owner'])) {
            $query->where('model_type', (string) $filters['owner']);
        }
    }

    private function bulkDelete(array $ids): string
    {
        Media::query()->whereIn('id', $ids)->get()->each(fn (Media $media) => $media->delete());

        return 'Selected media moved to trash. Physical files are retained until force delete.';
    }

    private function bulkRestore(array $ids): string
    {
        Media::onlyTrashed()->whereIn('id', $ids)->get()->each(fn (Media $media) => $media->restore());

        return 'Selected media restored successfully.';
    }

    private function bulkForceDelete(array $ids): string
    {
        Media::onlyTrashed()->whereIn('id', $ids)->get()->each(fn (Media $media) => $media->forceDelete());

        return 'Selected media permanently deleted from database and storage.';
    }

    private function ownerLabel(Media $media): string
    {
        try {
            $owner = $media->model;
        } catch (\Throwable) {
            $owner = null;
        }

        if ($owner) {
            foreach (['name', 'title', 'setting_key', 'label'] as $attribute) {
                $value = $owner->{$attribute} ?? null;

                if (is_string($value) && trim($value) !== '') {
                    return class_basename($media->model_type) . ': ' . Str::limit($value, 45);
                }
            }
        }

        return class_basename((string) $media->model_type) . ' #' . $media->model_id;
    }

    private function humanBytes(int $bytes): string
    {
        if ($bytes <= 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = min((int) floor(log($bytes, 1024)), count($units) - 1);
        $value = $bytes / (1024 ** $power);

        return number_format($value, $power === 0 ? 0 : 1) . ' ' . $units[$power];
    }
}
