<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class PricingAddon extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    public const MEDIA_COLLECTION = 'image';

    protected $fillable = [
        'name',
        'description',
        'amount',
        'amount_max',
        'price_type',
        'unit',
        'is_active',
        'sort_order',
    ];

    protected $attributes = [
        'price_type' => 'fixed',
        'is_active' => true,
        'sort_order' => 0,
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'amount_max' => 'decimal:2',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::MEDIA_COLLECTION)
            ->useDisk('public')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
    }

    public function pricingPackageLinks(): HasMany
    {
        return $this->hasMany(PricingPackageAddon::class);
    }

    public function pricingPackages(): BelongsToMany
    {
        return $this->belongsToMany(PricingPackage::class, 'pricing_package_addon')
            ->withPivot('id', 'override_data', 'deleted_at')
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name')->orderBy('id');
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
        if ($this->price_type === 'call') {
            return 'Call for Price';
        }

        $amount = $this->amount !== null ? number_format((float) $this->amount, 2) : '—';
        $amountMax = $this->amount_max !== null ? number_format((float) $this->amount_max, 2) : null;

        $price = match ($this->price_type) {
            'from' => 'From ' . $amount,
            'range' => $amountMax !== null ? $amount . ' - ' . $amountMax : $amount,
            default => $amount,
        };

        return $this->unit ? $price . ' / ' . $this->unit : $price;
    }
}
