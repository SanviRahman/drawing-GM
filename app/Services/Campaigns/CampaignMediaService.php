<?php

namespace App\Services\Campaigns;

use App\Models\Campaign;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CampaignMediaService
{
    public function syncFromRequest(Request $request, Campaign $campaign): void
    {
        $this->syncHeroImages($request, $campaign);
        $this->syncSingle($request, $campaign, Campaign::HERO_VIDEO, 'video');
        $this->syncSingle($request, $campaign, Campaign::HERO_VIDEO_POSTER, 'image');
        $this->syncSingle($request, $campaign, Campaign::SOCIAL_IMAGE, 'image');
    }

    public function duplicate(Campaign $source, Campaign $target): void
    {
        foreach (Campaign::MEDIA_COLLECTIONS as $collection) {
            foreach ($source->getMedia($collection) as $media) {
                $media->copy($target, $collection, 'public');
            }
        }
    }

    public function purgeAll(Campaign $campaign): void
    {
        Media::withTrashed()
            ->where('model_type', $campaign->getMorphClass())
            ->where('model_id', $campaign->getKey())
            ->whereIn('collection_name', Campaign::MEDIA_COLLECTIONS)
            ->get()
            ->each(fn (Media $media) => $media->forceDelete());
    }

    private function syncHeroImages(Request $request, Campaign $campaign): void
    {
        if ($request->boolean('hero_images_clear')) {
            $campaign->clearMediaCollection(Campaign::HERO_IMAGES);
        }

        $removeIds = collect($request->input('hero_images_remove_ids', []))
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->all();

        if ($removeIds !== []) {
            $campaign->getMedia(Campaign::HERO_IMAGES)
                ->whereIn('id', $removeIds)
                ->each(fn ($media) => $media->delete());
        }

        $files = $request->file('hero_images', []);
        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        foreach (is_array($files) ? $files : [] as $file) {
            if ($file instanceof UploadedFile) {
                $campaign->addMedia($file)
                    ->usingFileName($this->safeFilename($file->getClientOriginalExtension()))
                    ->toMediaCollection(Campaign::HERO_IMAGES, 'public');
            }
        }

        if ($request->filled('hero_images_media_ids')) {
            foreach ($this->parseIds((string) $request->input('hero_images_media_ids'), 'hero_images_media_ids') as $id) {
                $media = Media::query()->find($id);
                $this->assertReusable($media, 'image', 'hero_images_media_ids');
                $media->copy($campaign, Campaign::HERO_IMAGES, 'public');
            }
        }

        if ($campaign->getMedia(Campaign::HERO_IMAGES)->count() > 12) {
            throw ValidationException::withMessages([
                'hero_images' => 'A campaign may have at most 12 hero images.',
            ]);
        }
    }

    private function syncSingle(Request $request, Campaign $campaign, string $collection, string $type): void
    {
        $fileField = $collection;
        $pickerField = $collection . '_media_id';
        $removeField = $collection . '_remove';

        if ($request->hasFile($fileField)) {
            $file = $request->file($fileField);
            if ($file instanceof UploadedFile) {
                $campaign->clearMediaCollection($collection);
                $campaign->addMedia($file)
                    ->usingFileName($this->safeFilename($file->getClientOriginalExtension()))
                    ->toMediaCollection($collection, 'public');
            }
            return;
        }

        if ($request->filled($pickerField)) {
            $media = Media::query()->find((int) $request->input($pickerField));
            $this->assertReusable($media, $type, $pickerField);
            $campaign->clearMediaCollection($collection);
            $media->copy($campaign, $collection, 'public');
            return;
        }

        if ($request->boolean($removeField)) {
            $campaign->clearMediaCollection($collection);
        }
    }

    private function assertReusable(?Media $media, string $type, string $field): void
    {
        if (! $media || ! $media->isPickerSafe()) {
            throw ValidationException::withMessages([$field => 'The selected media is unavailable or cannot be reused.']);
        }

        $valid = $type === 'image' ? $media->isImage() : $media->isVideo();
        if (! $valid) {
            throw ValidationException::withMessages([$field => "The selected media must be a {$type}."]);
        }
    }

    private function parseIds(string $json, string $field): array
    {
        try {
            $ids = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw ValidationException::withMessages([$field => 'The selected media payload is invalid.']);
        }

        $ids = collect(is_array($ids) ? $ids : [])
            ->filter(fn ($id) => is_numeric($id) && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (count($ids) > 12) {
            throw ValidationException::withMessages([$field => 'A maximum of 12 images can be selected at once.']);
        }

        return $ids;
    }

    private function safeFilename(?string $extension): string
    {
        $extension = strtolower(trim((string) $extension));
        $extension = preg_replace('/[^a-z0-9]+/', '', $extension) ?: 'bin';

        return Str::uuid() . '.' . $extension;
    }
}
