<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceFeatureController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePermission('service_feature_list');

        $query = ServiceFeature::query()->with('service:id,name,slug');
        $this->applyFilters($query, $request);
        $features = $query->orderBy('service_id')->ordered()->paginate(15)->withQueryString();
        $services = Service::query()->select('id', 'name')->orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json(['html' => view('backoffice.admin.service_features.partials.table', compact('features'))->render()]);
        }

        $title = 'Service Features Management';
        $breadcrumb = [['text' => 'CMS', 'url' => null], ['text' => 'Service Features', 'url' => route('admin.service_features.index')]];

        return view('backoffice.admin.service_features.index', compact('features', 'services', 'title', 'breadcrumb'));
    }

    public function create(Request $request)
    {
        $this->ensurePermission('service_feature_create');
        abort_unless($request->ajax(), 404);

        $services = Service::query()->select('id', 'name')->orderBy('name')->get();
        $selectedServiceId = $request->integer('service_id') ?: null;

        return response()->json(['html' => view('backoffice.admin.service_features.partials.form', compact('services', 'selectedServiceId'))->render()]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('service_feature_create');
        $validated = $this->validateFeature($request);

        $feature = DB::transaction(function () use ($validated): ServiceFeature {
            if (! isset($validated['sort_order']) || $validated['sort_order'] === null || $validated['sort_order'] === '') {
                $validated['sort_order'] = (int) ServiceFeature::query()->where('service_id', $validated['service_id'])->max('sort_order') + 1;
            }

            $validated['description'] = $this->sanitizeRichText($validated['description'] ?? null);
            $validated['is_active'] = (bool) $validated['is_active'];

            return ServiceFeature::create($validated);
        });

        return response()->json(['success' => true, 'message' => 'Service feature created successfully.', 'data' => ['id' => $feature->id]]);
    }

    public function show(Request $request, ServiceFeature $serviceFeature)
    {
        $this->ensurePermission('service_feature_view');
        abort_unless($request->ajax(), 404);
        $serviceFeature->load('service:id,name,slug');

        return response()->json(['html' => view('backoffice.admin.service_features.partials.show', compact('serviceFeature'))->render()]);
    }

    public function edit(Request $request, ServiceFeature $serviceFeature)
    {
        $this->ensurePermission('service_feature_update');
        abort_unless($request->ajax(), 404);
        $services = Service::query()->select('id', 'name')->orderBy('name')->get();

        return response()->json(['html' => view('backoffice.admin.service_features.partials.form', compact('serviceFeature', 'services'))->render()]);
    }

    public function update(Request $request, ServiceFeature $serviceFeature)
    {
        $this->ensurePermission('service_feature_update');
        $validated = $this->validateFeature($request, $serviceFeature);
        $validated['description'] = $this->sanitizeRichText($validated['description'] ?? null);
        $validated['is_active'] = (bool) $validated['is_active'];
        $serviceFeature->update($validated);

        return response()->json(['success' => true, 'message' => 'Service feature updated successfully.']);
    }

    public function destroy(ServiceFeature $serviceFeature)
    {
        $this->ensurePermission('service_feature_delete');
        $serviceFeature->delete();

        return response()->json(['success' => true, 'message' => 'Service feature moved to trash.']);
    }

    public function toggle(ServiceFeature $serviceFeature)
    {
        $this->ensurePermission('service_feature_toggle');
        $serviceFeature->update(['is_active' => ! $serviceFeature->is_active]);

        return response()->json(['success' => true, 'message' => $serviceFeature->is_active ? 'Service feature activated.' : 'Service feature deactivated.']);
    }

    public function duplicate(ServiceFeature $serviceFeature)
    {
        $this->ensurePermission('service_feature_duplicate');

        $copy = ServiceFeature::create([
            'service_id' => $serviceFeature->service_id,
            'title' => Str::limit($serviceFeature->title . ' Copy', 190, ''),
            'description' => $serviceFeature->description,
            'icon' => $serviceFeature->icon,
            'sort_order' => (int) ServiceFeature::query()->where('service_id', $serviceFeature->service_id)->max('sort_order') + 1,
            'is_active' => false,
        ]);

        return response()->json(['success' => true, 'message' => 'Service feature duplicated as inactive.', 'data' => ['id' => $copy->id]]);
    }

    public function reorder(Request $request)
    {
        $this->ensurePermission('service_feature_reorder');
        $validated = $request->validate(['items' => ['required', 'array', 'min:1'], 'items.*.id' => ['required', 'integer', 'distinct'], 'items.*.sort_order' => ['required', 'integer', 'min:0']]);

        DB::transaction(function () use ($validated): void {
            foreach ($validated['items'] as $item) {
                ServiceFeature::query()->whereKey((int) $item['id'])->update(['sort_order' => (int) $item['sort_order']]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Service feature order updated.']);
    }

    public function trash(Request $request)
    {
        $this->ensurePermission('service_feature_trash');

        $query = ServiceFeature::onlyTrashed()->with('service:id,name,slug');
        $this->applyFilters($query, $request);
        $features = $query->latest('deleted_at')->paginate(15)->withQueryString();
        $services = Service::withTrashed()->select('id', 'name')->orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json(['html' => view('backoffice.admin.service_features.partials.table', ['features' => $features, 'isTrash' => true])->render()]);
        }

        $title = 'Trashed Service Features';
        $breadcrumb = [['text' => 'CMS', 'url' => null], ['text' => 'Service Features', 'url' => route('admin.service_features.index')], ['text' => 'Trash', 'url' => null]];

        return view('backoffice.admin.service_features.trash', compact('features', 'services', 'title', 'breadcrumb'));
    }

    public function restore(int $serviceFeature)
    {
        $this->ensurePermission('service_feature_restore');
        $record = ServiceFeature::onlyTrashed()->findOrFail($serviceFeature);
        $record->restore();

        return response()->json(['success' => true, 'message' => 'Service feature restored successfully.']);
    }

    public function forceDelete(int $serviceFeature)
    {
        $this->ensurePermission('service_feature_force_delete');
        $record = ServiceFeature::onlyTrashed()->findOrFail($serviceFeature);
        $record->forceDelete();

        return response()->json(['success' => true, 'message' => 'Service feature permanently deleted.']);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['activate', 'deactivate', 'delete', 'restore', 'force_delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $permission = match ($validated['action']) {
            'activate', 'deactivate' => 'service_feature_toggle',
            'delete' => 'service_feature_delete',
            'restore' => 'service_feature_restore',
            'force_delete' => 'service_feature_force_delete',
        };

        $this->ensurePermission($permission);
        $ids = collect($validated['ids'])->map(fn ($id) => (int) $id)->unique()->values()->all();

        $message = match ($validated['action']) {
            'activate' => $this->bulkToggle($ids, true),
            'deactivate' => $this->bulkToggle($ids, false),
            'delete' => $this->bulkDelete($ids),
            'restore' => $this->bulkRestore($ids),
            'force_delete' => $this->bulkForceDelete($ids),
        };

        return response()->json(['success' => true, 'message' => $message]);
    }

    private function validateFeature(Request $request, ?ServiceFeature $serviceFeature = null): array
    {
        return $request->validate([
            'service_id' => ['required', 'integer', Rule::exists('services', 'id')->whereNull('deleted_at')],
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:60000'],
            'icon' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(fn ($q) => $q->where('title', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")->orWhere('icon', 'like', "%{$search}%"));
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', (int) $request->service_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
    }

    private function sanitizeRichText(mixed $value): ?string
    {
        $html = trim((string) ($value ?? ''));
        if ($html === '') return null;

        $allowedTags = '<p><br><strong><b><em><i><u><s><ul><ol><li><blockquote><h1><h2><h3><h4><h5><h6><a><hr><pre><code><span><table><thead><tbody><tfoot><tr><th><td>';
        $html = strip_tags($html, $allowedTags);
        $html = preg_replace('/\s(?:on\w+|style|srcdoc|formaction)\s*=\s*(["\']).*?\1/isu', '', $html) ?? $html;
        $html = preg_replace('/\s(?:on\w+|style|srcdoc|formaction)\s*=\s*[^\s>]+/iu', '', $html) ?? $html;
        $html = preg_replace_callback('/\s(href)\s*=\s*(["\'])(.*?)\2/isu', function (array $matches): string {
            $url = trim(html_entity_decode($matches[3], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
            if ($url === '' || Str::startsWith(strtolower($url), ['javascript:', 'data:', 'vbscript:'])) return '';
            return ' href=' . $matches[2] . e($url) . $matches[2];
        }, $html) ?? $html;

        return trim($html) !== '' ? trim($html) : null;
    }

    private function bulkToggle(array $ids, bool $active): string
    {
        ServiceFeature::query()->whereIn('id', $ids)->update(['is_active' => $active, 'updated_at' => now()]);
        return $active ? 'Selected service features activated.' : 'Selected service features deactivated.';
    }

    private function bulkDelete(array $ids): string
    {
        ServiceFeature::query()->whereIn('id', $ids)->get()->each->delete();
        return 'Selected service features moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        ServiceFeature::onlyTrashed()->whereIn('id', $ids)->get()->each->restore();
        return 'Selected service features restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        ServiceFeature::onlyTrashed()->whereIn('id', $ids)->get()->each->forceDelete();
        return 'Selected service features permanently deleted.';
    }

    private function ensurePermission(string $permission): void
    {
        $admin = auth('admin')->user();
        abort_unless($admin && $admin->can($permission), 403, 'You do not have permission to perform this service feature action.');
    }
}
