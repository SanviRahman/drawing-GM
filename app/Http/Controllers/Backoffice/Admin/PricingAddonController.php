<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingAddon;
use App\Services\PricingAddonMediaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PricingAddonController extends Controller
{
    public function __construct(private readonly PricingAddonMediaService $pricingAddonMediaService) {}

    public function index(Request $request)
    {
        $this->ensurePermission('pricing_addon_list');

        $query = PricingAddon::query()->with('media')->ordered();
        $this->applyFilters($query, $request);
        $addons = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.pricing_addons.partials.table', ['addons' => $addons, 'isTrash' => false])->render(),
            ]);
        }

        $title = 'Pricing Add-ons Management';
        $breadcrumb = [
            ['text' => 'CMS', 'url' => null],
            ['text' => 'Pricing Add-ons', 'url' => route('admin.pricing_addons.index')],
        ];

        return view('backoffice.admin.pricing_addons.index', compact('addons', 'title', 'breadcrumb'));
    }

    public function list(Request $request)
    {
        $this->ensurePermission('pricing_addon_list');

        $query = PricingAddon::query()->select('id', 'name', 'amount', 'amount_max', 'price_type', 'unit', 'is_active', 'sort_order');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(fn (Builder $builder) => $builder->where('name', 'like', "%{$search}%")->orWhere('unit', 'like', "%{$search}%"));
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        return response()->json([
            'success' => true,
            'data' => $query->ordered()->limit(100)->get(),
        ]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('pricing_addon_create');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.pricing_addons.partials.form')->render(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('pricing_addon_create');
        $validated = $this->validatePricingAddon($request);

        if (! array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = $this->nextSortOrder();
        }

        $pricingAddon = DB::transaction(function () use ($request, $validated): PricingAddon {
            $pricingAddon = PricingAddon::create($this->addonAttributes($validated));
            $this->pricingAddonMediaService->syncFromRequest($request, $pricingAddon);

            return $pricingAddon;
        });

        return response()->json([
            'success' => true,
            'message' => 'Pricing add-on created successfully.',
            'data' => ['id' => $pricingAddon->id],
        ]);
    }

    public function show(Request $request, PricingAddon $pricingAddon)
    {
        $this->ensurePermission('pricing_addon_view');
        abort_unless($request->ajax(), 404);

        $pricingAddon->load('media');

        return response()->json([
            'html' => view('backoffice.admin.pricing_addons.partials.show', compact('pricingAddon'))->render(),
        ]);
    }

    public function edit(Request $request, PricingAddon $pricingAddon)
    {
        $this->ensurePermission('pricing_addon_update');
        abort_unless($request->ajax(), 404);

        $pricingAddon->load('media');

        return response()->json([
            'html' => view('backoffice.admin.pricing_addons.partials.form', compact('pricingAddon'))->render(),
        ]);
    }

    public function update(Request $request, PricingAddon $pricingAddon)
    {
        $this->ensurePermission('pricing_addon_update');
        $validated = $this->validatePricingAddon($request);

        if (! array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = $pricingAddon->sort_order;
        }

        DB::transaction(function () use ($request, $validated, $pricingAddon): void {
            $pricingAddon->update($this->addonAttributes($validated));
            $this->pricingAddonMediaService->syncFromRequest($request, $pricingAddon);
        });

        return response()->json([
            'success' => true,
            'message' => 'Pricing add-on updated successfully.',
        ]);
    }

    public function destroy(PricingAddon $pricingAddon)
    {
        $this->ensurePermission('pricing_addon_delete');
        $pricingAddon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pricing add-on moved to trash.',
        ]);
    }

    public function toggle(PricingAddon $pricingAddon)
    {
        $this->ensurePermission('pricing_addon_toggle');
        $pricingAddon->update(['is_active' => ! $pricingAddon->is_active]);

        return response()->json([
            'success' => true,
            'message' => $pricingAddon->is_active ? 'Pricing add-on activated.' : 'Pricing add-on deactivated.',
        ]);
    }

    public function duplicate(PricingAddon $pricingAddon)
    {
        $this->ensurePermission('pricing_addon_duplicate');

        $copy = DB::transaction(function () use ($pricingAddon): PricingAddon {
            $copy = $pricingAddon->replicate();
            $copy->name = mb_substr($pricingAddon->name . ' Copy', 0, 190);
            $copy->sort_order = $this->nextSortOrder();
            $copy->is_active = false;
            $copy->save();

            $this->pricingAddonMediaService->duplicateMedia($pricingAddon, $copy);

            return $copy;
        });

        return response()->json([
            'success' => true,
            'message' => 'Pricing add-on duplicated as inactive.',
            'data' => ['id' => $copy->id],
        ]);
    }

    public function trash(Request $request)
    {
        $this->ensurePermission('pricing_addon_trash');

        $query = PricingAddon::onlyTrashed()->with('media')->orderByDesc('deleted_at');
        $this->applyFilters($query, $request);
        $addons = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.pricing_addons.partials.table', ['addons' => $addons, 'isTrash' => true])->render(),
            ]);
        }

        $title = 'Trashed Pricing Add-ons';
        $breadcrumb = [
            ['text' => 'CMS', 'url' => null],
            ['text' => 'Pricing Add-ons', 'url' => route('admin.pricing_addons.index')],
            ['text' => 'Trash', 'url' => null],
        ];

        return view('backoffice.admin.pricing_addons.trash', compact('addons', 'title', 'breadcrumb'));
    }

    public function restore(int $pricingAddon)
    {
        $this->ensurePermission('pricing_addon_restore');
        PricingAddon::onlyTrashed()->findOrFail($pricingAddon)->restore();

        return response()->json([
            'success' => true,
            'message' => 'Pricing add-on restored successfully.',
        ]);
    }

    public function forceDelete(int $pricingAddon)
    {
        $this->ensurePermission('pricing_addon_force_delete');

        DB::transaction(function () use ($pricingAddon): void {
            $record = PricingAddon::onlyTrashed()->findOrFail($pricingAddon);
            $this->ensureForceDeletable([$record->id]);
            $this->pricingAddonMediaService->purgeAll($record);
            $record->forceDelete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Pricing add-on permanently deleted.',
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

    private function validatePricingAddon(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:60000'],
            'price_type' => ['required', Rule::in(['fixed', 'from', 'range', 'call'])],
            'amount' => ['nullable', 'required_unless:price_type,call', 'numeric', 'min:0', 'max:9999999999.99'],
            'amount_max' => ['nullable', 'required_if:price_type,range', 'numeric', 'min:0', 'max:9999999999.99', 'gte:amount'],
            'unit' => ['nullable', 'string', 'max:80'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'image_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')->whereNull('deleted_at')],
            'image_remove' => ['nullable', 'boolean'],
        ]);
    }

    private function addonAttributes(array $validated): array
    {
        if ($validated['price_type'] === 'call') {
            $validated['amount'] = null;
            $validated['amount_max'] = null;
        } elseif ($validated['price_type'] !== 'range') {
            $validated['amount_max'] = null;
        }

        return [
            'name' => trim($validated['name']),
            'description' => $this->sanitizeRichText($validated['description'] ?? null),
            'amount' => $validated['amount'] ?? null,
            'amount_max' => $validated['amount_max'] ?? null,
            'price_type' => $validated['price_type'],
            'unit' => $this->nullableTrim($validated['unit'] ?? null),
            'is_active' => (bool) $validated['is_active'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ];
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('unit', 'like', "%{$search}%");
            });
        }

        if ($request->filled('price_type')) {
            $query->where('price_type', (string) $request->price_type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
    }

    private function nextSortOrder(): int
    {
        return ((int) PricingAddon::withTrashed()->max('sort_order')) + 10;
    }

    private function bulkStatus(array $ids, bool $status): string
    {
        $this->ensurePermission('pricing_addon_toggle');
        PricingAddon::whereIn('id', $ids)->update(['is_active' => $status]);

        return $status ? 'Selected pricing add-ons activated.' : 'Selected pricing add-ons deactivated.';
    }

    private function bulkDelete(array $ids): string
    {
        $this->ensurePermission('pricing_addon_delete');
        PricingAddon::whereIn('id', $ids)->delete();

        return 'Selected pricing add-ons moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        $this->ensurePermission('pricing_addon_restore');
        PricingAddon::onlyTrashed()->whereIn('id', $ids)->restore();

        return 'Selected pricing add-ons restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        $this->ensurePermission('pricing_addon_force_delete');

        DB::transaction(function () use ($ids): void {
            $addons = PricingAddon::onlyTrashed()->whereIn('id', $ids)->get();
            $this->ensureForceDeletable($addons->pluck('id')->all());

            $addons->each(function (PricingAddon $pricingAddon): void {
                $this->pricingAddonMediaService->purgeAll($pricingAddon);
                $pricingAddon->forceDelete();
            });
        });

        return 'Selected pricing add-ons permanently deleted.';
    }

    private function ensureForceDeletable(array $ids): void
    {
        if ($ids === [] || ! Schema::hasTable('pricing_package_addon')) {
            return;
        }

        if (DB::table('pricing_package_addon')->whereIn('pricing_addon_id', $ids)->exists()) {
            throw ValidationException::withMessages([
                'pricing_addon' => 'One or more pricing add-ons are still attached to pricing packages. Detach them before permanently deleting.',
            ]);
        }
    }

    private function sanitizeRichText(mixed $value): ?string
    {
        $html = trim((string) ($value ?? ''));

        if ($html === '') {
            return null;
        }

        $allowedTags = '<p><br><strong><b><em><i><u><s><ul><ol><li><blockquote><h1><h2><h3><h4><h5><h6><a><hr><pre><code><span><table><thead><tbody><tfoot><tr><th><td><img>';
        $html = strip_tags($html, $allowedTags);
        $html = preg_replace('/\s(?:on\w+|style|srcdoc|formaction)\s*=\s*(["\']).*?\1/isu', '', $html) ?? $html;
        $html = preg_replace('/\s(?:on\w+|style|srcdoc|formaction)\s*=\s*[^\s>]+/iu', '', $html) ?? $html;
        $html = preg_replace_callback('/\s(href|src)\s*=\s*(["\'])(.*?)\2/isu', function (array $matches): string {
            $url = trim(html_entity_decode($matches[3], ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            if ($url === '' || Str::startsWith(strtolower($url), ['javascript:', 'data:', 'vbscript:'])) {
                return '';
            }

            return ' ' . strtolower($matches[1]) . '=' . $matches[2] . e($url) . $matches[2];
        }, $html) ?? $html;

        return trim($html) !== '' ? trim($html) : null;
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
