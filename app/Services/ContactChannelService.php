<?php

namespace App\Services;

use App\Models\ContactChannel;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContactChannelService
{
    public function normalizeValue(string $type, string $value): string
    {
        $value = trim($value);

        if (in_array($type, ['whatsapp', 'phone'], true)) {
            $value = preg_replace('/^tel:/i', '', $value) ?? $value;
            $value = preg_replace('/[\s\-().]/', '', $value) ?? $value;

            if (str_starts_with($value, '00')) {
                $value = '+' . substr($value, 2);
            }
        }

        return $value;
    }

    public function assertValueIsValid(string $type, string $value): void
    {
        if (in_array($type, ['whatsapp', 'phone'], true)) {
            if (! preg_match('/^\+[1-9][0-9]{7,14}$/', $value)) {
                throw ValidationException::withMessages([
                    'value' => 'Phone and WhatsApp values must be normalized E.164 numbers, for example +6591234567.',
                ]);
            }

            return;
        }

        if ($type === 'email') {
            if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages([
                    'value' => 'Please enter a valid email address.',
                ]);
            }

            return;
        }

        if ($type === 'custom') {
            $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

            if (! filter_var($value, FILTER_VALIDATE_URL) || ! in_array($scheme, ['http', 'https'], true)) {
                throw ValidationException::withMessages([
                    'value' => 'Custom contact values must be valid http:// or https:// URLs.',
                ]);
            }
        }
    }

    /**
     * Current schema stores is_default on contact_channels itself, so this module
     * enforces one global default channel. Target-scoped resolution is added when
     * contact_targets is implemented.
     */
    public function setDefault(ContactChannel $channel): void
    {
        DB::transaction(function () use ($channel): void {
            $record = ContactChannel::query()
                ->whereKey($channel->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            ContactChannel::withTrashed()
                ->where('id', '!=', $record->id)
                ->where('is_default', true)
                ->update(['is_default' => false]);

            $record->forceFill([
                'is_default' => true,
                'is_active' => true,
            ])->save();
        });
    }

    public function clearDefault(ContactChannel $channel): void
    {
        if (! $channel->is_default) {
            return;
        }

        $channel->forceFill(['is_default' => false])->save();
    }

    public function prepareForSoftDelete(ContactChannel $channel): void
    {
        $this->clearDefault($channel);
    }
}
