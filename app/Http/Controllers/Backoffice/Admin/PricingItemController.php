<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingItem;
use App\Models\PricingPackage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PricingItemController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePermission('pricing_item_list');

        $query = PricingItem::query()
            ->with(['pricingPackage.service', 'pricingPackage.location'])
            ->ordered();

        $this->applyFilters($query, $request);

        $items = $query->paginate(15)->withQueryString();
        $packages = $this->packageOptions();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.pricing_items.partials.table', [
                    'items' => $items,
                    'isTrash' => false,
                ])->render(),
            ]);
        }

        $title = 'Pricing Items Management';
        $breadcrumb = [
            ['text' => 'CMS', 'url' => null],
            ['text' => 'Pricing Items', 'url' => route('admin.pricing_items.index')],
        ];

        return view('backoffice.admin.pricing_items.index', compact('items', 'packages', 'title', 'breadcrumb'));
    }

    public function list(Request $request)
    {
        $this->ensurePermission('pricing_item_list');

        $query = PricingItem::query()
            ->with('pricingPackage:id,name,currency')
            ->select('id', 'pricing_package_id', 'label', 'amount', 'amount_max', 'price_type', 'unit', 'is_active');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($builder) use ($search) {
                $builder->where('label', 'like', "%{$search}%")
                    ->orWhere('unit', 'like', "%{$search}%")
                    ->orWhereHas('pricingPackage', fn ($packageQuery) => $packageQuery->where('name', 'like', "%{$search}%"));
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query->ordered()->limit(50)->get(),
        ]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('pricing_item_create');
        abort_unless($request->ajax(), 404);

        $packages = $this->packageOptions();
        $selectedPackageId = $request->integer('pricing_package_id') ?: null;

        return response()->json([
            'html' => view('backoffice.admin.pricing_items.partials.form', compact('packages', 'selectedPackageId'))->render(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('pricing_item_create');

        $validated = $this->validatePricingItem($request);

        if (! array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = $this->nextSortOrder((int) $validated['pricing_package_id']);
        }

        PricingItem::create($this->normalizePriceData($validated));

        return response()->json([
            'success' => true,
            'message' => 'Pricing item created successfully.',
        ]);
    }

    public function show(Request $request, PricingItem $pricingItem)
    {
        $this->ensurePermission('pricing_item_view');
        abort_unless($request->ajax(), 404);

        $pricingItem->load(['pricingPackage.service', 'pricingPackage.location']);

        return response()->json([
            'html' => view('backoffice.admin.pricing_items.partials.show', compact('pricingItem'))->render(),
        ]);
    }

    public function edit(Request $request, PricingItem $pricingItem)
    {
        $this->ensurePermission('pricing_item_update');
        abort_unless($request->ajax(), 404);

        $packages = $this->packageOptions();

        return response()->json([
            'html' => view('backoffice.admin.pricing_items.partials.form', compact('pricingItem', 'packages'))->render(),
        ]);
    }

    public function update(Request $request, PricingItem $pricingItem)
    {
        $this->ensurePermission('pricing_item_update');

        $validated = $this->validatePricingItem($request);

        if (! array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = $pricingItem->sort_order;
        }

        $pricingItem->update($this->normalizePriceData($validated));

        return response()->json([
            'success' => true,
            'message' => 'Pricing item updated successfully.',
        ]);
    }

    public function destroy(PricingItem $pricingItem)
    {
        $this->ensurePermission('pricing_item_delete');

        $pricingItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pricing item moved to trash.',
        ]);
    }

    public function toggle(PricingItem $pricingItem)
    {
        $this->ensurePermission('pricing_item_toggle');

        $pricingItem->update([
            'is_active' => ! $pricingItem->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => $pricingItem->is_active ? 'Pricing item activated.' : 'Pricing item deactivated.',
        ]);
    }

    public function duplicate(PricingItem $pricingItem)
    {
        $this->ensurePermission('pricing_item_duplicate');

        $copy = $pricingItem->replicate();
        $copy->label = mb_substr($pricingItem->label . ' Copy', 0, 190);
        $copy->sort_order = $this->nextSortOrder((int) $pricingItem->pricing_package_id);
        $copy->is_active = false;
        $copy->save();

        return response()->json([
            'success' => true,
            'message' => 'Pricing item duplicated as inactive.',
            'data' => ['id' => $copy->id],
        ]);
    }

    public function trash(Request $request)
    {
        $this->ensurePermission('pricing_item_trash');

        $query = PricingItem::onlyTrashed()
            ->with(['pricingPackage.service', 'pricingPackage.location'])
            ->orderByDesc('deleted_at');

        $this->applyFilters($query, $request);

        $items = $query->paginate(15)->withQueryString();
        $packages = $this->packageOptions(true);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.pricing_items.partials.table', [
                    'items' => $items,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        $title = 'Trashed Pricing Items';
        $breadcrumb = [
            ['text' => 'CMS', 'url' => null],
            ['text' => 'Pricing Items', 'url' => route('admin.pricing_items.index')],
            ['text' => 'Trash', 'url' => null],
        ];

        return view('backoffice.admin.pricing_items.trash', compact('items', 'packages', 'title', 'breadcrumb'));
    }

    public function restore(int $pricingItem)
    {
        $this->ensurePermission('pricing_item_restore');

        PricingItem::onlyTrashed()->findOrFail($pricingItem)->restore();

        return response()->json([
            'success' => true,
            'message' => 'Pricing item restored successfully.',
        ]);
    }

    public function forceDelete(int $pricingItem)
    {
        $this->ensurePermission('pricing_item_force_delete');

        PricingItem::onlyTrashed()->findOrFail($pricingItem)->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Pricing item permanently deleted.',
        ]);
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

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    private function validatePricingItem(Request $request): array
    {
        return $request->validate([
            'pricing_package_id' => ['required', 'integer', Rule::exists('pricing_packages', 'id')->whereNull('deleted_at')],
            'label' => ['required', 'string', 'max:190'],
            'price_type' => ['required', Rule::in(['fixed', 'from', 'range', 'call'])],
            'amount' => ['nullable', 'required_unless:price_type,call', 'numeric', 'min:0', 'max:9999999999.99'],
            'amount_max' => ['nullable', 'required_if:price_type,range', 'numeric', 'min:0', 'max:9999999999.99', 'gte:amount'],
            'unit' => ['nullable', 'string', 'max:80'],
            'prefix' => ['nullable', 'string', 'max:40'],
            'suffix' => ['nullable', 'string', 'max:40'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function normalizePriceData(array $validated): array
    {
        if ($validated['price_type'] === 'call') {
            $validated['amount'] = null;
            $validated['amount_max'] = null;
        } elseif ($validated['price_type'] !== 'range') {
            $validated['amount_max'] = null;
        }

        $validated['unit'] = $this->nullableTrim($validated['unit'] ?? null);
        $validated['prefix'] = $this->nullableTrim($validated['prefix'] ?? null);
        $validated['suffix'] = $this->nullableTrim($validated['suffix'] ?? null);

        return $validated;
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('pricing_package_id')) {
            $query->where('pricing_package_id', $request->integer('pricing_package_id'));
        }

        if ($request->filled('price_type')) {
            $query->where('price_type', (string) $request->price_type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            }

            if ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($builder) use ($search) {
                $builder->where('label', 'like', "%{$search}%")
                    ->orWhere('unit', 'like', "%{$search}%")
                    ->orWhere('prefix', 'like', "%{$search}%")
                    ->orWhere('suffix', 'like', "%{$search}%")
                    ->orWhereHas('pricingPackage', fn ($packageQuery) => $packageQuery->where('name', 'like', "%{$search}%"));
            });
        }
    }

    private function packageOptions(bool $withTrashed = false)
    {
        $query = PricingPackage::query()->with(['service:id,name', 'location:id,name']);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->orderBy('name')->get();
    }

    private function nextSortOrder(int $packageId): int
    {
        return ((int) PricingItem::withTrashed()->where('pricing_package_id', $packageId)->max('sort_order')) + 10;
    }

    private function bulkStatus(array $ids, bool $status): string
    {
        $this->ensurePermission('pricing_item_toggle');

        PricingItem::whereIn('id', $ids)->update(['is_active' => $status]);

        return $status ? 'Selected pricing items activated.' : 'Selected pricing items deactivated.';
    }

    private function bulkDelete(array $ids): string
    {
        $this->ensurePermission('pricing_item_delete');

        PricingItem::whereIn('id', $ids)->delete();

        return 'Selected pricing items moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        $this->ensurePermission('pricing_item_restore');

        PricingItem::onlyTrashed()->whereIn('id', $ids)->restore();

        return 'Selected pricing items restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        $this->ensurePermission('pricing_item_force_delete');

        PricingItem::onlyTrashed()->whereIn('id', $ids)->forceDelete();

        return 'Selected pricing items permanently deleted.';
    }

    private function nullableTrim(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function ensurePermission(string $permission): void
    {
        abort_unless(auth('admin')->user()?->can($permission), 403);
    }
}
