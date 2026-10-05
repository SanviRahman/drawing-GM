<?php

namespace App\Services;

use App\Models\ContactChannel;
use App\Models\ContactTarget;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContactTargetService
{
    /**
     * @return array{0: ContactTarget, 1: bool}
     */
    public function createOrRestore(int $contactChannelId, string $targetType, int $targetId): array
    {
        $target = $this->resolveTarget($targetType, $targetId);
        $morphType = $target->getMorphClass();

        return DB::transaction(function () use ($contactChannelId, $targetId, $morphType): array {
            $existing = ContactTarget::withTrashed()
                ->where('contact_channel_id', $contactChannelId)
                ->where('targetable_type', $morphType)
                ->where('targetable_id', $targetId)
                ->lockForUpdate()
                ->first();

            if ($existing && ! $existing->trashed()) {
                throw ValidationException::withMessages([
                    'targetable_id' => 'This contact channel is already assigned to the selected target.',
                ]);
            }

            if ($existing) {
                $this->ensureRestorable($existing);
                $existing->restore();
                $existing->touch();

                return [$existing->fresh(), true];
            }

            $record = ContactTarget::create([
                'contact_channel_id' => $contactChannelId,
                'targetable_type' => $morphType,
                'targetable_id' => $targetId,
            ]);

            return [$record, false];
        });
    }

    public function update(ContactTarget $contactTarget, int $contactChannelId, string $targetType, int $targetId): ContactTarget
    {
        $target = $this->resolveTarget($targetType, $targetId);
        $morphType = $target->getMorphClass();

        return DB::transaction(function () use ($contactTarget, $contactChannelId, $morphType, $targetId): ContactTarget {
            $duplicate = ContactTarget::withTrashed()
                ->where('id', '!=', $contactTarget->id)
                ->where('contact_channel_id', $contactChannelId)
                ->where('targetable_type', $morphType)
                ->where('targetable_id', $targetId)
                ->lockForUpdate()
                ->first();

            if ($duplicate) {
                throw ValidationException::withMessages([
                    'targetable_id' => $duplicate->trashed()
                        ? 'The same mapping already exists in Trash. Restore or permanently delete that mapping first.'
                        : 'This contact channel is already assigned to the selected target.',
                ]);
            }

            $contactTarget->update([
                'contact_channel_id' => $contactChannelId,
                'targetable_type' => $morphType,
                'targetable_id' => $targetId,
            ]);

            return $contactTarget->fresh();
        });
    }

    public function ensureRestorable(ContactTarget $contactTarget): void
    {
        $channelExists = ContactChannel::query()
            ->whereKey($contactTarget->contact_channel_id)
            ->exists();

        if (! $channelExists) {
            throw ValidationException::withMessages([
                'contact_channel_id' => 'Restore the related contact channel before restoring this contact target.',
            ]);
        }

        $targetType = $contactTarget->target_type_key;

        if ($targetType === null) {
            throw ValidationException::withMessages([
                'targetable_type' => 'This mapping uses an unsupported target type and cannot be restored safely.',
            ]);
        }

        $this->resolveTarget($targetType, (int) $contactTarget->targetable_id);
    }

    public function resolveTarget(string $targetType, int $targetId): Model
    {
        $modelClass = ContactTarget::modelClassForType($targetType);

        if ($modelClass === null) {
            throw ValidationException::withMessages([
                'target_type' => 'Unsupported contact target type.',
            ]);
        }

        $target = $modelClass::query()->find($targetId);

        if (! $target) {
            $label = ContactTarget::TARGET_TYPES[$targetType]['label'] ?? 'target';

            throw ValidationException::withMessages([
                'targetable_id' => "The selected {$label} is unavailable or is currently in Trash.",
            ]);
        }

        return $target;
    }
}
