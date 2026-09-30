<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Models\PricingPackage;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PricingPackageController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePermission('pricing_package_list');

        $query = PricingPackage::query()->with(['service:id,name', 'location:id,name']);
        $this->applyFilters($query, $request);

        $packages = $query->orderBy('sort_order')->latest('id')->paginate(15)->withQueryString();
        $services = Service::query()->orderBy('name')->get(['id', 'name']);
        $locations = Location::query()->orderBy('name')->get(['id', 'name']);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.pricing_packages.partials.table', compact('packages'))->render(),
            ]);
        }

        $title = 'Pricing Packages Management';
        $breadcrumb = [
            ['text' => 'CMS', 'url' => null],
            ['text' => 'Pricing Packages', 'url' => route('admin.pricing_packages.index')],
        ];

        return view('backoffice.admin.pricing_packages.index', compact('packages', 'services', 'locations', 'title', 'breadcrumb'));
    }

    public function list(Request $request)
    {
        $this->ensurePermission('pricing_package_list');

        $query = PricingPackage::query()->select('id', 'name', 'currency', 'is_active');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where('name', 'like', "%{$search}%");
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderBy('name')->limit(50)->get(),
        ]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('pricing_package_create');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.pricing_packages.partials.form', $this->formData())->render(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('pricing_package_create');
        $validated = $this->validatePackage($request);
        $validated['description'] = $this->sanitizeRichText($validated['description'] ?? null);
        $validated['currency'] = strtoupper($validated['currency']);

        PricingPackage::create($validated);

        return response()->json(['success' => true, 'message' => 'Pricing package created successfully.']);
    }

    public function show(Request $request, PricingPackage $pricingPackage)
    {
        $this->ensurePermission('pricing_package_view');
        abort_unless($request->ajax(), 404);

        $pricingPackage->load(['service', 'location']);

        return response()->json([
            'html' => view('backoffice.admin.pricing_packages.partials.show', compact('pricingPackage'))->render(),
        ]);
    }

    public function edit(Request $request, PricingPackage $pricingPackage)
    {
        $this->ensurePermission('pricing_package_update');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.pricing_packages.partials.form', array_merge($this->formData(), compact('pricingPackage')))->render(),
        ]);
    }

    public function update(Request $request, PricingPackage $pricingPackage)
    {
        $this->ensurePermission('pricing_package_update');
        $validated = $this->validatePackage($request, $pricingPackage);
        $validated['description'] = $this->sanitizeRichText($validated['description'] ?? null);
        $validated['currency'] = strtoupper($validated['currency']);

        $pricingPackage->update($validated);

        return response()->json(['success' => true, 'message' => 'Pricing package updated successfully.']);
    }

    public function destroy(PricingPackage $pricingPackage)
    {
        $this->ensurePermission('pricing_package_delete');
        $pricingPackage->delete();

        return response()->json(['success' => true, 'message' => 'Pricing package moved to trash.']);
    }

    public function toggle(PricingPackage $pricingPackage)
    {
        $this->ensurePermission('pricing_package_toggle');
        $pricingPackage->update(['is_active' => ! $pricingPackage->is_active]);

        return response()->json(['success' => true, 'message' => $pricingPackage->is_active ? 'Pricing package activated.' : 'Pricing package deactivated.']);
    }

    public function duplicate(PricingPackage $pricingPackage)
    {
        $this->ensurePermission('pricing_package_duplicate');

        $copy = PricingPackage::create([
            'service_id' => $pricingPackage->service_id,
            'location_id' => $pricingPackage->location_id,
            'name' => $pricingPackage->name . ' Copy',
            'subtitle' => $pricingPackage->subtitle,
            'badge' => $pricingPackage->badge,
            'description' => $pricingPackage->description,
            'currency' => $pricingPackage->currency,
            'is_featured' => false,
            'is_active' => false,
            'sort_order' => $pricingPackage->sort_order,
        ]);

        return response()->json(['success' => true, 'message' => 'Pricing package duplicated.', 'data' => ['id' => $copy->id]]);
    }

    public function trash(Request $request)
    {
        $this->ensurePermission('pricing_package_trash');

        $query = PricingPackage::onlyTrashed()->with(['service:id,name', 'location:id,name']);
        $this->applyFilters($query, $request);
        $packages = $query->latest('deleted_at')->paginate(15)->withQueryString();
        $services = Service::query()->orderBy('name')->get(['id', 'name']);
        $locations = Location::query()->orderBy('name')->get(['id', 'name']);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.pricing_packages.partials.table', ['packages' => $packages, 'isTrash' => true])->render(),
            ]);
        }

        $title = 'Trashed Pricing Packages';
        $breadcrumb = [
            ['text' => 'CMS', 'url' => null],
            ['text' => 'Pricing Packages', 'url' => route('admin.pricing_packages.index')],
            ['text' => 'Trash', 'url' => null],
        ];

        return view('backoffice.admin.pricing_packages.trash', compact('packages', 'services', 'locations', 'title', 'breadcrumb'));
    }

    public function restore(int $pricingPackage)
    {
        $this->ensurePermission('pricing_package_restore');
        PricingPackage::onlyTrashed()->findOrFail($pricingPackage)->restore();

        return response()->json(['success' => true, 'message' => 'Pricing package restored successfully.']);
    }

    public function forceDelete(int $pricingPackage)
    {
        $this->ensurePermission('pricing_package_force_delete');
        PricingPackage::onlyTrashed()->findOrFail($pricingPackage)->forceDelete();

        return response()->json(['success' => true, 'message' => 'Pricing package permanently deleted.']);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['activate', 'deactivate', 'delete', 'restore', 'force_delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $ids = collect($validated['ids'])->map(fn ($id) => (int) $id)->unique()->values()->all();

        $message = match ($validated['action']) {
            'activate' => $this->bulkStatus($ids, true),
            'deactivate' => $this->bulkStatus($ids, false),
            'delete' => $this->bulkDelete($ids),
            'restore' => $this->bulkRestore($ids),
            'force_delete' => $this->bulkForceDelete($ids),
        };

        return response()->json(['success' => true, 'message' => $message]);
    }

    private function formData(): array
    {
        return [
            'services' => Service::query()->orderBy('name')->get(['id', 'name']),
            'locations' => Location::query()->orderBy('name')->get(['id', 'name']),
        ];
    }

    private function validatePackage(Request $request, ?PricingPackage $pricingPackage = null): array
    {
        return $request->validate([
            'service_id' => ['nullable', 'integer', Rule::exists('services', 'id')->whereNull('deleted_at')],
            'location_id' => ['nullable', 'integer', Rule::exists('locations', 'id')->whereNull('deleted_at')],
            'name' => ['required', 'string', 'max:190'],
            'subtitle' => ['nullable', 'string', 'max:190'],
            'badge' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:60000'],
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
            'is_featured' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('subtitle', 'like', "%{$search}%")
                    ->orWhere('badge', 'like', "%{$search}%")
                    ->orWhere('currency', 'like', "%{$search}%");
            });
        }

        if ($request->filled('service_id')) {
            $query->where('service_id', (int) $request->service_id);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', (int) $request->location_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('featured')) {
            $query->where('is_featured', $request->featured === 'yes');
        }
    }

    private function bulkStatus(array $ids, bool $status): string
    {
        $this->ensurePermission('pricing_package_toggle');
        PricingPackage::whereIn('id', $ids)->update(['is_active' => $status]);
        return $status ? 'Selected pricing packages activated.' : 'Selected pricing packages deactivated.';
    }

    private function bulkDelete(array $ids): string
    {
        $this->ensurePermission('pricing_package_delete');
        PricingPackage::whereIn('id', $ids)->delete();
        return 'Selected pricing packages moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        $this->ensurePermission('pricing_package_restore');
        PricingPackage::onlyTrashed()->whereIn('id', $ids)->restore();
        return 'Selected pricing packages restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        $this->ensurePermission('pricing_package_force_delete');
        DB::transaction(function () use ($ids): void {
            PricingPackage::onlyTrashed()->whereIn('id', $ids)->get()->each->forceDelete();
        });
        return 'Selected pricing packages permanently deleted.';
    }

    private function ensurePermission(string $permission): void
    {
        abort_unless(auth('admin')->user()?->can($permission), 403);
    }

    private function sanitizeRichText(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return null;
        }

        $clean = strip_tags($html, '<p><br><strong><b><em><i><u><ul><ol><li><a><blockquote><h2><h3><h4><span>');
        $clean = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?? $clean;
        $clean = preg_replace('/href\s*=\s*(["\'])\s*(?:javascript|data|vbscript):.*?\1/i', 'href="#"', $clean) ?? $clean;

        return trim($clean) !== '' ? $clean : null;
    }
}
