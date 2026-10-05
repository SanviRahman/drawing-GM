<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactChannel;
use App\Models\ContactTarget;
use App\Models\Location;
use App\Models\Page;
use App\Models\Service;
use App\Services\ContactTargetService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ContactTargetController extends Controller
{
    public function __construct(
        private readonly ContactTargetService $contactTargetService,
    ) {}

    public function index(Request $request)
    {
        $this->ensurePermission('contact_target_list');

        $query = ContactTarget::query()->with(['channel', 'targetable']);
        $this->applyFilters($query, $request);

        $contactTargets = $query->ordered()->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.contact_targets.partials.table', [
                    'contactTargets' => $contactTargets,
                    'isTrash' => false,
                ])->render(),
            ]);
        }

        $title = 'Contact Targets Management';
        $breadcrumb = [
            ['text' => 'Site Configuration', 'url' => null],
            ['text' => 'Contact Targets', 'url' => route('admin.contact_targets.index')],
        ];

        return view('backoffice.admin.contact_targets.index', [
            'contactTargets' => $contactTargets,
            'channelOptions' => $this->channelFilterOptions(),
            'targetTypes' => ContactTarget::TARGET_TYPES,
            'title' => $title,
            'breadcrumb' => $breadcrumb,
        ]);
    }

    public function list(Request $request)
    {
        $this->ensurePermission('contact_target_list');

        $query = ContactTarget::query()->with(['channel', 'targetable']);
        $this->applyFilters($query, $request);

        $data = $query->ordered()
            ->limit(100)
            ->get()
            ->map(fn (ContactTarget $contactTarget): array => [
                'id' => $contactTarget->id,
                'contact_channel_id' => $contactTarget->contact_channel_id,
                'channel_label' => $contactTarget->channel?->label ?? 'Unavailable channel',
                'target_type' => $contactTarget->target_type_key,
                'target_type_label' => $contactTarget->target_type_label,
                'targetable_id' => $contactTarget->targetable_id,
                'target_name' => $contactTarget->target_name,
            ]);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('contact_target_create');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.contact_targets.partials.form', $this->formOptions())->render(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('contact_target_create');

        $validated = $this->validateContactTarget($request);

        [$contactTarget, $restored] = $this->contactTargetService->createOrRestore(
            (int) $validated['contact_channel_id'],
            (string) $validated['target_type'],
            (int) $validated['targetable_id'],
        );

        return response()->json([
            'success' => true,
            'message' => $restored
                ? 'Existing contact target mapping restored successfully.'
                : 'Contact target created successfully.',
            'data' => ['id' => $contactTarget->id],
        ]);
    }

    public function show(Request $request, ContactTarget $contactTarget)
    {
        $this->ensurePermission('contact_target_view');
        abort_unless($request->ajax(), 404);

        $contactTarget->load(['channel', 'targetable']);

        return response()->json([
            'html' => view('backoffice.admin.contact_targets.partials.show', compact('contactTarget'))->render(),
        ]);
    }

    public function edit(Request $request, ContactTarget $contactTarget)
    {
        $this->ensurePermission('contact_target_update');
        abort_unless($request->ajax(), 404);

        $contactTarget->load(['channel', 'targetable']);

        return response()->json([
            'html' => view('backoffice.admin.contact_targets.partials.form', array_merge(
                $this->formOptions(),
                ['contactTarget' => $contactTarget],
            ))->render(),
        ]);
    }

    public function update(Request $request, ContactTarget $contactTarget)
    {
        $this->ensurePermission('contact_target_update');

        $validated = $this->validateContactTarget($request);

        $this->contactTargetService->update(
            $contactTarget,
            (int) $validated['contact_channel_id'],
            (string) $validated['target_type'],
            (int) $validated['targetable_id'],
        );

        return response()->json([
            'success' => true,
            'message' => 'Contact target updated successfully.',
        ]);
    }

    public function destroy(ContactTarget $contactTarget)
    {
        $this->ensurePermission('contact_target_delete');

        $contactTarget->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contact target moved to trash.',
        ]);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['delete', 'restore', 'force_delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $permission = match ($validated['action']) {
            'delete' => 'contact_target_delete',
            'restore' => 'contact_target_restore',
            'force_delete' => 'contact_target_force_delete',
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
                'ids' => 'Please select at least one valid contact target.',
            ]);
        }

        $message = match ($validated['action']) {
            'delete' => $this->bulkDelete($ids),
            'restore' => $this->bulkRestore($ids),
            'force_delete' => $this->bulkForceDelete($ids),
        };

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function trash(Request $request)
    {
        $this->ensurePermission('contact_target_trash');

        $query = ContactTarget::onlyTrashed()->with(['channel', 'targetable']);
        $this->applyFilters($query, $request);

        $contactTargets = $query
            ->orderByDesc('deleted_at')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.contact_targets.partials.table', [
                    'contactTargets' => $contactTargets,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        $title = 'Trashed Contact Targets';
        $breadcrumb = [
            ['text' => 'Site Configuration', 'url' => null],
            ['text' => 'Contact Targets', 'url' => route('admin.contact_targets.index')],
            ['text' => 'Trash', 'url' => null],
        ];

        return view('backoffice.admin.contact_targets.trash', [
            'contactTargets' => $contactTargets,
            'channelOptions' => $this->channelFilterOptions(),
            'targetTypes' => ContactTarget::TARGET_TYPES,
            'title' => $title,
            'breadcrumb' => $breadcrumb,
        ]);
    }

    public function restore(int $contactTarget)
    {
        $this->ensurePermission('contact_target_restore');

        DB::transaction(function () use ($contactTarget): void {
            $record = ContactTarget::onlyTrashed()->findOrFail($contactTarget);
            $this->contactTargetService->ensureRestorable($record);
            $record->restore();
        });

        return response()->json([
            'success' => true,
            'message' => 'Contact target restored successfully.',
        ]);
    }

    public function forceDelete(int $contactTarget)
    {
        $this->ensurePermission('contact_target_force_delete');

        ContactTarget::onlyTrashed()->findOrFail($contactTarget)->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Contact target permanently deleted.',
        ]);
    }

    private function validateContactTarget(Request $request): array
    {
        $validated = $request->validate([
            'contact_channel_id' => [
                'required',
                'integer',
                Rule::exists('contact_channels', 'id')->whereNull('deleted_at'),
            ],
            'target_type' => [
                'required',
                'string',
                Rule::in(array_keys(ContactTarget::TARGET_TYPES)),
            ],
            'targetable_id' => ['required', 'integer', 'min:1'],
        ]);

        // Resolve through a controlled alias. Raw model class names from the request are never trusted.
        $this->contactTargetService->resolveTarget(
            (string) $validated['target_type'],
            (int) $validated['targetable_id'],
        );

        return $validated;
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('contact_channel_id')) {
            $query->where('contact_channel_id', (int) $request->contact_channel_id);
        }

        if ($request->filled('target_type')) {
            $modelClass = ContactTarget::modelClassForType((string) $request->target_type);

            if ($modelClass !== null) {
                $query->where('targetable_type', (new $modelClass())->getMorphClass());
            }
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $pageIds = Page::withTrashed()
                ->where('title', 'like', "%{$search}%")
                ->limit(100)
                ->pluck('id');

            $serviceIds = Service::withTrashed()
                ->where('name', 'like', "%{$search}%")
                ->limit(100)
                ->pluck('id');

            $locationIds = Location::withTrashed()
                ->where('name', 'like', "%{$search}%")
                ->limit(100)
                ->pluck('id');

            $pageMorph = (new Page())->getMorphClass();
            $serviceMorph = (new Service())->getMorphClass();
            $locationMorph = (new Location())->getMorphClass();

            $query->where(function (Builder $builder) use (
                $search,
                $pageIds,
                $serviceIds,
                $locationIds,
                $pageMorph,
                $serviceMorph,
                $locationMorph,
            ): void {
                $builder->whereHas('channel', function (Builder $channelQuery) use ($search): void {
                    $channelQuery->where(function (Builder $nested) use ($search): void {
                        $nested->where('label', 'like', "%{$search}%")
                            ->orWhere('region', 'like', "%{$search}%")
                            ->orWhere('value', 'like', "%{$search}%")
                            ->orWhere('display_value', 'like', "%{$search}%");
                    });
                });

                if ($pageIds->isNotEmpty()) {
                    $builder->orWhere(function (Builder $nested) use ($pageMorph, $pageIds): void {
                        $nested->where('targetable_type', $pageMorph)
                            ->whereIn('targetable_id', $pageIds->all());
                    });
                }

                if ($serviceIds->isNotEmpty()) {
                    $builder->orWhere(function (Builder $nested) use ($serviceMorph, $serviceIds): void {
                        $nested->where('targetable_type', $serviceMorph)
                            ->whereIn('targetable_id', $serviceIds->all());
                    });
                }

                if ($locationIds->isNotEmpty()) {
                    $builder->orWhere(function (Builder $nested) use ($locationMorph, $locationIds): void {
                        $nested->where('targetable_type', $locationMorph)
                            ->whereIn('targetable_id', $locationIds->all());
                    });
                }
            });
        }
    }

    private function formOptions(): array
    {
        return [
            'contactChannelOptions' => ContactChannel::query()
                ->ordered()
                ->get(['id', 'type', 'label', 'region', 'is_active']),
            'pageOptions' => Page::query()->orderBy('title')->pluck('title', 'id'),
            'serviceOptions' => Service::query()->orderBy('name')->pluck('name', 'id'),
            'locationOptions' => Location::query()->orderBy('name')->pluck('name', 'id'),
            'targetTypes' => ContactTarget::TARGET_TYPES,
        ];
    }

    private function channelFilterOptions()
    {
        return ContactChannel::withTrashed()
            ->orderBy('label')
            ->orderBy('id')
            ->get(['id', 'label', 'type', 'deleted_at']);
    }

    private function bulkDelete(array $ids): string
    {
        ContactTarget::query()->whereIn('id', $ids)->delete();

        return 'Selected contact targets moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        DB::transaction(function () use ($ids): void {
            $records = ContactTarget::onlyTrashed()->whereIn('id', $ids)->get();

            foreach ($records as $record) {
                $this->contactTargetService->ensureRestorable($record);
            }

            foreach ($records as $record) {
                $record->restore();
            }
        });

        return 'Selected contact targets restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        ContactTarget::onlyTrashed()->whereIn('id', $ids)->forceDelete();

        return 'Selected contact targets permanently deleted.';
    }

    private function ensurePermission(string $permission): void
    {
        abort_unless(
            auth('admin')->user()?->can($permission),
            403,
            'You do not have permission to perform this contact target action.'
        );
    }
}
