<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrackingEventRule extends Model
{
    use HasFactory, SoftDeletes;

    public const INTERNAL_EVENTS = [
        'page_view' => 'Page View',
        'view_content' => 'View Content',
        'view_pricing' => 'View Pricing',
        'contact' => 'Contact',
        'click_whatsapp' => 'Click WhatsApp',
        'click_call' => 'Click Call',
        'submit_quote' => 'Submit Quote',
        'lead' => 'Lead',
    ];

    protected $fillable = [
        'tracking_provider_id',
        'internal_event',
        'provider_event',
        'parameter_map',
        'requires_marketing_consent',
        'is_enabled',
    ];

    protected $attributes = [
        'requires_marketing_consent' => true,
        'is_enabled' => true,
    ];

    protected function casts(): array
    {
        return [
            'tracking_provider_id' => 'integer',
            'parameter_map' => 'array',
            'requires_marketing_consent' => 'boolean',
            'is_enabled' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(TrackingProvider::class, 'tracking_provider_id')->withTrashed();
    }

    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('internal_event')->orderBy('id');
    }
}
