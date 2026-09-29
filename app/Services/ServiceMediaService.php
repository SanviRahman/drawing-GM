<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ServiceMediaService
{
    private const LIMITS = [
        'hero_desktop' => 1,
        'hero_mobile' => 1,
        'gallery' => 20,
    ];

    public function syncFromRequest(Request $request, Service $service): void
    {
        foreach (Service::MEDIA_COLLECTIONS as $collection) {
            $files = $request->file($collection);
            $pickerField = $collection . '_media_ids';
            $clearField = $collection . '_clear';

            if ($files instanceof UploadedFile) {
                $files = [$files];
            }

            if (is_array($files) && count($files) > 0) {
                $this->replaceWithUploads($service, $collection, $files);
                continue;
            }

            if ($request->filled($pickerField)) {
                $mediaIds = $this->parseMediaIds((string) $request->input($pickerField), $pickerField);
                $this->replaceWithPickerMedia($service, $collection, $mediaIds, $pickerField);
                continue;
            }

            if ($request->boolean($clearField)) {
                $this->clearCollection($service, $collection);
            }
        }
    }

    public function duplicateMedia(Service $source, Service $target): void
    {
        foreach (Service::MEDIA_COLLECTIONS as $collection) {
            foreach ($source->getMedia($collection) as $media) {
                $media->copy($target, $collection, 'public');
            }
        }
    }

    public function purgeAll(Service $service): void
    {
        Media::withTrashed()
            ->where('model_type', Service::class)
            ->where('model_id', $service->getKey())
            ->whereIn('collection_name', Service::MEDIA_COLLECTIONS)
            ->get()
            ->each(fn (Media $media) => $media->forceDelete());
    }

    private function replaceWithUploads(Service $service, string $collection, array $files): void
    {
        $limit = self::LIMITS[$collection] ?? 1;

        if (count($files) > $limit) {
            throw ValidationException::withMessages([$collection => "A maximum of {$limit} image(s) can be uploaded to this collection."]);
        }

        $this->clearCollection($service, $collection);

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $service->addMedia($file)->usingFileName($this->safeFilename($file->getClientOriginalExtension()))->toMediaCollection($collection, 'public');
        }
    }

    private function replaceWithPickerMedia(Service $service, string $collection, array $mediaIds, string $field): void
    {
        $limit = self::LIMITS[$collection] ?? 1;

        if (count($mediaIds) > $limit) {
            throw ValidationException::withMessages([$field => "A maximum of {$limit} image(s) can be selected for this collection."]);
        }

        $media = Media::query()->whereIn('id', $mediaIds)->get()->keyBy('id');

        foreach ($mediaIds as $id) {
            $item = $media->get($id);

            if (! $item || ! $item->isPickerSafe()) {
                throw ValidationException::withMessages([$field => 'One or more selected media items are unavailable or cannot be reused.']);
            }

            if (! $item->isImage()) {
                throw ValidationException::withMessages([$field => 'Service hero/gallery media must contain images only.']);
            }
        }

        $this->clearCollection($service, $collection);

        foreach ($mediaIds as $id) {
            $media->get($id)->copy($service, $collection, 'public');
        }
    }

    private function parseMediaIds(string $value, string $field): array
    {
        try {
            $ids = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw ValidationException::withMessages([$field => 'The selected media payload is invalid.']);
        }

        if (! is_array($ids)) {
            throw ValidationException::withMessages([$field => 'The selected media payload must be a list of media IDs.']);
        }

        $ids = collect($ids)
            ->filter(fn ($id) => is_numeric($id) && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (count($ids) === 0) {
            throw ValidationException::withMessages([$field => 'Please select at least one valid media item.']);
        }

        return $ids;
    }

    private function clearCollection(Service $service, string $collection): void
    {
        if ($service->hasMedia($collection)) {
            $service->clearMediaCollection($collection);
        }
    }

    private function safeFilename(?string $extension): string
    {
        $extension = strtolower(trim((string) $extension));
        $extension = preg_replace('/[^a-z0-9]+/', '', $extension) ?: 'jpg';

        return Str::uuid() . '.' . $extension;
    }
}
