<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactTarget extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Request/UI aliases mapped to the only target models allowed by the schema.
     * Never accept an arbitrary PHP class name from the browser.
     */
    public const TARGET_TYPES = [
        'page' => [
            'label' => 'Page',
            'model' => Page::class,
            'name_column' => 'title',
        ],
        'service' => [
            'label' => 'Service',
            'model' => Service::class,
            'name_column' => 'name',
        ],
        'location' => [
            'label' => 'Location',
            'model' => Location::class,
            'name_column' => 'name',
        ],
    ];

    protected $fillable = [
        'contact_channel_id',
        'targetable_type',
        'targetable_id',
    ];

    protected function casts(): array
    {
        return [
            'contact_channel_id' => 'integer',
            'targetable_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(ContactChannel::class, 'contact_channel_id')->withTrashed();
    }

    public function targetable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('id');
    }

    public static function modelClassForType(string $type): ?string
    {
        return self::TARGET_TYPES[$type]['model'] ?? null;
    }

    public static function typeKeyForMorphClass(string $morphClass): ?string
    {
        foreach (self::TARGET_TYPES as $key => $meta) {
            $modelClass = $meta['model'];

            if ((new $modelClass())->getMorphClass() === $morphClass) {
                return $key;
            }
        }

        return null;
    }

    public function getTargetTypeKeyAttribute(): ?string
    {
        return self::typeKeyForMorphClass((string) $this->targetable_type);
    }

    public function getTargetTypeLabelAttribute(): string
    {
        $key = $this->target_type_key;

        return $key !== null
            ? (string) self::TARGET_TYPES[$key]['label']
            : class_basename((string) $this->targetable_type);
    }

    public function targetRecord(bool $withTrashed = true): ?Model
    {
        $key = $this->target_type_key;
        $modelClass = $key !== null ? self::modelClassForType($key) : null;

        if ($modelClass === null) {
            return null;
        }

        $query = $withTrashed
            ? $modelClass::withTrashed()
            : $modelClass::query();

        return $query->find($this->targetable_id);
    }

    public function getTargetNameAttribute(): string
    {
        $target = $this->targetable ?: $this->targetRecord(true);

        if (! $target) {
            return 'Unavailable target #' . $this->targetable_id;
        }

        $key = $this->target_type_key;
        $nameColumn = $key !== null
            ? (string) self::TARGET_TYPES[$key]['name_column']
            : 'id';

        $value = trim((string) data_get($target, $nameColumn, ''));

        return $value !== '' ? $value : ('#' . $target->getKey());
    }
}
