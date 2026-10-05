<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SeoMeta extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    public const SOCIAL_IMAGE_COLLECTION = 'social_image';

    public const SEOABLE_TYPES = [
        'page' => Page::class,
        'service' => Service::class,
        'location' => Location::class,
        'post' => Post::class,
        'category' => Category::class,
    ];

    public const SEOABLE_LABELS = [
        'page' => 'Page',
        'service' => 'Service',
        'location' => 'Location',
        'post' => 'Blog Post',
        'category' => 'Blog Category',
    ];

    public const ROBOTS = [
        'index,follow' => 'Index, Follow',
        'noindex,follow' => 'Noindex, Follow',
        'index,nofollow' => 'Index, Nofollow',
        'noindex,nofollow' => 'Noindex, Nofollow',
    ];

    public const CHANGEFREQ = [
        'always' => 'Always',
        'hourly' => 'Hourly',
        'daily' => 'Daily',
        'weekly' => 'Weekly',
        'monthly' => 'Monthly',
        'yearly' => 'Yearly',
        'never' => 'Never',
    ];

    protected $fillable = [
        'seoable_type',
        'seoable_id',
        'meta_title',
        'meta_description',
        'canonical_url',
        'robots',
        'og_title',
        'og_description',
        'schema_overrides',
        'include_in_sitemap',
        'sitemap_priority',
        'sitemap_changefreq',
    ];

    protected $attributes = [
        'robots' => 'index,follow',
        'include_in_sitemap' => true,
    ];

    protected function casts(): array
    {
        return [
            'seoable_id' => 'integer',
            'schema_overrides' => 'array',
            'include_in_sitemap' => 'boolean',
            'sitemap_priority' => 'decimal:1',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::SOCIAL_IMAGE_COLLECTION)
            ->useDisk('public')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    public function seoable(): MorphTo
    {
        return $this->morphTo()->withTrashed();
    }

    public static function typeClass(string $key): ?string
    {
        return self::SEOABLE_TYPES[$key] ?? null;
    }

    public static function typeKeyForClass(string $class): ?string
    {
        return array_search($class, self::SEOABLE_TYPES, true) ?: null;
    }

    public function typeKey(): ?string
    {
        foreach (self::SEOABLE_TYPES as $key => $class) {
            if ($this->seoable_type === $class || $this->seoable_type === (new $class)->getMorphClass()) {
                return $key;
            }
        }

        return null;
    }

    public function ownerLabel(): string
    {
        $owner = $this->seoable;

        if (! $owner) {
            return "#{$this->seoable_id}";
        }

        return (string) ($owner->title ?? $owner->name ?? "#{$owner->getKey()}");
    }

    public function getSocialImageUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl(self::SOCIAL_IMAGE_COLLECTION);

        return $url !== '' ? $url : null;
    }
}
