<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'guard_name',
        'group_name',
    ];

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $subQuery) use ($search) {
            $subQuery->where('name', 'like', "%{$search}%")
                ->orWhere('guard_name', 'like', "%{$search}%")
                ->orWhere('group_name', 'like', "%{$search}%");
        });
    }

    public function scopeByGuard(Builder $query, string $guardName): Builder
    {
        return $query->where('guard_name', $guardName);
    }

    public function scopeByGroup(Builder $query, string $groupName): Builder
    {
        return $query->where('group_name', $groupName);
    }
}
