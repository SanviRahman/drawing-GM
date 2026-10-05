<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Redirect extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_CODES = [
        301 => '301 Permanent',
        302 => '302 Found',
        307 => '307 Temporary',
        308 => '308 Permanent',
    ];

    protected $fillable = [
        'from_path',
        'to_url',
        'status_code',
        'hits',
        'is_active',
        'last_hit_at',
    ];

    protected $attributes = [
        'status_code' => 301,
        'hits' => 0,
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'hits' => 'integer',
            'is_active' => 'boolean',
            'last_hit_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('updated_at')->orderByDesc('id');
    }
}
