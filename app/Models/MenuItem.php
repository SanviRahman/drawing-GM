<?php

namespace App\Models;

<<<<<<< HEAD
<<<<<<< HEAD
use Illuminate\Database\Eloquent\Builder;
=======
>>>>>>> origin/arena/01a0e931-drawing-gm
=======
>>>>>>> 27cfaac023287e7a99e8201f7b3a9bca742bf4a3
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
<<<<<<< HEAD
<<<<<<< HEAD
=======
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
>>>>>>> 27cfaac023287e7a99e8201f7b3a9bca742bf4a3

class MenuItem extends Model
{
    use HasFactory, SoftDeletes;

<<<<<<< HEAD
    public const TARGETS = ['_self', '_blank'];
=======
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class MenuItem extends Model
{
    use HasFactory, SoftDeletes;

=======
>>>>>>> 27cfaac023287e7a99e8201f7b3a9bca742bf4a3
    protected $table = 'menu_items';

    /**
     * Supported link types.
     */
    public const LINK_TYPES = [
        'url' => 'Custom URL',
        'linkable' => 'Internal Link',
        'text' => 'Plain Text (no link)',
    ];

    /**
     * Allowed link targets (see docs/database-schema.md Section 3).
     */
    public const TARGETS = [
        '_self',
        '_blank',
    ];
<<<<<<< HEAD
>>>>>>> origin/arena/01a0e931-drawing-gm
=======
>>>>>>> 27cfaac023287e7a99e8201f7b3a9bca742bf4a3

    protected $fillable = [
        'menu_id',
        'parent_id',
        'label',
        'link_type',
        'linkable_type',
        'linkable_id',
        'url',
        'icon',
        'target',
        'css_class',
        'sort_order',
        'is_active',
    ];

    /**
     * Attribute defaults mirroring the database column defaults.
     */
    protected $attributes = [
        'link_type' => 'url',
        'target' => '_self',
        'sort_order' => 0,
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
<<<<<<< HEAD
<<<<<<< HEAD
=======
            'menu_id' => 'integer',
            'parent_id' => 'integer',
            'linkable_id' => 'integer',
>>>>>>> 27cfaac023287e7a99e8201f7b3a9bca742bf4a3
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

<<<<<<< HEAD
=======
            'menu_id' => 'integer',
            'parent_id' => 'integer',
            'linkable_id' => 'integer',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

=======
>>>>>>> 27cfaac023287e7a99e8201f7b3a9bca742bf4a3
    /*
    |--------------------------------------------------------------------------
    | Model events (tree cascade rules from docs/database-erd.md)
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::deleting(function (MenuItem $item): void {
            if ($item->isForceDeleting()) {
                $item->children()->withTrashed()->get()->each->forceDelete();

                return;
            }

            $item->children()->get()->each->delete();
        });

        static::restoring(function (MenuItem $item): void {
            $deletedAt = $item->deleted_at;

            if (! $deletedAt) {
                return;
            }

            // Restore children that were cascade-deleted together with this item.
            $item->children()
                ->withTrashed()
                ->whereNotNull('deleted_at')
                ->where('deleted_at', '>=', $deletedAt)
                ->get()
                ->each(fn (self $child) => $child->restore());
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
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

<<<<<<< HEAD
>>>>>>> origin/arena/01a0e931-drawing-gm
=======
>>>>>>> 27cfaac023287e7a99e8201f7b3a9bca742bf4a3
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
<<<<<<< HEAD
<<<<<<< HEAD
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
=======
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
>>>>>>> origin/arena/01a0e931-drawing-gm
=======
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
>>>>>>> 27cfaac023287e7a99e8201f7b3a9bca742bf4a3
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
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

    /**
     * Allowlist of models that menu items may link to.
     *
     * Extend this map when the Page, Service, Location and Post models
     * are implemented, e.g. 'Page' => 'Page', keyed by morph alias.
     *
     * @return array<class-string, string>
     */
    public static function linkableModels(): array
    {
=======
    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Allowlist of models that menu items may link to.
     *
     * Extend this map when the Page, Service, Location and Post models
     * are implemented, e.g. 'Page' => 'Page', keyed by morph alias.
     *
     * @return array<class-string, string>
     */
    public static function linkableModels(): array
    {
>>>>>>> 27cfaac023287e7a99e8201f7b3a9bca742bf4a3
        return [
            // \App\Models\Page::class => 'Page',
            // \App\Models\Service::class => 'Service',
            // \App\Models\Location::class => 'Location',
            // \App\Models\Post::class => 'Post',
        ];
    }

    /**
     * IDs of every descendant item (children, grandchildren, ...).
     *
     * @return Collection<int, int>
     */
    public function descendantIds(): Collection
    {
        $ids = collect();

        $children = $this->children()->pluck('id');

        while ($children->isNotEmpty()) {
            $ids = $ids->merge($children);
            $children = static::query()->whereIn('parent_id', $children)->pluck('id');
        }

        return $ids;
    }

    public function linkSummary(): string
    {
        return match ($this->link_type) {
            'url' => (string) $this->url,
            'linkable' => $this->linkable_type
                ? class_basename($this->linkable_type) . ' #' . $this->linkable_id
                : 'Internal',
            default => '—',
        };
<<<<<<< HEAD
>>>>>>> origin/arena/01a0e931-drawing-gm
=======
>>>>>>> 27cfaac023287e7a99e8201f7b3a9bca742bf4a3
    }
}
