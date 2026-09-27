<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use SoftDeletes;

    public function isSuperAdmin(): bool
    {
        return $this->name === 'super_admin'
            && $this->guard_name === 'admin';
    }
}
