<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadStatusHistory extends Model
{
    use HasFactory, SoftDeletes;

    public const UPDATED_AT = null;

    protected $fillable = [
        'lead_id',
        'changed_by_type',
        'changed_by_id',
        'from_status',
        'to_status',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'lead_id' => 'integer',
            'changed_by_id' => 'integer',
            'created_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class)->withTrashed();
    }

    public function changedBy(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'changed_by_type', 'changed_by_id');
    }
}
