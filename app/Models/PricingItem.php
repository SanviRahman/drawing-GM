<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PricingItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pricing_package_id',
        'label',
        'amount',
        'amount_max',
        'price_type',
        'unit',
        'prefix',
        'suffix',
        'sort_order',
        'is_active',
    ];

    protected $attributes = [
        'price_type' => 'fixed',
        'sort_order' => 0,
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'amount_max' => 'decimal:2',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    public function pricingPackage(): BelongsTo
    {
        return $this->belongsTo(PricingPackage::class)->withTrashed();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function getPriceTypeLabelAttribute(): string
    {
        return match ($this->price_type) {
            'fixed' => 'Fixed',
            'from' => 'From',
            'range' => 'Range',
            'call' => 'Call for Price',
            default => ucfirst((string) $this->price_type),
        };
    }

    public function getDisplayPriceAttribute(): string
    {
        $currency = $this->pricingPackage?->currency ?: '';
        $prefix = trim((string) $this->prefix);
        $suffix = trim((string) $this->suffix);

        if ($this->price_type === 'call') {
            return trim(($prefix ? $prefix . ' ' : '') . 'Call for Price' . ($suffix ? ' ' . $suffix : ''));
        }

        $amount = $this->amount !== null ? number_format((float) $this->amount, 2) : null;
        $amountMax = $this->amount_max !== null ? number_format((float) $this->amount_max, 2) : null;

        $value = match ($this->price_type) {
            'from' => trim('From ' . $currency . ' ' . $amount),
            'range' => trim($currency . ' ' . $amount . ' - ' . $currency . ' ' . $amountMax),
            default => trim($currency . ' ' . $amount),
        };

        return trim(($prefix ? $prefix . ' ' : '') . $value . ($suffix ? ' ' . $suffix : ''));
    }
}
