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
        'html',
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

    protected $attributes = [
        'value_type' => 'string',
        'is_public'  => false,
    ];

    protected function casts(): array
    {
        return [
            'is_public'  => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('group_name')->orderBy('setting_key');
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

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

    public static function prepareValue(?string $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = (string) $value;

        return match ($type) {
            'boolean'   => filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
            'integer'   => (string) (int) $value,
            'encrypted' => $value === '' ? null : Crypt::encryptString($value),
            default     => $value,
        };
    }

    public function resolveValue(): mixed
    {
        $value = $this->setting_value;

        if ($value === null || $value === '') {
            return match ($this->value_type) {
                'boolean' => false,
                default   => null,
            };
        }

        return match ($this->value_type) {
            'boolean'   => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer'   => (int) $value,
            'json'      => json_decode($value),
            'encrypted' => $this->decryptValue($value),
            default     => $value,
        };
    }

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

        return Str::limit(strip_tags((string) $value), $limit);
    }

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