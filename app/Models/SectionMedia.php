<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SectionMedia extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'section_media';

    protected $fillable = [
        'page_section_id',
        'media_id',
        'role',
        'caption_override',
        'sort_order',
    ];

    protected $attributes = [
        'sort_order' => 0,
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function pageSection(): BelongsTo
    {
        return $this->belongsTo(PageSection::class)->withTrashed();
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class)->withTrashed();
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function scopeForRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }
}
