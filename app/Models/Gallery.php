<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gallery extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'attachable_type',
        'attachable_id',
        'layout',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(GalleryItem::class)->orderBy('sort_order');
    }

    public function attachable()
    {
        return $this->morphTo();
    }
}
