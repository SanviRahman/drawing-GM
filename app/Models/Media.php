<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\MediaCollections\Models\Media as BaseMedia;

class Media extends BaseMedia
{
    use SoftDeletes;

    public const PATH_SCHEME_PROPERTY = 'storage_path_scheme';

    public const PATH_SCHEME_MODEL_SECTION = 'model_section_v1';

    /**
     * Collections that must never be exposed by the reusable global picker.
     * They can still be managed from the dedicated media manager by an
     * authorized administrator.
     *
     * @var array<int, string>
     */
    public const PRIVATE_COLLECTIONS = [
        'lead_attachments',
        'attachments',
    ];


    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $media): void {
            $properties = is_array($media->custom_properties) ? $media->custom_properties : [];

            if (! array_key_exists(self::PATH_SCHEME_PROPERTY, $properties)) {
                $properties[self::PATH_SCHEME_PROPERTY] = self::PATH_SCHEME_MODEL_SECTION;
                $media->custom_properties = $properties;
            }
        });
    }

    public function usesModelSectionPath(): bool
    {
        return $this->getCustomProperty(self::PATH_SCHEME_PROPERTY) === self::PATH_SCHEME_MODEL_SECTION;
    }

    public function scopePickerSafe(Builder $query): Builder
    {
        return $query
            ->whereNotIn('collection_name', self::PRIVATE_COLLECTIONS)
            ->where('disk', 'not like', '%private%');
    }

    public function isPickerSafe(): bool
    {
        return ! in_array($this->collection_name, self::PRIVATE_COLLECTIONS, true)
            && ! str_contains(strtolower((string) $this->disk), 'private');
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with((string) $this->mime_type, 'video/');
    }
}
