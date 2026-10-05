<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Campaign extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    public const STATUSES = [
        'draft' => 'Draft',
        'published' => 'Published',
        'inactive' => 'Inactive',
        'archived' => 'Archived',
    ];

    public const HERO_IMAGES = 'hero_images';
    public const HERO_VIDEO = 'hero_video';
    public const HERO_VIDEO_POSTER = 'hero_video_poster';
    public const SOCIAL_IMAGE = 'social_image';

    public const MEDIA_COLLECTIONS = [
        self::HERO_IMAGES,
        self::HERO_VIDEO,
        self::HERO_VIDEO_POSTER,
        self::SOCIAL_IMAGE,
    ];

    protected $fillable = [
        'title',
        'slug',
        'custom_route',
        'summary',
        'status',
        'hero_config',
        'published_at',
        'starts_at',
        'ends_at',
        'created_by',
        'updated_by',
    ];

    protected $attributes = [
        'status' => 'draft',
        'hero_config' => '{}',
    ];

    protected function casts(): array
    {
        return [
            'hero_config' => 'array',
            'published_at' => 'datetime',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'created_by' => 'integer',
            'updated_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $images = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $videos = ['video/mp4', 'video/webm'];

        $this->addMediaCollection(self::HERO_IMAGES)
            ->useDisk('public')
            ->acceptsMimeTypes($images);

        $this->addMediaCollection(self::HERO_VIDEO)
            ->useDisk('public')
            ->singleFile()
            ->acceptsMimeTypes($videos);

        $this->addMediaCollection(self::HERO_VIDEO_POSTER)
            ->useDisk('public')
            ->singleFile()
            ->acceptsMimeTypes($images);

        $this->addMediaCollection(self::SOCIAL_IMAGE)
            ->useDisk('public')
            ->singleFile()
            ->acceptsMimeTypes($images);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CampaignSection::class)->ordered();
    }

    public function enabledSections(): HasMany
    {
        return $this->hasMany(CampaignSection::class)
            ->where('is_enabled', true)
            ->ordered();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by')->withTrashed();
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'updated_by')->withTrashed();
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('published_at')->orderByDesc('id');
    }

    public function scopePublicEligible(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>', now()));
    }

    public function isPublicEligible(): bool
    {
        if ($this->trashed() || $this->status !== 'published' || ! $this->published_at || $this->published_at->isFuture()) {
            return false;
        }

        if ($this->starts_at?->isFuture()) {
            return false;
        }

        return ! $this->ends_at || $this->ends_at->isFuture();
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'published' => 'success',
            'inactive' => 'warning',
            'archived' => 'secondary',
            default => 'info',
        };
    }

    public function publicPath(): string
    {
        return $this->custom_route ?: '/campaign/' . $this->slug;
    }

    public function heroValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->hero_config ?? [], $key, $default);
    }
}
