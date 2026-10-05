<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditLog extends Model
{
    use SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'actor_type',
        'actor_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'actor_id' => 'integer',
            'auditable_id' => 'integer',
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $log): void {
            $log->old_values = self::redactPayload($log->old_values);
            $log->new_values = self::redactPayload($log->new_values);
            $log->created_at ??= now();
        });
    }

    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeOrdered($query)
    {
        return $query->latest('created_at')->latest('id');
    }

    public static function redactPayload(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        $sensitiveFragments = [
            'password', 'secret', 'token', 'authorization', 'cookie',
            'api_key', 'apikey', 'client_secret', 'private_key', 'access_token',
        ];

        $redacted = [];

        foreach ($value as $key => $item) {
            $normalized = strtolower((string) $key);
            $isSensitive = collect($sensitiveFragments)
                ->contains(fn (string $fragment): bool => str_contains($normalized, $fragment));

            $redacted[$key] = $isSensitive
                ? '[REDACTED]'
                : (is_array($item) ? self::redactPayload($item) : $item);
        }

        return $redacted;
    }
}
