<?php

namespace App\Models;

use App\Casts\EncryptedJson;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrackingProvider extends Model
{
    use HasFactory, SoftDeletes;

    public const PROVIDERS = [
        'meta_pixel' => 'Meta Pixel',
        'meta_capi' => 'Meta Conversions API',
        'ga4' => 'Google Analytics 4',
        'gtm' => 'Google Tag Manager',
        'tiktok' => 'TikTok Pixel',
    ];

    protected $fillable = [
        'provider',
        'public_identifier',
        'secret',
        'config',
        'is_enabled',
        'test_mode',
    ];

    protected $hidden = [
        'secret',
        'config',
    ];

    protected $attributes = [
        'is_enabled' => false,
        'test_mode' => true,
    ];

    protected function casts(): array
    {
        return [
            'secret' => 'encrypted',
            'config' => EncryptedJson::class,
            'is_enabled' => 'boolean',
            'test_mode' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function eventRules(): HasMany
    {
        return $this->hasMany(TrackingEventRule::class);
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('provider')->orderBy('id');
    }

    public function label(): string
    {
        return self::PROVIDERS[$this->provider] ?? $this->provider;
    }

    public function metaPixels(): array
    {
        $pixels = data_get($this->config ?? [], 'meta_pixels', []);

        return is_array($pixels) ? array_values($pixels) : [];
    }

    public function publicConfig(): array
    {
        $config = $this->config ?? [];

        if ($this->provider === 'meta_pixel') {
            return [
                'meta_pixels' => collect($this->metaPixels())
                    ->filter(fn ($pixel) => (bool) ($pixel['is_active'] ?? true))
                    ->map(fn ($pixel) => [
                        'label' => (string) ($pixel['label'] ?? ''),
                        'pixel_id' => (string) ($pixel['pixel_id'] ?? ''),
                    ])
                    ->filter(fn ($pixel) => $pixel['pixel_id'] !== '')
                    ->values()
                    ->all(),
            ];
        }

        return [];
    }
}
