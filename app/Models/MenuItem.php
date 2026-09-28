<?php

namespace App\Models;

<<<<<<< HEAD
use Illuminate\Database\Eloquent\Builder;
=======
>>>>>>> origin/arena/01a0e931-drawing-gm
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
<<<<<<< HEAD

class MenuItem extends Model
{
    use HasFactory;

    public const TARGETS = ['_self', '_blank'];
=======
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

class MenuItem extends Model
{
    use HasFactory, SoftDeletes;

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
>>>>>>> origin/arena/01a0e931-drawing-gm

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

    protected function casts(): array
    {
        return [
<<<<<<< HEAD
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

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

>>>>>>> origin/arena/01a0e931-drawing-gm
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
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
=======
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('id');
>>>>>>> origin/arena/01a0e931-drawing-gm
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

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
>>>>>>> origin/arena/01a0e931-drawing-gm
    }
}
