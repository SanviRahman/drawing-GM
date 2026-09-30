<?php

namespace App\Services;

use App\Models\Location;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LocationMediaService
{
    private const LIMITS = [
        'hero_desktop' => 1,
        'hero_mobile' => 1,
        'gallery' => 20,
    ];

    public function syncFromRequest(Request $request, Location $location): void
    {
        foreach (Location::MEDIA_COLLECTIONS as $collection) {
            $files = $request->file($collection);
            $pickerField = $collection . '_media_ids';
            $clearField = $collection . '_clear';
            $removeField = $collection . '_remove_ids';

            if ($files instanceof UploadedFile) {
                $files = [$files];
            }

            if (is_array($files) && count($files) > 0) {
                $this->replaceWithUploads($location, $collection, $files);
                continue;
            }

            if ($request->filled($pickerField)) {
                $mediaIds = $this->parseMediaIds((string) $request->input($pickerField), $pickerField, false);
                $this->replaceWithPickerMedia($location, $collection, $mediaIds, $pickerField);
                continue;
            }

            if ($request->boolean($clearField)) {
                $this->clearCollection($location, $collection);
                continue;
            }

            if ($request->filled($removeField)) {
                $removeIds = $this->parseMediaIds((string) $request->input($removeField), $removeField, true);
                $this->removeOwnedMedia($location, $collection, $removeIds);
            }
        }
    }

    public function duplicateMedia(Location $source, Location $target): void
    {
        foreach (Location::MEDIA_COLLECTIONS as $collection) {
            foreach ($source->getMedia($collection) as $media) {
                $media->copy($target, $collection, 'public');
            }
        }
    }

    public function purgeAll(Location $location): void
    {
        Media::withTrashed()
            ->where('model_type', $location->getMorphClass())
            ->where('model_id', $location->getKey())
            ->whereIn('collection_name', Location::MEDIA_COLLECTIONS)
            ->get()
            ->each(fn (Media $media) => $media->forceDelete());
    }

    private function replaceWithUploads(Location $location, string $collection, array $files): void
    {
        $limit = self::LIMITS[$collection] ?? 1;

        if (count($files) > $limit) {
            throw ValidationException::withMessages([$collection => "A maximum of {$limit} image(s) can be uploaded to this collection."]);
        }

        $this->clearCollection($location, $collection);

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $location->addMedia($file)->usingFileName($this->safeFilename($file->getClientOriginalExtension()))->toMediaCollection($collection, 'public');
        }
    }

    private function replaceWithPickerMedia(Location $location, string $collection, array $mediaIds, string $field): void
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
                throw ValidationException::withMessages([$field => 'Location hero/gallery media must contain images only.']);
            }
        }

        $this->clearCollection($location, $collection);

        foreach ($mediaIds as $id) {
            $media->get($id)->copy($location, $collection, 'public');
        }
    }

    private function removeOwnedMedia(Location $location, string $collection, array $mediaIds): void
    {
        if (count($mediaIds) === 0) {
            return;
        }

        Media::query()
            ->where('model_type', $location->getMorphClass())
            ->where('model_id', $location->getKey())
            ->where('collection_name', $collection)
            ->whereIn('id', $mediaIds)
            ->get()
            ->each(fn (Media $media) => $media->delete());
    }

    private function parseMediaIds(string $value, string $field, bool $allowEmpty): array
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

        if (! $allowEmpty && count($ids) === 0) {
            throw ValidationException::withMessages([$field => 'Please select at least one valid media item.']);
        }

        return $ids;
    }

    private function clearCollection(Location $location, string $collection): void
    {
        if ($location->hasMedia($collection)) {
            $location->clearMediaCollection($collection);
        }
    }

    private function safeFilename(?string $extension): string
    {
        $extension = strtolower(trim((string) $extension));
        $extension = preg_replace('/[^a-z0-9]+/', '', $extension) ?: 'jpg';

        return Str::uuid() . '.' . $extension;
    }
}
