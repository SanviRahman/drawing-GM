<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Testimonial extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;
    public const TYPES   = ['text', 'whatsapp_screenshot', 'video'];
    public const SOURCES = ['google', 'whatsapp', 'facebook', 'direct'];
    protected $fillable  = ['type', 'customer_name', 'customer_title', 'rating', 'review', 'source', 'source_url', 'reviewed_at', 'is_featured', 'is_active', 'sort_order'];
    protected $casts     = ['rating' => 'integer', 'reviewed_at' => 'datetime', 'is_featured' => 'boolean', 'is_active' => 'boolean', 'sort_order' => 'integer'];
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile()->useDisk('public')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
        $this->addMediaCollection('testimonial_screenshot')->singleFile()->useDisk('public')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }
    public function services()
    {
        return $this->morphedByMany(Service::class, 'testimonialable')->withPivot('sort_order')->withTimestamps();
    }
    public function locations()
    {
        return $this->morphedByMany(Location::class, 'testimonialable')->withPivot('sort_order')->withTimestamps();
    }
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
    public function getPhotoUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('photo') ?: null;
    }
    public function getScreenshotUrlAttribute(): ?string
    {
        return $this->getFirstMediaUrl('testimonial_screenshot') ?: null;
    }
}
