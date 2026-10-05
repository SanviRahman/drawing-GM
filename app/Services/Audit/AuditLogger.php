<?php

namespace App\Services\Audit;

use App\Models\Admin;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditLogger
{
    public function log(
        string $action,
        Model|string $auditable,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?Model $actor = null,
        ?Request $request = null,
    ): AuditLog {
        $actor ??= $this->resolveActor();
        $request ??= request();

        [$auditableType, $auditableId] = $auditable instanceof Model
            ? [$auditable->getMorphClass(), $auditable->getKey()]
            : [(string) $auditable, null];

        if ($actor && ! $actor instanceof Admin && ! $actor instanceof User) {
            $actor = null;
        }

        return AuditLog::query()->create([
            'actor_type' => $actor?->getMorphClass(),
            'actor_id' => $actor?->getKey(),
            'action' => $action,
            'auditable_type' => $auditableType,
            'auditable_id' => $auditableId,
            'old_values' => AuditLog::redactPayload($oldValues),
            'new_values' => AuditLog::redactPayload($newValues),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'created_at' => now(),
        ]);
    }

    private function resolveActor(): ?Model
    {
        if (auth('admin')->check()) {
            return auth('admin')->user();
        }

        if (auth('web')->check()) {
            return auth('web')->user();
        }

        return null;
    }
}
