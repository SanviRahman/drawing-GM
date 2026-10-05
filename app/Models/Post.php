<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Post extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    public const STATUSES = [
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ];

    public const FEATURED_COLLECTION = 'featured';
    public const CONTENT_IMAGES_COLLECTION = 'content_images';

    public const MEDIA_COLLECTIONS = [
        self::FEATURED_COLLECTION,
        self::CONTENT_IMAGES_COLLECTION,
    ];

    protected $fillable = [
        'author_id',
        'category_id',
        'title',
        'slug',
        'excerpt',
        'body',
        'status',
        'published_at',
        'reading_minutes',
        'allow_comments',
    ];

    protected $attributes = [
        'status' => 'draft',
        'allow_comments' => false,
    ];

    protected function casts(): array
    {
        return [
            'author_id' => 'integer',
            'category_id' => 'integer',
            'published_at' => 'datetime',
            'reading_minutes' => 'integer',
            'allow_comments' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $acceptedImages = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        $this->addMediaCollection(self::FEATURED_COLLECTION)
            ->useDisk('public')
            ->singleFile()
            ->acceptsMimeTypes($acceptedImages);

        $this->addMediaCollection(self::CONTENT_IMAGES_COLLECTION)
            ->useDisk('public')
            ->acceptsMimeTypes($acceptedImages);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'author_id')->withTrashed();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    public function statusLabel(): string
    {
        if ($this->status === 'published' && $this->published_at?->isFuture()) {
            return 'Scheduled';
        }

        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function statusBadgeClass(): string
    {
        if ($this->status === 'published' && $this->published_at?->isFuture()) {
            return 'info';
        }

        return match ($this->status) {
            'published' => 'success',
            'archived' => 'secondary',
            default => 'warning',
        };
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        $url = $this->getFirstMediaUrl(self::FEATURED_COLLECTION);

        return $url !== '' ? $url : null;
    }
}
