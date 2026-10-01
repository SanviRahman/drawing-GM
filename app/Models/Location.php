<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Location extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    public const STATUSES = [
        'draft'     => 'Draft',
        'published' => 'Published',
        'archived'  => 'Archived',
    ];

    public const MEDIA_COLLECTIONS = [
        'hero_desktop',
        'hero_mobile',
        'gallery',
    ];

    protected $fillable = [
        'name',
        'slug',
        'region',
        'postal_codes',
        'summary',
        'content',
        'hero_config',
        'status',
        'sort_order',
        'published_at',
    ];

    protected $attributes = [
        'status'     => 'draft',
        'sort_order' => 0,
    ];

    protected function casts(): array
    {
        return [
            'postal_codes' => 'array',
            'hero_config'  => 'array',
            'sort_order'   => 'integer',
            'published_at' => 'datetime',
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
            'deleted_at'   => 'datetime',
        ];
    }

    public function testimonials()
    {
        return $this->morphToMany(Testimonial::class, 'testimonialable')->withPivot('sort_order')->withTimestamps();
    }

    public function registerMediaCollections(): void
    {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

        $this->addMediaCollection('hero_desktop')->useDisk('public')->singleFile()->acceptsMimeTypes($allowed);
        $this->addMediaCollection('hero_mobile')->useDisk('public')->singleFile()->acceptsMimeTypes($allowed);
        $this->addMediaCollection('gallery')->useDisk('public')->acceptsMimeTypes($allowed);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name')->orderBy('id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'published' => 'success',
            'archived'  => 'secondary',
            default     => 'warning',
        };
    }

    public function heroValue(string $key, mixed $default = null): mixed
    {
        return data_get($this->hero_config ?? [], $key, $default);
    }
}
