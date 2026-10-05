<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PricingPackageAddon extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pricing_package_addon';

    protected $fillable = [
        'pricing_package_id',
        'pricing_addon_id',
        'override_data',
    ];

    protected function casts(): array
    {
        return [
            'pricing_package_id' => 'integer',
            'pricing_addon_id' => 'integer',
            'override_data' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function pricingPackage(): BelongsTo
    {
        return $this->belongsTo(PricingPackage::class)->withTrashed();
    }

    public function pricingAddon(): BelongsTo
    {
        return $this->belongsTo(PricingAddon::class)->withTrashed();
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('id');
    }
}
