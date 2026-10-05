<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrackingEventRule;
use App\Models\TrackingProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TrackingEventRuleController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePermission('tracking_event_rule_list');

        $query = TrackingEventRule::query()->with('provider');
        $this->applyFilters($query, $request);

        $trackingEventRules = $query->ordered()->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.tracking_event_rules.partials.table', [
                    'trackingEventRules' => $trackingEventRules,
                    'isTrash' => false,
                ])->render(),
            ]);
        }

        return view('backoffice.admin.tracking_event_rules.index', [
            'trackingEventRules' => $trackingEventRules,
            'providers' => TrackingProvider::query()->ordered()->get(['id', 'provider']),
            'events' => TrackingEventRule::INTERNAL_EVENTS,
            'title' => 'Tracking Event Rules',
            'breadcrumb' => [
                ['text' => 'SEO & Tracking', 'url' => null],
                ['text' => 'Tracking Event Rules', 'url' => route('admin.tracking_event_rules.index')],
            ],
        ]);
    }

    public function list(Request $request)
    {
        $this->ensurePermission('tracking_event_rule_list');

        $query = TrackingEventRule::query()->with('provider:id,provider');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(fn (Builder $q) => $q
                ->where('internal_event', 'like', "%{$search}%")
                ->orWhere('provider_event', 'like', "%{$search}%"));
        }

        return response()->json([
            'success' => true,
            'data' => $query->ordered()->limit(100)->get(),
        ]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('tracking_event_rule_create');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.tracking_event_rules.partials.form', $this->formOptions())->render(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('tracking_event_rule_create');

        $validated = $this->validateRule($request);

        $rule = DB::transaction(function () use ($validated): TrackingEventRule {
            $existing = TrackingEventRule::withTrashed()
                ->where('tracking_provider_id', (int) $validated['tracking_provider_id'])
                ->where('internal_event', (string) $validated['internal_event'])
                ->lockForUpdate()
                ->first();

            if ($existing && ! $existing->trashed()) {
                throw ValidationException::withMessages([
                    'internal_event' => 'This provider already has a rule for the selected internal event.',
                ]);
            }

            $record = $existing ?: new TrackingEventRule;
            $record->fill($this->attributes($validated));

            if ($existing?->trashed()) {
                $existing->restore();
            }

            $record->save();

            return $record;
        });

        return response()->json([
            'success' => true,
            'message' => 'Tracking event rule created successfully.',
            'data' => ['id' => $rule->id],
        ]);
    }

    public function show(Request $request, TrackingEventRule $trackingEventRule)
    {
        $this->ensurePermission('tracking_event_rule_view');
        abort_unless($request->ajax(), 404);

        $trackingEventRule->load('provider');

        return response()->json([
            'html' => view('backoffice.admin.tracking_event_rules.partials.show', compact('trackingEventRule'))->render(),
        ]);
    }

    public function edit(Request $request, TrackingEventRule $trackingEventRule)
    {
        $this->ensurePermission('tracking_event_rule_update');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.tracking_event_rules.partials.form', array_merge(
                $this->formOptions(),
                compact('trackingEventRule')
            ))->render(),
        ]);
    }

    public function update(Request $request, TrackingEventRule $trackingEventRule)
    {
        $this->ensurePermission('tracking_event_rule_update');

        $validated = $this->validateRule($request, $trackingEventRule);

        $conflict = TrackingEventRule::withTrashed()
            ->where('tracking_provider_id', (int) $validated['tracking_provider_id'])
            ->where('internal_event', (string) $validated['internal_event'])
            ->where('id', '!=', $trackingEventRule->id)
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'internal_event' => 'Another active or trashed rule already uses this provider/internal-event pair.',
            ]);
        }

        $trackingEventRule->update($this->attributes($validated));

        return response()->json(['success' => true, 'message' => 'Tracking event rule updated successfully.']);
    }

    public function destroy(TrackingEventRule $trackingEventRule)
    {
        $this->ensurePermission('tracking_event_rule_delete');
        $trackingEventRule->update(['is_enabled' => false]);
        $trackingEventRule->delete();

        return response()->json(['success' => true, 'message' => 'Tracking event rule moved to trash.']);
    }

    public function toggle(TrackingEventRule $trackingEventRule)
    {
        $this->ensurePermission('tracking_event_rule_toggle');

        $trackingEventRule->update(['is_enabled' => ! $trackingEventRule->is_enabled]);

        return response()->json([
            'success' => true,
            'message' => $trackingEventRule->fresh()->is_enabled
                ? 'Tracking event rule enabled.'
                : 'Tracking event rule disabled.',
        ]);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['enable', 'disable', 'delete', 'restore', 'force_delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $permission = match ($validated['action']) {
            'enable', 'disable' => 'tracking_event_rule_toggle',
            'delete' => 'tracking_event_rule_delete',
            'restore' => 'tracking_event_rule_restore',
            'force_delete' => 'tracking_event_rule_force_delete',
        };

        $this->ensurePermission($permission);

        $ids = collect($validated['ids'])->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();

        $message = match ($validated['action']) {
            'enable' => $this->bulkStatus($ids, true),
            'disable' => $this->bulkStatus($ids, false),
            'delete' => $this->bulkDelete($ids),
            'restore' => $this->bulkRestore($ids),
            'force_delete' => $this->bulkForceDelete($ids),
        };

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function trash(Request $request)
    {
        $this->ensurePermission('tracking_event_rule_trash');

        $query = TrackingEventRule::onlyTrashed()->with('provider')->latest('deleted_at');
        $this->applyFilters($query, $request);

        $trackingEventRules = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.tracking_event_rules.partials.table', [
                    'trackingEventRules' => $trackingEventRules,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        return view('backoffice.admin.tracking_event_rules.trash', [
            'trackingEventRules' => $trackingEventRules,
            'providers' => TrackingProvider::withTrashed()->ordered()->get(['id', 'provider']),
            'events' => TrackingEventRule::INTERNAL_EVENTS,
            'title' => 'Trashed Tracking Event Rules',
            'breadcrumb' => [
                ['text' => 'SEO & Tracking', 'url' => null],
                ['text' => 'Tracking Event Rules', 'url' => route('admin.tracking_event_rules.index')],
                ['text' => 'Trash', 'url' => null],
            ],
        ]);
    }

    public function restore(int $trackingEventRule)
    {
        $this->ensurePermission('tracking_event_rule_restore');

        $record = TrackingEventRule::onlyTrashed()->with('provider')->findOrFail($trackingEventRule);

        if (! $record->provider || $record->provider->trashed()) {
            throw ValidationException::withMessages([
                'tracking_event_rule' => 'Restore the related tracking provider before restoring this rule.',
            ]);
        }

        if (TrackingEventRule::query()
            ->where('tracking_provider_id', $record->tracking_provider_id)
            ->where('internal_event', $record->internal_event)
            ->exists()) {
            throw ValidationException::withMessages([
                'tracking_event_rule' => 'An active rule already exists for this provider/internal-event pair.',
            ]);
        }

        $record->forceFill(['is_enabled' => false]);
        $record->restore();
        $record->save();

        return response()->json([
            'success' => true,
            'message' => 'Tracking event rule restored as disabled.',
        ]);
    }

    public function forceDelete(int $trackingEventRule)
    {
        $this->ensurePermission('tracking_event_rule_force_delete');
        TrackingEventRule::onlyTrashed()->findOrFail($trackingEventRule)->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Tracking event rule permanently deleted.',
        ]);
    }

    private function validateRule(Request $request, ?TrackingEventRule $rule = null): array
    {
        return $request->validate([
            'tracking_provider_id' => [
                'required',
                'integer',
                Rule::exists('tracking_providers', 'id')->whereNull('deleted_at'),
            ],
            'internal_event' => ['required', 'string', Rule::in(array_keys(TrackingEventRule::INTERNAL_EVENTS))],
            'provider_event' => ['required', 'string', 'max:120'],
            'parameter_map' => ['nullable', 'string', 'max:60000'],
            'requires_marketing_consent' => ['required', 'boolean'],
            'is_enabled' => ['required', 'boolean'],
        ]);
    }

    private function attributes(array $validated): array
    {
        return [
            'tracking_provider_id' => (int) $validated['tracking_provider_id'],
            'internal_event' => (string) $validated['internal_event'],
            'provider_event' => trim((string) $validated['provider_event']),
            'parameter_map' => $this->decodeJsonObject($validated['parameter_map'] ?? null),
            'requires_marketing_consent' => (bool) $validated['requires_marketing_consent'],
            'is_enabled' => (bool) $validated['is_enabled'],
        ];
    }

    private function decodeJsonObject(mixed $value): ?array
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return null;
        }

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw ValidationException::withMessages(['parameter_map' => 'The parameter map JSON is invalid.']);
        }

        if (! is_array($decoded) || array_is_list($decoded)) {
            throw ValidationException::withMessages(['parameter_map' => 'The parameter map must be a JSON object.']);
        }

        return $decoded;
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('internal_event', 'like', "%{$search}%")
                    ->orWhere('provider_event', 'like', "%{$search}%");
            });
        }

        if ($request->filled('provider_id')) {
            $query->where('tracking_provider_id', (int) $request->provider_id);
        }

        if ($request->filled('internal_event') && array_key_exists((string) $request->internal_event, TrackingEventRule::INTERNAL_EVENTS)) {
            $query->where('internal_event', (string) $request->internal_event);
        }

        if ($request->filled('status')) {
            $query->where('is_enabled', $request->status === 'enabled');
        }
    }

    private function formOptions(): array
    {
        return [
            'providers' => TrackingProvider::query()->ordered()->get(['id', 'provider']),
            'events' => TrackingEventRule::INTERNAL_EVENTS,
        ];
    }

    private function bulkStatus(array $ids, bool $enabled): string
    {
        TrackingEventRule::query()->whereIn('id', $ids)->update(['is_enabled' => $enabled]);
        return $enabled ? 'Selected tracking event rules enabled.' : 'Selected tracking event rules disabled.';
    }

    private function bulkDelete(array $ids): string
    {
        TrackingEventRule::query()->whereIn('id', $ids)->update(['is_enabled' => false]);
        TrackingEventRule::query()->whereIn('id', $ids)->delete();
        return 'Selected tracking event rules moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        $records = TrackingEventRule::onlyTrashed()->with('provider')->whereIn('id', $ids)->get();

        foreach ($records as $record) {
            if (! $record->provider || $record->provider->trashed()) {
                throw ValidationException::withMessages([
                    'tracking_event_rule' => "Rule #{$record->id} cannot be restored until its provider is restored.",
                ]);
            }

            if (TrackingEventRule::query()
                ->where('tracking_provider_id', $record->tracking_provider_id)
                ->where('internal_event', $record->internal_event)
                ->exists()) {
                throw ValidationException::withMessages([
                    'tracking_event_rule' => "Rule #{$record->id} conflicts with an active rule.",
                ]);
            }
        }

        $records->each(function (TrackingEventRule $record): void {
            $record->forceFill(['is_enabled' => false]);
            $record->restore();
            $record->save();
        });

        return 'Selected tracking event rules restored as disabled.';
    }

    private function bulkForceDelete(array $ids): string
    {
        TrackingEventRule::onlyTrashed()->whereIn('id', $ids)->forceDelete();
        return 'Selected tracking event rules permanently deleted.';
    }

    private function ensurePermission(string $permission): void
    {
        abort_unless(auth('admin')->user()?->can($permission), 403);
    }
}
