<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Actions\Leads\CreateLead;
use App\Actions\Leads\UpdateLeadStatus;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Lead;
use App\Models\LeadFormField;
use App\Models\LeadNote;
use App\Models\Location;
use App\Models\Media;
use App\Models\Service;
use App\Models\User;
use App\Services\LeadMediaService;
use App\Services\LeadWorkflowService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeadController extends Controller
{
    public function __construct(
        private readonly CreateLead $createLead,
        private readonly UpdateLeadStatus $updateLeadStatus,
        private readonly LeadWorkflowService $workflow,
        private readonly LeadMediaService $media,
    ) {}

    public function index(Request $request)
    {
        $this->ensurePermission('lead_list');

        $query = Lead::query()->with(['location', 'assignee', 'services']);
        $this->applyFilters($query, $request);

        $leads = $query->ordered()->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.leads.partials.table', [
                    'leads' => $leads,
                    'isTrash' => false,
                ])->render(),
            ]);
        }

        $title = 'Leads Management';
        $breadcrumb = [
            ['text' => 'Lead Management', 'url' => null],
            ['text' => 'Leads', 'url' => route('admin.leads.index')],
        ];

        return view('backoffice.admin.leads.index', array_merge(
            [
                'leads' => $leads,
                'title' => $title,
                'breadcrumb' => $breadcrumb,
            ],
            $this->filterOptions(),
        ));
    }

    public function list(Request $request)
    {
        $this->ensurePermission('lead_list');

        $query = Lead::query();
        $this->applyFilters($query, $request);

        return response()->json([
            'success' => true,
            'data' => $query->ordered()
                ->limit(100)
                ->get()
                ->map(fn (Lead $lead): array => [
                    'id' => $lead->id,
                    'reference' => $lead->reference,
                    'name' => $lead->name,
                    'phone' => $lead->phone,
                    'status' => $lead->status,
                    'status_label' => $lead->statusLabel(),
                ]),
        ]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('lead_create');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.leads.partials.form', $this->formOptions())->render(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('lead_create');

        $validated = $this->validateLead($request);
        $attributes = $this->leadAttributes($validated);

        /** @var array<int, \Illuminate\Http\UploadedFile> $attachments */
        $attachments = $request->file('attachments', []);
        $attachments = is_array($attachments) ? $attachments : [];

        $lead = $this->createLead->execute(
            attributes: $attributes,
            dynamicAnswers: $validated['dynamic_answers'] ?? [],
            serviceIds: $validated['service_ids'] ?? [],
            serviceNotes: $this->sanitizedServiceNotes($validated['service_notes'] ?? []),
            attachments: $attachments,
            attachmentMediaIds: $validated['attachment_media_ids'] ?? [],
            actor: auth('admin')->user(),
            initialStatusReason: $validated['status_reason'] ?? null,
        );

        return response()->json([
            'success' => true,
            'message' => 'Lead created successfully.',
            'data' => ['id' => $lead->id],
        ]);
    }

    public function show(Request $request, Lead $lead)
    {
        $this->ensurePermission('lead_view');
        abort_unless($request->ajax(), 404);

        $lead->load([
            'user',
            'location',
            'assignee',
            'answers.field',
            'serviceLinks.service',
            'statusHistories.changedBy',
            'notes.author',
            'media',
        ]);

        $trashedNotes = LeadNote::onlyTrashed()
            ->where('lead_id', $lead->id)
            ->orderByDesc('deleted_at')
            ->get();

        return response()->json([
            'html' => view('backoffice.admin.leads.partials.show', compact('lead', 'trashedNotes'))->render(),
        ]);
    }

    public function edit(Request $request, Lead $lead)
    {
        $this->ensurePermission('lead_update');
        abort_unless($request->ajax(), 404);

        $lead->load(['serviceLinks', 'media']);

        return response()->json([
            'html' => view('backoffice.admin.leads.partials.form', array_merge(
                $this->formOptions(),
                ['lead' => $lead],
            ))->render(),
        ]);
    }

    public function update(Request $request, Lead $lead)
    {
        $this->ensurePermission('lead_update');

        $validated = $this->validateLead($request, $lead);
        $attributes = $this->leadAttributes($validated, $lead);
        $requestedStatus = (string) $validated['status'];

        unset($attributes['status']);

        DB::transaction(function () use ($lead, $attributes, $validated, $requestedStatus): void {
            $lead->update($attributes);

            $this->workflow->syncServices(
                $lead,
                $validated['service_ids'] ?? [],
                $this->sanitizedServiceNotes($validated['service_notes'] ?? []),
            );

            if ($lead->status !== $requestedStatus) {
                $this->updateLeadStatus->execute(
                    $lead,
                    $requestedStatus,
                    auth('admin')->user(),
                    $validated['status_reason'] ?? null,
                );
            }
        });

        $this->media->syncFromRequest($request, $lead);

        return response()->json([
            'success' => true,
            'message' => 'Lead updated successfully.',
        ]);
    }

    public function destroy(Lead $lead)
    {
        $this->ensurePermission('lead_delete');

        $lead->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lead moved to trash.',
        ]);
    }

    public function assign(Request $request, Lead $lead)
    {
        $this->ensurePermission('lead_assign');

        $validated = $request->validate([
            'assigned_to' => [
                'nullable',
                'integer',
                $this->eligibleAssigneeRule(),
            ],
        ]);

        $lead->update([
            'assigned_to' => $validated['assigned_to'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => filled($validated['assigned_to'] ?? null)
                ? 'Lead assigned successfully.'
                : 'Lead assignment cleared.',
        ]);
    }

    public function changeStatus(Request $request, Lead $lead)
    {
        $this->ensurePermission('lead_change_status');

        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(Lead::STATUSES))],
            'reason' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->updateLeadStatus->execute(
            $lead,
            (string) $validated['status'],
            auth('admin')->user(),
            $validated['reason'] ?? null,
        );

        return response()->json([
            'success' => true,
            'message' => 'Lead status updated successfully.',
        ]);
    }

    public function storeNote(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'note' => ['required', 'string', 'max:60000'],
            'visible_to_user' => ['required', 'boolean'],
        ]);

        $visibleToUser = (bool) $validated['visible_to_user'];
        $this->ensurePermission($visibleToUser ? 'lead_public_note' : 'lead_internal_note');

        $note = $this->sanitizeRichText($validated['note']);

        if (blank(strip_tags((string) $note))) {
            throw ValidationException::withMessages([
                'note' => 'Note cannot be empty.',
            ]);
        }

        $this->workflow->createNote(
            $lead,
            auth('admin')->user(),
            (string) $note,
            $visibleToUser,
        );

        return response()->json([
            'success' => true,
            'message' => $visibleToUser ? 'Public note added.' : 'Internal note added.',
        ]);
    }

    public function destroyNote(Lead $lead, int $note)
    {
        $model = LeadNote::query()
            ->where('lead_id', $lead->id)
            ->findOrFail($note);

        $this->ensurePermission($model->visible_to_user ? 'lead_public_note' : 'lead_internal_note');

        $model->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lead note moved to trash.',
        ]);
    }

    public function restoreNote(Lead $lead, int $note)
    {
        $model = LeadNote::onlyTrashed()
            ->where('lead_id', $lead->id)
            ->findOrFail($note);

        $this->ensurePermission($model->visible_to_user ? 'lead_public_note' : 'lead_internal_note');

        $model->restore();

        return response()->json([
            'success' => true,
            'message' => 'Lead note restored successfully.',
        ]);
    }

    public function downloadAttachment(Lead $lead, int $media)
    {
        $this->ensurePermission('lead_attachment_download');

        $attachment = $this->media->findOwnedAttachmentOrFail($lead, $media);

        return Storage::disk($attachment->disk)->download(
            $attachment->getPathRelativeToRoot(),
            $attachment->file_name,
        );
    }

    public function previewAttachment(Lead $lead, int $media)
    {
        $this->ensurePermission('lead_attachment_download');

        $attachment = $this->media->findOwnedAttachmentOrFail($lead, $media);

        abort_unless($attachment->isImage(), 404);

        return Storage::disk($attachment->disk)->response(
            $attachment->getPathRelativeToRoot(),
            $attachment->file_name,
            ['Content-Type' => (string) $attachment->mime_type],
            'inline',
        );
    }

    public function export(Request $request): StreamedResponse
    {
        $this->ensurePermission('lead_export');

        $query = Lead::query()->with(['location', 'assignee']);
        $this->applyFilters($query, $request);

        $filename = 'leads-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($query): void {
            $handle = fopen('php://output', 'wb');

            fputcsv($handle, [
                'Reference',
                'Name',
                'Email',
                'Phone',
                'Status',
                'Location',
                'Assigned To',
                'Created At',
            ]);

            $query->reorder('id')->chunkById(200, function ($leads) use ($handle): void {
                foreach ($leads as $lead) {
                    fputcsv($handle, [
                        $lead->reference,
                        $lead->name,
                        $lead->email,
                        $lead->phone,
                        $lead->statusLabel(),
                        $lead->location?->name,
                        $lead->assignee?->name,
                        optional($lead->created_at)->toDateTimeString(),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function multipleAction(Request $request)
    {
        $statusActions = collect(array_keys(Lead::STATUSES))
            ->mapWithKeys(fn (string $status) => ['status_' . $status => $status])
            ->all();

        $validated = $request->validate([
            'action' => ['required', Rule::in(array_merge(
                array_keys($statusActions),
                ['delete', 'restore', 'force_delete'],
            ))],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $action = (string) $validated['action'];

        $permission = match (true) {
            array_key_exists($action, $statusActions) => 'lead_change_status',
            $action === 'delete' => 'lead_delete',
            $action === 'restore' => 'lead_restore',
            $action === 'force_delete' => 'lead_force_delete',
        };

        $this->ensurePermission($permission);

        $ids = collect($validated['ids'])
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            throw ValidationException::withMessages([
                'ids' => 'Please select at least one valid lead.',
            ]);
        }

        if (array_key_exists($action, $statusActions)) {
            $message = $this->bulkStatus($ids, $statusActions[$action]);
        } else {
            $message = match ($action) {
                'delete' => $this->bulkDelete($ids),
                'restore' => $this->bulkRestore($ids),
                'force_delete' => $this->bulkForceDelete($ids),
            };
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function trash(Request $request)
    {
        $this->ensurePermission('lead_trash');

        $query = Lead::onlyTrashed()->with(['location', 'assignee', 'services']);
        $this->applyFilters($query, $request);

        $leads = $query
            ->orderByDesc('deleted_at')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.leads.partials.table', [
                    'leads' => $leads,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        $title = 'Trashed Leads';
        $breadcrumb = [
            ['text' => 'Lead Management', 'url' => null],
            ['text' => 'Leads', 'url' => route('admin.leads.index')],
            ['text' => 'Trash', 'url' => null],
        ];

        return view('backoffice.admin.leads.trash', array_merge(
            [
                'leads' => $leads,
                'title' => $title,
                'breadcrumb' => $breadcrumb,
            ],
            $this->filterOptions(includeTrashedOptions: true),
        ));
    }

    public function restore(int $lead)
    {
        $this->ensurePermission('lead_restore');

        Lead::onlyTrashed()->findOrFail($lead)->restore();

        return response()->json([
            'success' => true,
            'message' => 'Lead restored successfully.',
        ]);
    }

    public function forceDelete(int $lead)
    {
        $this->ensurePermission('lead_force_delete');

        DB::transaction(function () use ($lead): void {
            $record = Lead::onlyTrashed()->findOrFail($lead);
            $this->media->purgeAll($record);
            $record->forceDelete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Lead permanently deleted.',
        ]);
    }

    private function validateLead(Request $request, ?Lead $lead = null): array
    {
        $rules = [
            'user_id' => ['nullable', 'integer', Rule::exists('users', 'id')],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:32'],
            'location_id' => [
                'nullable',
                'integer',
                Rule::exists('locations', 'id')->whereNull('deleted_at'),
            ],
            'message' => ['nullable', 'string', 'max:60000'],
            'status' => ['required', Rule::in(array_keys(Lead::STATUSES))],
            'assigned_to' => ['nullable', 'integer', $this->eligibleAssigneeRule()],
            'source_page_url' => ['nullable', 'string', 'max:255'],
            'metadata_json' => ['nullable', 'string', 'max:100000'],
            'utm_json' => ['nullable', 'string', 'max:50000'],
            'consent_json' => ['nullable', 'string', 'max:50000'],
            'pricing_snapshot_json' => ['nullable', 'string', 'max:100000'],
            'status_reason' => ['nullable', 'string', 'max:1000'],

            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('services', 'id')->whereNull('deleted_at'),
            ],
            'service_notes' => ['nullable', 'array'],
            'service_notes.*' => ['nullable', 'string', 'max:60000'],

            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => [
                'file',
                'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx',
                'max:10240',
            ],
            'attachment_media_ids' => ['nullable', 'array', 'max:10'],
            'attachment_media_ids.*' => [
                'integer',
                'distinct',
                Rule::exists('media', 'id')->whereNull('deleted_at'),
                $this->reusableAttachmentImageRule(),
            ],
            'remove_attachment_ids' => ['nullable', 'array'],
            'remove_attachment_ids.*' => ['integer', 'distinct'],
        ];

        if ($lead === null) {
            $rules['dynamic_answers'] = ['nullable', 'array'];

            $fields = $this->workflow->activeBookingFields();

            foreach ($fields as $field) {
                $rules['dynamic_answers.' . $field->field_key] = [
                    $field->is_required ? 'required' : 'nullable',
                    'string',
                    Rule::in($field->options ?? []),
                ];
            }
        }

        $validated = $request->validate($rules);

        $uploadedAttachments = $request->file('attachments', []);
        $uploadedAttachments = is_array($uploadedAttachments) ? $uploadedAttachments : [];
        $selectedMediaIds = collect($validated['attachment_media_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if (count($uploadedAttachments) + $selectedMediaIds->count() > 10) {
            throw ValidationException::withMessages([
                'attachments' => 'You may add at most 10 attachments per request, including Media Picker selections.',
            ]);
        }

        if ($lead === null) {
            $allowedKeys = $this->workflow->activeBookingFields()->pluck('field_key')->all();
            $submittedKeys = array_keys($validated['dynamic_answers'] ?? []);
            $unknownKeys = array_diff($submittedKeys, $allowedKeys);

            if ($unknownKeys !== []) {
                throw ValidationException::withMessages([
                    'dynamic_answers' => 'Unknown booking field submitted.',
                ]);
            }
        }

        if (! empty($validated['remove_attachment_ids']) && $lead) {
            $ownedCount = Media::query()
                ->where('model_type', $lead->getMorphClass())
                ->where('model_id', $lead->id)
                ->where('collection_name', Lead::ATTACHMENTS_COLLECTION)
                ->whereIn('id', $validated['remove_attachment_ids'])
                ->count();

            if ($ownedCount !== count(array_unique($validated['remove_attachment_ids']))) {
                throw ValidationException::withMessages([
                    'remove_attachment_ids' => 'One or more selected attachments do not belong to this lead.',
                ]);
            }
        }

        return $validated;
    }

    private function leadAttributes(array $validated, ?Lead $lead = null): array
    {
        return [
            'user_id' => $validated['user_id'] ?? null,
            'name' => trim($validated['name']),
            'email' => filled($validated['email'] ?? null)
                ? strtolower(trim((string) $validated['email']))
                : null,
            'phone' => $this->workflow->normalizePhone((string) $validated['phone']),
            'location_id' => $validated['location_id'] ?? null,
            'message' => $this->sanitizeRichText($validated['message'] ?? null),
            'metadata' => $this->decodeJsonObject($validated['metadata_json'] ?? null, 'metadata_json'),
            'status' => (string) $validated['status'],
            'assigned_to' => $validated['assigned_to'] ?? null,
            'source_page_url' => $this->workflow->safeSourcePageUrl($validated['source_page_url'] ?? null),
            'utm' => $this->decodeJsonObject($validated['utm_json'] ?? null, 'utm_json'),
            'consent' => $this->decodeJsonObject($validated['consent_json'] ?? null, 'consent_json'),
            'pricing_snapshot' => $this->decodeJsonObject(
                $validated['pricing_snapshot_json'] ?? null,
                'pricing_snapshot_json',
            ),
        ];
    }

    /**
     * @param array<int|string, string|null> $notes
     * @return array<int|string, string|null>
     */
    private function sanitizedServiceNotes(array $notes): array
    {
        return collect($notes)
            ->map(fn ($value) => $this->sanitizeRichText($value))
            ->all();
    }

    private function decodeJsonObject(mixed $value, string $field): ?array
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw ValidationException::withMessages([
                $field => 'Enter valid JSON.',
            ]);
        }

        if (! is_array($decoded) || array_is_list($decoded)) {
            throw ValidationException::withMessages([
                $field => 'JSON must be an object, for example {"key":"value"}.',
            ]);
        }

        return $decoded;
    }

    private function reusableAttachmentImageRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }

            $media = Media::query()->find((int) $value);

            if (! $media || ! $media->isPickerSafe() || ! $media->isImage()) {
                $fail('The selected media must be an active reusable image.');
            }
        };
    }

    private function eligibleAssigneeRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }

            $admin = Admin::query()
                ->whereKey((int) $value)
                ->whereNull('deleted_at')
                ->where('status', true)
                ->first();

            if (! $admin || ! $admin->can('lead_view')) {
                $fail('The selected assignee must be an active administrator with lead access.');
            }
        };
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('reference', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && array_key_exists((string) $request->status, Lead::STATUSES)) {
            $query->where('status', (string) $request->status);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', (int) $request->assigned_to);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', (int) $request->location_id);
        }

        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->date('created_from'));
        }

        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->date('created_to'));
        }
    }

    private function formOptions(): array
    {
        return [
            'users' => User::query()->orderBy('name')->limit(500)->get(['id', 'name', 'email']),
            'locations' => Location::query()->orderBy('name')->get(['id', 'name']),
            'assignees' => $this->eligibleAssignees(),
            'services' => Service::query()->orderBy('name')->get(['id', 'name']),
            'bookingFields' => LeadFormField::query()->active()->ordered()->get(),
            'statuses' => Lead::STATUSES,
        ];
    }

    private function filterOptions(bool $includeTrashedOptions = false): array
    {
        $locationQuery = $includeTrashedOptions ? Location::withTrashed() : Location::query();

        return [
            'statuses' => Lead::STATUSES,
            'assignees' => $this->eligibleAssignees(),
            'locations' => $locationQuery->orderBy('name')->get(['id', 'name']),
        ];
    }

    private function eligibleAssignees()
    {
        return Admin::query()
            ->where('status', true)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get()
            ->filter(fn (Admin $admin) => $admin->can('lead_view'))
            ->values();
    }

    private function bulkStatus(array $ids, string $status): string
    {
        Lead::query()
            ->whereIn('id', $ids)
            ->get()
            ->each(function (Lead $lead) use ($status): void {
                $this->updateLeadStatus->execute(
                    $lead,
                    $status,
                    auth('admin')->user(),
                    'Bulk status update.',
                );
            });

        return 'Selected leads updated to ' . (Lead::STATUSES[$status] ?? $status) . '.';
    }

    private function bulkDelete(array $ids): string
    {
        Lead::query()
            ->whereIn('id', $ids)
            ->get()
            ->each(fn (Lead $lead) => $lead->delete());

        return 'Selected leads moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        Lead::onlyTrashed()->whereIn('id', $ids)->restore();

        return 'Selected leads restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        Lead::onlyTrashed()
            ->whereIn('id', $ids)
            ->get()
            ->each(function (Lead $lead): void {
                $this->media->purgeAll($lead);
                $lead->forceDelete();
            });

        return 'Selected leads permanently deleted.';
    }

    private function sanitizeRichText(mixed $value): ?string
    {
        $html = trim((string) $value);

        if ($html === '') {
            return null;
        }

        $allowedTags = '<p><br><strong><b><em><i><u><s><ul><ol><li><a><blockquote><h2><h3><h4><h5><h6><span><table><thead><tbody><tr><th><td>';
        $html = strip_tags($html, $allowedTags);

        $html = preg_replace('/\son\w+\s*=\s*(["\']).*?\1/iu', '', $html) ?? $html;
        $html = preg_replace('/\shref\s*=\s*(["\'])\s*(?:javascript|data|vbscript):.*?\1/iu', '', $html) ?? $html;

        return trim($html) !== '' ? $html : null;
    }

    private function ensurePermission(string $permission): void
    {
        abort_unless(auth('admin')->user()?->can($permission), 403);
    }
}
