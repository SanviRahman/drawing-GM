<?php

namespace App\Actions\Leads;

use App\Models\Lead;
use App\Services\LeadMediaService;
use App\Services\LeadWorkflowService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateLead
{
    public function __construct(
        private readonly LeadWorkflowService $workflow,
        private readonly LeadMediaService $media,
        private readonly UpdateLeadStatus $statusAction,
    ) {}

    /**
     * @param array<string, mixed> $attributes
     * @param array<string, mixed> $dynamicAnswers
     * @param array<int, int|string> $serviceIds
     * @param array<int|string, string|null> $serviceNotes
     * @param array<int, UploadedFile> $attachments
     * @param array<int, int|string> $attachmentMediaIds
     */
    public function execute(
        array $attributes,
        array $dynamicAnswers = [],
        array $serviceIds = [],
        array $serviceNotes = [],
        array $attachments = [],
        array $attachmentMediaIds = [],
        ?Model $actor = null,
        ?string $initialStatusReason = null,
    ): Lead {
        return DB::transaction(function () use (
            $attributes,
            $dynamicAnswers,
            $serviceIds,
            $serviceNotes,
            $attachments,
            $attachmentMediaIds,
            $actor,
            $initialStatusReason,
        ): Lead {
            $attributes['reference'] = 'TMP-' . Str::uuid();

            $lead = Lead::query()->create($attributes);

            $lead->forceFill([
                'reference' => $this->workflow->generateReference($lead),
            ])->save();

            $this->workflow->snapshotDynamicAnswers($lead, $dynamicAnswers);
            $this->workflow->syncServices($lead, $serviceIds, $serviceNotes);
            $this->media->addUploads($lead, $attachments);
            $this->media->addPickedImages($lead, $attachmentMediaIds);
            $this->statusAction->recordInitial($lead, $actor, $initialStatusReason);

            return $lead->refresh();
        });
    }
}
