<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CampaignSection extends Model
{
    use HasFactory, SoftDeletes;

    public const TYPES = [
        'hero' => 'Hero',
        'hero_benefits' => 'Hero Benefits',
        'service_grid' => 'Service Grid',
        'category_brand' => 'Category / Brand',
        'pricing' => 'Pricing',
        'gallery' => 'Gallery',
        'video_gallery' => 'Video Gallery',
        'testimonials' => 'Testimonials',
        'whatsapp_reviews' => 'WhatsApp Reviews',
        'faq' => 'FAQ',
        'cta' => 'Call To Action',
        'lead_form' => 'Lead Form',
    ];

    protected $fillable = [
        'campaign_id',
        'section_key',
        'heading',
        'subheading',
        'payload',
        'is_enabled',
        'sort_order',
    ];

    protected $attributes = [
        'payload' => '{}',
        'is_enabled' => true,
        'sort_order' => 0,
    ];

    protected function casts(): array
    {
        return [
            'campaign_id' => 'integer',
            'payload' => 'array',
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class)->withTrashed();
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public function label(): string
    {
        return self::TYPES[$this->section_key] ?? $this->section_key;
    }
}
