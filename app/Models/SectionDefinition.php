<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SectionDefinition extends Model
{
    use SoftDeletes;

    public const ALLOWED_KEYS = [
        'hero'             => 'Hero',
        'rich_text'        => 'Rich Text',
        'guarantee'        => 'Guarantee',
        'benefit_grid'     => 'Benefit Grid',
        'service_carousel' => 'Service Carousel',
        'pricing'          => 'Pricing',
        'gallery'          => 'Gallery',
        'before_after'     => 'Before / After',
        'video_gallery'    => 'Video Gallery',
        'testimonials'     => 'Testimonials',
        'whatsapp_reviews' => 'WhatsApp Reviews',
        'paint_calculator' => 'Paint Calculator',
        'faq'              => 'FAQ',
        'cta'              => 'CTA',
        'contact_form'     => 'Contact Form',
        'safe_embed'       => 'Safe Embed',
        'spacer'           => 'Spacer',
    ];

    protected $fillable = [
        'key',
        'name',
        'description',
        'schema_json',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'schema_json' => 'array',
            'is_active'   => 'boolean',
            'deleted_at'  => 'datetime',
        ];
    }

    public function pageSections(): HasMany
    {
        return $this->hasMany(PageSection::class);
    }
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('name')->orderBy('key');
    }

    public function getComponentNameAttribute(): string
    {
        return str_replace('_', '-', $this->key);
    }

    public function getComponentViewAttribute(): string
    {
        return 'components.sections.' . $this->component_name;
    }
}
