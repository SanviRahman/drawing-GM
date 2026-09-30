<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class GalleryItem extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'gallery_id',
        'item_type',
        'title',
        'caption',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function gallery()
    {
        return $this->belongsTo(Gallery::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->useDisk('public')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
        $this->addMediaCollection('before')->useDisk('public')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
        $this->addMediaCollection('after')->useDisk('public')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }
}
