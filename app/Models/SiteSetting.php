<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SiteSetting extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $table = 'site_settings';

    /**
     * Supported setting value types.
     */
    public const VALUE_TYPES = [
        'string',
        'boolean',
        'integer',
        'json',
        'encrypted',
    ];

    /**
     * Media collections owned by the site settings module.
     */
    public const MEDIA_COLLECTIONS = [
        'site_logo',
        'site_favicon',
        'default_hero',
    ];

    protected $fillable = [
        'group_name',
        'setting_key',
        'setting_value',
        'value_type',
        'is_public',
    ];

    /**
     * Attribute defaults mirroring the database column defaults.
     */
    protected $attributes = [
        'value_type' => 'string',
        'is_public' => false,
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeOrdered($query)
    {
        return $query->orderBy('group_name')->orderBy('setting_key');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Media collections
    |--------------------------------------------------------------------------
    */

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('site_logo')
            ->useDisk('public')
            ->singleFile();

        $this->addMediaCollection('site_favicon')
            ->useDisk('public')
            ->singleFile();

        $this->addMediaCollection('default_hero')
            ->useDisk('public')
            ->singleFile();
    }

    public function collectionUrl(string $collection): string
    {
        $url = $this->getFirstMediaUrl($collection);

        return $url !== '' ? $url : asset('images/no-image.png');
    }

    /*
    |--------------------------------------------------------------------------
    | Typed value handling
    |--------------------------------------------------------------------------
    */

    /**
     * Normalize and secure a raw value before persisting it.
     */
    public static function prepareValue(?string $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = (string) $value;

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
            'integer' => (string) (int) $value,
            'encrypted' => $value === '' ? null : Crypt::encryptString($value),
            default => $value,
        };
    }

    /**
     * Resolve the stored value into its typed runtime representation.
     */
    public function resolveValue(): mixed
    {
        $value = $this->setting_value;

        if ($value === null || $value === '') {
            return match ($this->value_type) {
                'boolean' => false,
                default => null,
            };
        }

        return match ($this->value_type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'json' => json_decode($value),
            'encrypted' => $this->decryptValue($value),
            default => $value,
        };
    }

    /**
     * Safe presentation value for backoffice listing/detail views.
     * Encrypted values are never exposed.
     */
    public function presentValue(int $limit = 80): string
    {
        $value = $this->setting_value;

        if ($value === null || $value === '') {
            return '—';
        }

        if ($this->value_type === 'encrypted') {
            return '•••••••• (encrypted)';
        }

        if ($this->value_type === 'boolean') {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
        }

        return Str::limit((string) $value, $limit);
    }

    /**
     * Read a setting value by key for application usage.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::query()->where('setting_key', $key)->first();

        return $setting?->resolveValue() ?? $default;
    }

    private function decryptValue(string $value): ?string
    {
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return null;
        }
    }
}
