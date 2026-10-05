<?php

namespace App\Actions\Leads;

use App\Models\Lead;
use App\Models\LeadStatusHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateLeadStatus
{
    public function execute(
        Lead $lead,
        string $toStatus,
        ?Model $actor = null,
        ?string $reason = null,
    ): Lead {
        if (! array_key_exists($toStatus, Lead::STATUSES)) {
            throw ValidationException::withMessages([
                'status' => 'The selected lead status is invalid.',
            ]);
        }

        return DB::transaction(function () use ($lead, $toStatus, $actor, $reason): Lead {
            /** @var Lead $locked */
            $locked = Lead::withTrashed()->lockForUpdate()->findOrFail($lead->id);
            $fromStatus = $locked->status;

            if ($fromStatus === $toStatus) {
                return $locked;
            }

            $locked->update(['status' => $toStatus]);

            LeadStatusHistory::query()->create([
                'lead_id' => $locked->id,
                'changed_by_type' => $actor?->getMorphClass(),
                'changed_by_id' => $actor?->getKey(),
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'reason' => $reason ? trim($reason) : null,
            ]);

            return $locked->refresh();
        });
    }

    public function recordInitial(
        Lead $lead,
        ?Model $actor = null,
        ?string $reason = null,
    ): void {
        LeadStatusHistory::query()->create([
            'lead_id' => $lead->id,
            'changed_by_type' => $actor?->getMorphClass(),
            'changed_by_id' => $actor?->getKey(),
            'from_status' => null,
            'to_status' => $lead->status,
            'reason' => $reason ? trim($reason) : null,
        ]);
    }
}
