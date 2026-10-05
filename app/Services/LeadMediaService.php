<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LeadMediaService
{
    /**
     * @param array<int, UploadedFile> $files
     */
    public function addUploads(Lead $lead, array $files): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $lead->addMedia($file)
                ->usingFileName($this->safeFilename($file->getClientOriginalExtension()))
                ->toMediaCollection(Lead::ATTACHMENTS_COLLECTION, 'private');
        }
    }

    /**
     * Copy reusable public images selected in the global Media Picker into
     * the Lead-owned private attachment collection. The original media row
     * remains unchanged.
     *
     * @param array<int, int|string> $mediaIds
     */
    public function addPickedImages(Lead $lead, array $mediaIds): void
    {
        $ids = collect($mediaIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        foreach ($ids as $mediaId) {
            $media = Media::query()
                ->pickerSafe()
                ->whereKey($mediaId)
                ->first();

            if (! $media || ! $media->isImage()) {
                throw ValidationException::withMessages([
                    'attachment_media_ids' => 'One or more selected Media Picker items are unavailable or are not reusable images.',
                ]);
            }

            $media->copy(
                $lead,
                Lead::ATTACHMENTS_COLLECTION,
                'private',
            );
        }
    }

    public function syncFromRequest(Request $request, Lead $lead): void
    {
        $this->removeSelected(
            $lead,
            collect($request->input('remove_attachment_ids', []))
                ->map(fn ($id) => (int) $id)
                ->filter()
                ->unique()
                ->all(),
        );

        /** @var array<int, UploadedFile> $files */
        $files = $request->file('attachments', []);
        $this->addUploads($lead, is_array($files) ? $files : []);

        $this->addPickedImages(
            $lead,
            $request->input('attachment_media_ids', []),
        );
    }

    /**
     * @param array<int, int> $mediaIds
     */
    public function removeSelected(Lead $lead, array $mediaIds): void
    {
        if ($mediaIds === []) {
            return;
        }

        Media::query()
            ->where('model_type', $lead->getMorphClass())
            ->where('model_id', $lead->id)
            ->where('collection_name', Lead::ATTACHMENTS_COLLECTION)
            ->whereIn('id', $mediaIds)
            ->get()
            ->each(fn (Media $media) => $media->delete());
    }

    public function findOwnedAttachmentOrFail(Lead $lead, int $mediaId): Media
    {
        $media = Media::query()
            ->whereKey($mediaId)
            ->where('model_type', $lead->getMorphClass())
            ->where('model_id', $lead->id)
            ->where('collection_name', Lead::ATTACHMENTS_COLLECTION)
            ->first();

        if (! $media) {
            throw ValidationException::withMessages([
                'attachment' => 'The requested attachment is unavailable for this lead.',
            ]);
        }

        return $media;
    }

    public function purgeAll(Lead $lead): void
    {
        Media::withTrashed()
            ->where('model_type', $lead->getMorphClass())
            ->where('model_id', $lead->id)
            ->where('collection_name', Lead::ATTACHMENTS_COLLECTION)
            ->get()
            ->each(fn (Media $media) => $media->forceDelete());
    }

    private function safeFilename(?string $extension): string
    {
        $extension = strtolower(trim((string) $extension));
        $extension = preg_replace('/[^a-z0-9]+/', '', $extension) ?: 'bin';

        return Str::uuid() . '.' . $extension;
    }
}
