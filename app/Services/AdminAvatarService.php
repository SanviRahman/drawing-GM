<?php

namespace App\Services;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class AdminAvatarService
{
    public function syncFromRequest(Request $request, Admin $admin): void
    {
        if (! $request->hasFile('photo') && ! $request->filled('photo_media_id')) {
            return;
        }

        if ($request->hasFile('photo')) {
            $admin->addMedia($request->file('photo'))
                ->usingFileName($this->safeFilename($request->file('photo')->getClientOriginalExtension()))
                ->toMediaCollection('avatars', 'public');

            $this->clearLegacyPhoto($admin);

            return;
        }

        $media = Media::query()->findOrFail((int) $request->input('photo_media_id'));

        if (! str_starts_with((string) $media->mime_type, 'image/')) {
            throw ValidationException::withMessages([
                'photo_media_id' => 'The selected media must be an image.',
            ]);
        }

        $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);

        $admin->addMedia($media->getPath())
            ->preservingOriginal()
            ->usingName($media->name)
            ->usingFileName($this->safeFilename($extension))
            ->toMediaCollection('avatars', 'public');

        $this->clearLegacyPhoto($admin);
    }

    private function clearLegacyPhoto(Admin $admin): void
    {
        if (! empty($admin->photo)) {
            $photo = ltrim((string) $admin->photo, '/');

            if (! str_starts_with($photo, 'http://')
                && ! str_starts_with($photo, 'https://')
                && ! str_starts_with($photo, 'uploads/')
                && Storage::disk('public')->exists($photo)) {
                Storage::disk('public')->delete($photo);
            }
        }

        if ($admin->photo !== null) {
            $admin->forceFill(['photo' => null])->save();
        }
    }

    private function safeFilename(?string $extension): string
    {
        $extension = strtolower(trim((string) $extension));
        $extension = preg_replace('/[^a-z0-9]+/', '', $extension) ?: 'jpg';

        return Str::uuid() . '.' . $extension;
    }
}
