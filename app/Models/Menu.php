<?php

namespace App\Models;

<<<<<<< HEAD
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Builder;
=======
>>>>>>> 27cfaac023287e7a99e8201f7b3a9bca742bf4a3
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Menu extends Model
{
<<<<<<< HEAD
    use HasFactory;
=======
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Menu extends Model
{
=======
>>>>>>> 27cfaac023287e7a99e8201f7b3a9bca742bf4a3
    use HasFactory, SoftDeletes;

    protected $table = 'menus';

    /**
     * Known theme menu locations.
     */
    public const LOCATIONS = [
        'header-primary',
        'header-top',
        'footer-services',
        'footer-company',
        'footer-legal',
    ];
<<<<<<< HEAD
>>>>>>> origin/arena/01a0e931-drawing-gm
=======
>>>>>>> 27cfaac023287e7a99e8201f7b3a9bca742bf4a3

    protected $fillable = [
        'name',
        'location',
        'is_active',
    ];

    /**
     * Attribute defaults mirroring the database column defaults.
     */
    protected $attributes = [
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
<<<<<<< HEAD
<<<<<<< HEAD
=======
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
>>>>>>> 27cfaac023287e7a99e8201f7b3a9bca742bf4a3
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Model events (cascade rules from docs/database-erd.md)
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::deleting(function (Menu $menu): void {
            if ($menu->isForceDeleting()) {
                $menu->items()->withTrashed()->forceDelete();

                return;
            }

            $menu->items()->delete();
        });

        static::restoring(function (Menu $menu): void {
            $deletedAt = $menu->deleted_at;

            if (! $deletedAt) {
                return;
            }

            // Restore items that were cascade-deleted together with this menu.
            $menu->items()
                ->withTrashed()
                ->whereNotNull('deleted_at')
                ->where('deleted_at', '>=', $deletedAt)
                ->get()
                ->each(fn (MenuItem $item) => $item->restore());
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function items(): HasMany
    {
<<<<<<< HEAD
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
=======
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Model events (cascade rules from docs/database-erd.md)
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::deleting(function (Menu $menu): void {
            if ($menu->isForceDeleting()) {
                $menu->items()->withTrashed()->forceDelete();

                return;
            }

            $menu->items()->delete();
        });

        static::restoring(function (Menu $menu): void {
            $deletedAt = $menu->deleted_at;

            if (! $deletedAt) {
                return;
            }

            // Restore items that were cascade-deleted together with this menu.
            $menu->items()
                ->withTrashed()
                ->whereNotNull('deleted_at')
                ->where('deleted_at', '>=', $deletedAt)
                ->get()
                ->each(fn (MenuItem $item) => $item->restore());
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order')->orderBy('id');
>>>>>>> origin/arena/01a0e931-drawing-gm
=======
        return $this->hasMany(MenuItem::class)->orderBy('sort_order')->orderBy('id');
>>>>>>> 27cfaac023287e7a99e8201f7b3a9bca742bf4a3
    }

    public function rootItems(): HasMany
    {
        return $this->items()->whereNull('parent_id');
    }

<<<<<<< HEAD
<<<<<<< HEAD
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
=======
    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public static function locationLabel(string $location): string
    {
=======
    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public static function locationLabel(string $location): string
    {
>>>>>>> 27cfaac023287e7a99e8201f7b3a9bca742bf4a3
        return Str::title(str_replace('-', ' ', $location));
    }

    /**
     * Flattened select options for parent selection with depth markers.
     * The given item (and its descendants) are excluded to prevent cycles.
     *
     * @return array<int, string>
     */
    public function itemOptions(?MenuItem $exclude = null): array
    {
        $items = $this->items()->orderBy('sort_order')->orderBy('id')->get();
        $excludeIds = $exclude ? $exclude->descendantIds()->merge([$exclude->id])->all() : [];

        $byId = $items->keyBy('id');

        $depthOf = function (MenuItem $item) use ($byId): int {
            $depth = 0;
            $parentId = $item->parent_id;

            while ($parentId !== null && $byId->has($parentId)) {
                $depth++;
                $parentId = $byId->get($parentId)->parent_id;
            }

            return $depth;
        };

        $options = [];

        foreach ($items as $item) {
            if (in_array($item->id, $excludeIds, true)) {
                continue;
            }

            $options[$item->id] = str_repeat('— ', $depthOf($item)) . $item->label;
        }

        return $options;
<<<<<<< HEAD
>>>>>>> origin/arena/01a0e931-drawing-gm
=======
>>>>>>> 27cfaac023287e7a99e8201f7b3a9bca742bf4a3
    }
}
