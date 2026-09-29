<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Page extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    public const STATUSES = [
        'draft'     => 'Draft',
        'published' => 'Published',
        'archived'  => 'Archived',
    ];

    public const HERO_COLLECTIONS = [
        'hero_desktop',
        'hero_mobile',
    ];

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'template',
        'hero_config',
        'status',
        'published_at',
        'is_homepage',
        'show_header',
        'show_footer',
        'created_by',
        'updated_by',
    ];

    protected $attributes = [
        'status'      => 'draft',
        'is_homepage' => false,
        'show_header' => true,
        'show_footer' => true,
    ];

    protected function casts(): array
    {
        return [
            'hero_config'  => 'array',
            'published_at' => 'datetime',
            'is_homepage'  => 'boolean',
            'show_header'  => 'boolean',
            'show_footer'  => 'boolean',
            'created_at'   => 'datetime',
            'updated_at'   => 'datetime',
            'deleted_at'   => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('hero_desktop')->useDisk('public')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
        $this->addMediaCollection('hero_mobile')->useDisk('public')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(PageSection::class)->ordered();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeHomepage($query)
    {
        return $query->where('is_homepage', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('title')->orderBy('id');
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
