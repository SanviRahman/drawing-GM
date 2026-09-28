<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SiteSettingMediaService
{
    /**
     * Sync the branding media collections from the request.
     *
     * Uploads win over media-picker selections; collections without
     * any request input are left untouched.
     */
    public function syncFromRequest(Request $request, SiteSetting $setting): void
    {
        foreach (SiteSetting::MEDIA_COLLECTIONS as $collection) {
            $mediaIdField = $collection . '_media_id';

            if ($request->hasFile($collection)) {
                $this->clearCollection($setting, $collection);

                $setting->addMedia($request->file($collection))
                    ->usingFileName($this->safeFilename($request->file($collection)->getClientOriginalExtension()))
                    ->toMediaCollection($collection, 'public');

                continue;
            }

            if ($request->filled($mediaIdField)) {
                $media = Media::query()->findOrFail((int) $request->input($mediaIdField));

                if (! str_starts_with((string) $media->mime_type, 'image/')) {
                    throw ValidationException::withMessages([
                        $mediaIdField => 'The selected media must be an image.',
                    ]);
                }

                $this->clearCollection($setting, $collection);

                $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);

                $setting->addMedia($media->getPath())
                    ->preservingOriginal()
                    ->usingName($media->name)
                    ->usingFileName($this->safeFilename($extension))
                    ->toMediaCollection($collection, 'public');
            }
        }
    }

    /**
     * Remove every file owned by the model (used before force deletes).
     */
    public function purgeAll(SiteSetting $setting): void
    {
        foreach (SiteSetting::MEDIA_COLLECTIONS as $collection) {
            $this->clearCollection($setting, $collection);
        }
    }

    private function clearCollection(SiteSetting $setting, string $collection): void
    {
        if ($setting->hasMedia($collection)) {
            $setting->clearMediaCollection($collection);
        }
    }

    private function safeFilename(?string $extension): string
    {
        $extension = strtolower(trim((string) $extension));
        $extension = preg_replace('/[^a-z0-9]+/', '', $extension) ?: 'png';

        return Str::uuid() . '.' . $extension;
    }
}
