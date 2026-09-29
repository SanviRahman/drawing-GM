<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PageSection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'page_id',
        'section_definition_id',
        'heading',
        'subheading',
        'payload',
        'theme',
        'sort_order',
        'is_active',
        'starts_at',
        'ends_at',
    ];

    protected $attributes = [
        'theme' => 'default',
        'sort_order' => 0,
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class)->withTrashed();
    }

    public function sectionDefinition(): BelongsTo
    {
        return $this->belongsTo(SectionDefinition::class)->withTrashed();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeCurrentlyVisible(Builder $query): Builder
    {
        return $query->active()
            ->where(function (Builder $builder): void {
                $builder->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $builder): void {
                $builder->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function scheduleStatus(): string
    {
        if (! $this->is_active) {
            return 'inactive';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'scheduled';
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'expired';
        }

        return 'active';
    }

    public function scheduleBadgeClass(): string
    {
        return match ($this->scheduleStatus()) {
            'active' => 'success',
            'scheduled' => 'info',
            'expired' => 'secondary',
            default => 'warning',
        };
    }
}
