<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PageMediaService
{
    public function syncFromRequest(Request $request, Page $page): void
    {
        foreach (Page::HERO_COLLECTIONS as $collection) {
            $files = $request->file($collection, []);
            $pickerField = $collection . '_media_ids';
            $clearField = $collection . '_clear';

            if ($files instanceof UploadedFile) {
                $files = [$files];
            }

            if (is_array($files) && count($files) > 0) {
                $this->clearCollection($page, $collection);

                foreach ($files as $file) {
                    $page->addMedia($file)->usingFileName($this->safeFilename($file->getClientOriginalExtension()))->toMediaCollection($collection, 'public');
                }

                continue;
            }

            if ($request->filled($pickerField)) {
                $mediaIds = $this->parseMediaIds((string) $request->input($pickerField), $pickerField);
                $this->replaceWithPickerMedia($page, $collection, $mediaIds, $pickerField);
                continue;
            }

            if ($request->boolean($clearField)) {
                $this->clearCollection($page, $collection);
            }
        }
    }

    public function duplicateHeroMedia(Page $source, Page $target): void
    {
        foreach (Page::HERO_COLLECTIONS as $collection) {
            foreach ($source->getMedia($collection) as $media) {
                $media->copy($target, $collection, 'public');
            }
        }
    }

    public function purgeAll(Page $page): void
    {
        Media::withTrashed()->where('model_type', Page::class)->where('model_id', $page->getKey())->whereIn('collection_name', Page::HERO_COLLECTIONS)->get()->each(fn (Media $media) => $media->forceDelete());
    }

    private function replaceWithPickerMedia(Page $page, string $collection, array $mediaIds, string $field): void
    {
        if (count($mediaIds) > 10) {
            throw ValidationException::withMessages([$field => 'A maximum of 10 images can be selected for one hero collection.']);
        }

        $media = Media::query()->whereIn('id', $mediaIds)->get()->keyBy('id');

        foreach ($mediaIds as $id) {
            $item = $media->get($id);

            if (! $item || ! $item->isPickerSafe()) {
                throw ValidationException::withMessages([$field => 'One or more selected media items are unavailable or cannot be reused.']);
            }

            if (! $item->isImage()) {
                throw ValidationException::withMessages([$field => 'Hero media must contain images only.']);
            }
        }

        $this->clearCollection($page, $collection);

        foreach ($mediaIds as $id) {
            $media->get($id)->copy($page, $collection, 'public');
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

        $ids = collect($ids)->filter(fn ($id) => is_numeric($id) && (int) $id > 0)->map(fn ($id) => (int) $id)->unique()->values()->all();

        if (count($ids) === 0) {
            throw ValidationException::withMessages([$field => 'Please select at least one valid media item.']);
        }

        return $ids;
    }

    private function clearCollection(Page $page, string $collection): void
    {
        if ($page->hasMedia($collection)) {
            $page->clearMediaCollection($collection);
        }
    }

    private function safeFilename(?string $extension): string
    {
        $extension = strtolower(trim((string) $extension));
        $extension = preg_replace('/[^a-z0-9]+/', '', $extension) ?: 'jpg';

        return Str::uuid() . '.' . $extension;
    }
}
