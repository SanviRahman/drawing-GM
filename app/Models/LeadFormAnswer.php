<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeadFormAnswer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_id',
        'lead_form_field_id',
        'field_key',
        'field_label',
        'answer',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'lead_id' => 'integer',
            'lead_form_field_id' => 'integer',
            'sort_order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class)->withTrashed();
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(LeadFormField::class, 'lead_form_field_id')->withTrashed();
    }
}
