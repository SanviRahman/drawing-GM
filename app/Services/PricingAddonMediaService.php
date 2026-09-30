<?php

namespace App\Services;

use App\Models\Media;
use App\Models\PricingAddon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PricingAddonMediaService
{
    public function syncFromRequest(Request $request, PricingAddon $pricingAddon): void
    {
        if ($request->hasFile('image')) {
            $pricingAddon->addMedia($request->file('image'))
                ->usingFileName($this->safeFilename($request->file('image')->getClientOriginalExtension()))
                ->toMediaCollection(PricingAddon::MEDIA_COLLECTION, 'public');

            return;
        }

        if ($request->filled('image_media_id')) {
            $media = Media::query()->find((int) $request->input('image_media_id'));

            if (! $media || ! $media->isPickerSafe()) {
                throw ValidationException::withMessages([
                    'image_media_id' => 'The selected media is unavailable or cannot be reused.',
                ]);
            }

            if (! $media->isImage()) {
                throw ValidationException::withMessages([
                    'image_media_id' => 'The selected media must be an image.',
                ]);
            }

            $pricingAddon->clearMediaCollection(PricingAddon::MEDIA_COLLECTION);
            $media->copy($pricingAddon, PricingAddon::MEDIA_COLLECTION, 'public');

            return;
        }

        if ($request->boolean('image_remove')) {
            $pricingAddon->clearMediaCollection(PricingAddon::MEDIA_COLLECTION);
        }
    }

    public function duplicateMedia(PricingAddon $source, PricingAddon $target): void
    {
        $media = $source->getFirstMedia(PricingAddon::MEDIA_COLLECTION);

        if ($media) {
            $media->copy($target, PricingAddon::MEDIA_COLLECTION, 'public');
        }
    }

    public function purgeAll(PricingAddon $pricingAddon): void
    {
        Media::withTrashed()
            ->where('model_type', $pricingAddon->getMorphClass())
            ->where('model_id', $pricingAddon->getKey())
            ->where('collection_name', PricingAddon::MEDIA_COLLECTION)
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
