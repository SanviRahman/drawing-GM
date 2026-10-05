<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeadFormField;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LeadFormFieldController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePermission('lead_form_field_list');

        $query = LeadFormField::query();
        $this->applyFilters($query, $request);

        $leadFormFields = $query->ordered()->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.lead_form_fields.partials.table', [
                    'leadFormFields' => $leadFormFields,
                    'isTrash' => false,
                ])->render(),
            ]);
        }

        $title = 'Booking Form Fields Management';
        $breadcrumb = [
            ['text' => 'Lead Management', 'url' => null],
            ['text' => 'Booking Form Fields', 'url' => route('admin.lead_form_fields.index')],
        ];

        return view('backoffice.admin.lead_form_fields.index', compact(
            'leadFormFields',
            'title',
            'breadcrumb',
        ));
    }

    public function list(Request $request)
    {
        $this->ensurePermission('lead_form_field_list');

        $query = LeadFormField::query();
        $this->applyFilters($query, $request);

        return response()->json([
            'success' => true,
            'data' => $query->ordered()
                ->limit(100)
                ->get()
                ->map(fn (LeadFormField $field): array => [
                    'id' => $field->id,
                    'label' => $field->label,
                    'field_key' => $field->field_key,
                    'is_required' => $field->is_required,
                    'is_active' => $field->is_active,
                ]),
        ]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('lead_form_field_create');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.lead_form_fields.partials.form')->render(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('lead_form_field_create');

        $validated = $this->validateLeadFormField($request);

        LeadFormField::query()->create($this->attributes($validated));

        return response()->json([
            'success' => true,
            'message' => 'Booking form field created successfully.',
        ]);
    }

    public function show(Request $request, LeadFormField $leadFormField)
    {
        $this->ensurePermission('lead_form_field_view');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.lead_form_fields.partials.show', compact('leadFormField'))->render(),
        ]);
    }

    public function edit(Request $request, LeadFormField $leadFormField)
    {
        $this->ensurePermission('lead_form_field_update');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.lead_form_fields.partials.form', compact('leadFormField'))->render(),
        ]);
    }

    public function update(Request $request, LeadFormField $leadFormField)
    {
        $this->ensurePermission('lead_form_field_update');

        $validated = $this->validateLeadFormField($request, $leadFormField);
        $attributes = $this->attributes($validated);

        // field_key is a stable machine key; historical answer snapshots depend on it.
        $attributes['field_key'] = $leadFormField->field_key;

        $leadFormField->update($attributes);

        return response()->json([
            'success' => true,
            'message' => 'Booking form field updated successfully.',
        ]);
    }

    public function destroy(LeadFormField $leadFormField)
    {
        $this->ensurePermission('lead_form_field_delete');

        $leadFormField->delete();

        return response()->json([
            'success' => true,
            'message' => 'Booking form field moved to trash.',
        ]);
    }

    public function toggle(LeadFormField $leadFormField)
    {
        $this->ensurePermission('lead_form_field_toggle');

        $leadFormField->update([
            'is_active' => ! $leadFormField->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => $leadFormField->is_active
                ? 'Booking form field activated.'
                : 'Booking form field deactivated.',
        ]);
    }

    public function reorder(Request $request)
    {
        $this->ensurePermission('lead_form_field_reorder');

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'integer', Rule::exists('lead_form_fields', 'id')->whereNull('deleted_at')],
            'items.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($validated): void {
            foreach ($validated['items'] as $item) {
                LeadFormField::query()
                    ->whereKey((int) $item['id'])
                    ->update(['sort_order' => (int) $item['sort_order']]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Booking form field order updated.',
        ]);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['active', 'inactive', 'delete', 'restore', 'force_delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $permission = match ($validated['action']) {
            'active', 'inactive' => 'lead_form_field_toggle',
            'delete' => 'lead_form_field_delete',
            'restore' => 'lead_form_field_restore',
            'force_delete' => 'lead_form_field_force_delete',
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
                'ids' => 'Please select at least one valid booking field.',
            ]);
        }

        $message = match ($validated['action']) {
            'active' => $this->bulkStatus($ids, true),
            'inactive' => $this->bulkStatus($ids, false),
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
        $this->ensurePermission('lead_form_field_trash');

        $query = LeadFormField::onlyTrashed();
        $this->applyFilters($query, $request);

        $leadFormFields = $query
            ->orderByDesc('deleted_at')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.lead_form_fields.partials.table', [
                    'leadFormFields' => $leadFormFields,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        $title = 'Trashed Booking Form Fields';
        $breadcrumb = [
            ['text' => 'Lead Management', 'url' => null],
            ['text' => 'Booking Form Fields', 'url' => route('admin.lead_form_fields.index')],
            ['text' => 'Trash', 'url' => null],
        ];

        return view('backoffice.admin.lead_form_fields.trash', compact(
            'leadFormFields',
            'title',
            'breadcrumb',
        ));
    }

    public function restore(int $leadFormField)
    {
        $this->ensurePermission('lead_form_field_restore');

        LeadFormField::onlyTrashed()->findOrFail($leadFormField)->restore();

        return response()->json([
            'success' => true,
            'message' => 'Booking form field restored successfully.',
        ]);
    }

    public function forceDelete(int $leadFormField)
    {
        $this->ensurePermission('lead_form_field_force_delete');

        LeadFormField::onlyTrashed()->findOrFail($leadFormField)->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Booking form field permanently deleted. Historical answers retain their field key and label snapshots.',
        ]);
    }

    private function validateLeadFormField(Request $request, ?LeadFormField $leadFormField = null): array
    {
        $rules = [
            'label' => ['required', 'string', 'max:150'],
            'field_key' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-z][a-z0-9_]*$/',
                Rule::unique('lead_form_fields', 'field_key')->ignore($leadFormField?->id),
            ],
            'placeholder' => ['nullable', 'string', 'max:190'],
            'options' => ['required', 'array', 'min:1', 'max:100'],
            'options.*' => ['required', 'string', 'max:190', 'distinct'],
            'is_required' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];

        $validated = $request->validate($rules);

        if ($leadFormField && $validated['field_key'] !== $leadFormField->field_key) {
            throw ValidationException::withMessages([
                'field_key' => 'Field key is immutable after creation because historical lead answers depend on it.',
            ]);
        }

        return $validated;
    }

    private function attributes(array $validated): array
    {
        $options = collect($validated['options'])
            ->map(fn ($option) => trim((string) $option))
            ->filter()
            ->uniqueStrict()
            ->values()
            ->all();

        if ($options === []) {
            throw ValidationException::withMessages([
                'options' => 'Add at least one non-empty option.',
            ]);
        }

        return [
            'label' => trim($validated['label']),
            'field_key' => strtolower(trim($validated['field_key'])),
            'placeholder' => filled($validated['placeholder'] ?? null)
                ? trim((string) $validated['placeholder'])
                : null,
            'options' => $options,
            'is_required' => (bool) $validated['is_required'],
            'is_active' => (bool) $validated['is_active'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ];
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->where('label', 'like', "%{$search}%")
                    ->orWhere('field_key', 'like', "%{$search}%")
                    ->orWhere('placeholder', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
    }

    private function bulkStatus(array $ids, bool $status): string
    {
        LeadFormField::query()->whereIn('id', $ids)->update([
            'is_active' => $status,
            'updated_at' => now(),
        ]);

        return $status
            ? 'Selected booking fields activated.'
            : 'Selected booking fields deactivated.';
    }

    private function bulkDelete(array $ids): string
    {
        LeadFormField::query()
            ->whereIn('id', $ids)
            ->get()
            ->each(fn (LeadFormField $field) => $field->delete());

        return 'Selected booking fields moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        LeadFormField::onlyTrashed()->whereIn('id', $ids)->restore();

        return 'Selected booking fields restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        LeadFormField::onlyTrashed()
            ->whereIn('id', $ids)
            ->get()
            ->each(fn (LeadFormField $field) => $field->forceDelete());

        return 'Selected booking fields permanently deleted.';
    }

    private function ensurePermission(string $permission): void
    {
        abort_unless(auth('admin')->user()?->can($permission), 403);
    }
}
