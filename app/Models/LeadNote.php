<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadNote extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_id',
        'author_type',
        'author_id',
        'note',
        'visible_to_user',
    ];

    protected $attributes = [
        'visible_to_user' => false,
    ];

    protected function casts(): array
    {
        return [
            'lead_id' => 'integer',
            'author_id' => 'integer',
            'visible_to_user' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class)->withTrashed();
    }

    public function author(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'author_type', 'author_id');
    }
}
