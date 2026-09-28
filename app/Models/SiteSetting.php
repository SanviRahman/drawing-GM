<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class SiteSetting extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    public const VALUE_TYPES = [
        'string',
        'boolean',
        'integer',
        'json',
        'encrypted',
    ];

    protected $fillable = [
        'group_name',
        'setting_key',
        'setting_value',
        'value_type',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
        ];
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
}
