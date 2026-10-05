<?php

namespace App\Services\Seo;

use App\Models\Media;
use App\Models\SeoMeta;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SeoMetaMediaService
{
    public function syncFromRequest(Request $request, SeoMeta $seoMeta): void
    {
        if ($request->hasFile('social_image')) {
            $file = $request->file('social_image');

            if ($file instanceof UploadedFile) {
                $seoMeta->clearMediaCollection(SeoMeta::SOCIAL_IMAGE_COLLECTION);
                $seoMeta->addMedia($file)
                    ->usingFileName($this->safeFilename($file->getClientOriginalExtension()))
                    ->toMediaCollection(SeoMeta::SOCIAL_IMAGE_COLLECTION, 'public');
            }

            return;
        }

        if ($request->filled('social_image_media_id')) {
            $media = Media::query()->find((int) $request->input('social_image_media_id'));

            if (! $media || ! $media->isPickerSafe() || ! $media->isImage()) {
                throw ValidationException::withMessages([
                    'social_image_media_id' => 'The selected media must be an active reusable image.',
                ]);
            }

            $seoMeta->clearMediaCollection(SeoMeta::SOCIAL_IMAGE_COLLECTION);
            $media->copy($seoMeta, SeoMeta::SOCIAL_IMAGE_COLLECTION, 'public');

            return;
        }

        if ($request->boolean('social_image_remove')) {
            $seoMeta->clearMediaCollection(SeoMeta::SOCIAL_IMAGE_COLLECTION);
        }
    }

    public function purgeAll(SeoMeta $seoMeta): void
    {
        Media::withTrashed()
            ->where('model_type', $seoMeta->getMorphClass())
            ->where('model_id', $seoMeta->getKey())
            ->where('collection_name', SeoMeta::SOCIAL_IMAGE_COLLECTION)
            ->get()
            ->each(fn (Media $media) => $media->forceDelete());
    }

    private function safeFilename(?string $extension): string
    {
        $extension = strtolower(trim((string) $extension));
        $extension = preg_replace('/[^a-z0-9]+/', '', $extension) ?: 'jpg';

        return Str::uuid() . '.' . $extension;
    }
}
