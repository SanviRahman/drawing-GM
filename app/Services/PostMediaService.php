<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PostMediaService
{
    public function syncFromRequest(Request $request, Post $post): void
    {
        $this->syncFeatured($request, $post);
        $this->syncContentImages($request, $post);
    }

    public function duplicateMedia(Post $source, Post $target): void
    {
        foreach (Post::MEDIA_COLLECTIONS as $collection) {
            foreach ($source->getMedia($collection) as $media) {
                $media->copy($target, $collection, 'public');
            }
        }
    }

    public function purgeAll(Post $post): void
    {
        Media::withTrashed()
            ->where('model_type', $post->getMorphClass())
            ->where('model_id', $post->getKey())
            ->whereIn('collection_name', Post::MEDIA_COLLECTIONS)
            ->get()
            ->each(fn (Media $media) => $media->forceDelete());
    }

    private function syncFeatured(Request $request, Post $post): void
    {
        if ($request->hasFile('featured')) {
            $file = $request->file('featured');

            if ($file instanceof UploadedFile) {
                $post->clearMediaCollection(Post::FEATURED_COLLECTION);
                $post->addMedia($file)
                    ->usingFileName($this->safeFilename($file->getClientOriginalExtension()))
                    ->toMediaCollection(Post::FEATURED_COLLECTION, 'public');
            }

            return;
        }

        if ($request->filled('featured_media_id')) {
            $media = $this->pickerImage(
                (int) $request->input('featured_media_id'),
                'featured_media_id'
            );

            $post->clearMediaCollection(Post::FEATURED_COLLECTION);
            $media->copy($post, Post::FEATURED_COLLECTION, 'public');

            return;
        }

        if ($request->boolean('featured_remove')) {
            $post->clearMediaCollection(Post::FEATURED_COLLECTION);
        }
    }

    private function syncContentImages(Request $request, Post $post): void
    {
        $pickerMedia = collect();

        if ($request->filled('content_images_media_ids')) {
            $mediaIds = $this->parseMediaIds(
                (string) $request->input('content_images_media_ids'),
                'content_images_media_ids',
                true
            );

            foreach ($mediaIds as $mediaId) {
                $pickerMedia->push(
                    $this->pickerImage($mediaId, 'content_images_media_ids')
                );
            }
        }

        if ($request->boolean('content_images_clear')) {
            $post->clearMediaCollection(Post::CONTENT_IMAGES_COLLECTION);
        } elseif ($request->filled('content_images_remove_ids')) {
            $removeIds = $this->parseMediaIds(
                (string) $request->input('content_images_remove_ids'),
                'content_images_remove_ids',
                true
            );

            $this->removeOwnedMedia($post, $removeIds);
        }

        $files = $request->file('content_images', []);
        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        if (is_array($files)) {
            foreach ($files as $file) {
                if (! $file instanceof UploadedFile) {
                    continue;
                }

                $post->addMedia($file)
                    ->usingFileName($this->safeFilename($file->getClientOriginalExtension()))
                    ->toMediaCollection(Post::CONTENT_IMAGES_COLLECTION, 'public');
            }
        }

        $pickerMedia->each(
            fn (Media $media) => $media->copy(
                $post,
                Post::CONTENT_IMAGES_COLLECTION,
                'public'
            )
        );
    }

    private function pickerImage(int $mediaId, string $field): Media
    {
        $media = Media::query()->find($mediaId);

        if (! $media || ! $media->isPickerSafe()) {
            throw ValidationException::withMessages([
                $field => 'One or more selected media items are unavailable or cannot be reused.',
            ]);
        }

        if (! $media->isImage()) {
            throw ValidationException::withMessages([
                $field => 'Post media must contain images only.',
            ]);
        }

        return $media;
    }

    private function removeOwnedMedia(Post $post, array $mediaIds): void
    {
        if ($mediaIds === []) {
            return;
        }

        Media::query()
            ->where('model_type', $post->getMorphClass())
            ->where('model_id', $post->getKey())
            ->where('collection_name', Post::CONTENT_IMAGES_COLLECTION)
            ->whereIn('id', $mediaIds)
            ->get()
            ->each(fn (Media $media) => $media->delete());
    }

    private function parseMediaIds(string $value, string $field, bool $allowEmpty): array
    {
        try {
            $ids = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw ValidationException::withMessages([
                $field => 'The selected media payload is invalid.',
            ]);
        }

        if (! is_array($ids)) {
            throw ValidationException::withMessages([
                $field => 'The selected media payload must be a list of media IDs.',
            ]);
        }

        $ids = collect($ids)
            ->filter(fn ($id) => is_numeric($id) && (int) $id > 0)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (! $allowEmpty && $ids === []) {
            throw ValidationException::withMessages([
                $field => 'Please select at least one valid media item.',
            ]);
        }

        return $ids;
    }

    private function safeFilename(?string $extension): string
    {
        $extension = strtolower(trim((string) $extension));
        $extension = preg_replace('/[^a-z0-9]+/', '', $extension) ?: 'jpg';

        return Str::uuid() . '.' . $extension;
    }
}
