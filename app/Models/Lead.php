<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Lead extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    public const STATUSES = [
        'new' => 'New',
        'contacted' => 'Contacted',
        'qualified' => 'Qualified',
        'quoted' => 'Quoted',
        'won' => 'Won',
        'lost' => 'Lost',
        'spam' => 'Spam',
        'closed' => 'Closed',
    ];

    public const ATTACHMENTS_COLLECTION = 'lead_attachments';

    protected $fillable = [
        'user_id',
        'reference',
        'name',
        'email',
        'phone',
        'location_id',
        'message',
        'metadata',
        'status',
        'assigned_to',
        'source_page_url',
        'utm',
        'consent',
        'pricing_snapshot',
    ];

    protected $attributes = [
        'status' => 'new',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'location_id' => 'integer',
            'assigned_to' => 'integer',
            'metadata' => 'array',
            'utm' => 'array',
            'consent' => 'array',
            'pricing_snapshot' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(self::ATTACHMENTS_COLLECTION)
            ->useDisk('private');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class)->withTrashed();
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'assigned_to')->withTrashed();
    }

    public function answers(): HasMany
    {
        return $this->hasMany(LeadFormAnswer::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function serviceLinks(): HasMany
    {
        return $this->hasMany(LeadService::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'lead_services')
            ->withPivot(['id', 'notes', 'deleted_at'])
            ->withTimestamps()
            ->wherePivotNull('deleted_at');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(LeadStatusHistory::class)
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(LeadNote::class)
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('created_at')->orderByDesc('id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst((string) $this->status);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'new' => 'primary',
            'contacted' => 'info',
            'qualified' => 'warning',
            'quoted' => 'warning',
            'won' => 'success',
            'lost', 'spam' => 'danger',
            'closed' => 'secondary',
            default => 'secondary',
        };
    }
}
