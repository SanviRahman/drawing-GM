<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\PricingAddon;
use App\Models\PricingPackage;
use App\Models\PricingPackageAddon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use JsonException;

class PricingPackageAddonController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePermission('pricing_package_addon_list');

        $query = PricingPackageAddon::query()
            ->with([
                'pricingPackage.service:id,name',
                'pricingPackage.location:id,name',
                'pricingAddon.media',
            ])
            ->ordered();

        $this->applyFilters($query, $request);

        $mappings = $query->paginate(15)->withQueryString();
        $packages = $this->packageOptions();
        $addons = $this->addonOptions();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.pricing_package_addons.partials.table', [
                    'mappings' => $mappings,
                    'isTrash' => false,
                ])->render(),
            ]);
        }

        $title = 'Pricing Package Add-ons Management';
        $breadcrumb = [
            ['text' => 'CMS', 'url' => null],
            ['text' => 'Pricing Package Add-ons', 'url' => route('admin.pricing_package_addons.index')],
        ];

        return view(
            'backoffice.admin.pricing_package_addons.index',
            compact('mappings', 'packages', 'addons', 'title', 'breadcrumb')
        );
    }

    public function list(Request $request)
    {
        $this->ensurePermission('pricing_package_addon_list');

        $query = PricingPackageAddon::query()
            ->with([
                'pricingPackage:id,name,currency',
                'pricingAddon:id,name,amount,amount_max,price_type,unit',
            ])
            ->ordered();

        $this->applyFilters($query, $request);

        return response()->json([
            'success' => true,
            'data' => $query->limit(100)->get(),
        ]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('pricing_package_addon_create');
        abort_unless($request->ajax(), 404);

        $packages = $this->packageOptions();
        $addons = $this->addonOptions();
        $selectedPackageId = $request->integer('pricing_package_id') ?: null;
        $selectedAddonId = $request->integer('pricing_addon_id') ?: null;

        return response()->json([
            'html' => view(
                'backoffice.admin.pricing_package_addons.partials.form',
                compact('packages', 'addons', 'selectedPackageId', 'selectedAddonId')
            )->render(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('pricing_package_addon_create');

        $validated = $this->validateMapping($request);
        $overrideData = $this->decodeOverrideData($validated['override_data'] ?? null);

        try {
            $mapping = DB::transaction(function () use ($validated, $overrideData): PricingPackageAddon {
                $existing = PricingPackageAddon::withTrashed()
                    ->where('pricing_package_id', (int) $validated['pricing_package_id'])
                    ->where('pricing_addon_id', (int) $validated['pricing_addon_id'])
                    ->lockForUpdate()
                    ->first();

                if ($existing && ! $existing->trashed()) {
                    throw ValidationException::withMessages([
                        'pricing_addon_id' => 'This pricing add-on is already attached to the selected package.',
                    ]);
                }

                if ($existing) {
                    $existing->fill(['override_data' => $overrideData]);
                    $existing->restore();

                    return $existing->fresh(['pricingPackage', 'pricingAddon']) ?? $existing;
                }

                return PricingPackageAddon::create([
                    'pricing_package_id' => (int) $validated['pricing_package_id'],
                    'pricing_addon_id' => (int) $validated['pricing_addon_id'],
                    'override_data' => $overrideData,
                ]);
            });
        } catch (QueryException $exception) {
            if ($this->isDuplicateKeyException($exception)) {
                throw ValidationException::withMessages([
                    'pricing_addon_id' => 'This pricing add-on is already attached to the selected package.',
                ]);
            }

            throw $exception;
        }

        return response()->json([
            'success' => true,
            'message' => 'Pricing package add-on mapping created successfully.',
            'data' => ['id' => $mapping->id],
        ]);
    }

    public function show(Request $request, PricingPackageAddon $pricingPackageAddon)
    {
        $this->ensurePermission('pricing_package_addon_view');
        abort_unless($request->ajax(), 404);

        $pricingPackageAddon->load([
            'pricingPackage.service:id,name',
            'pricingPackage.location:id,name',
            'pricingAddon.media',
        ]);

        return response()->json([
            'html' => view(
                'backoffice.admin.pricing_package_addons.partials.show',
                compact('pricingPackageAddon')
            )->render(),
        ]);
    }

    public function edit(Request $request, PricingPackageAddon $pricingPackageAddon)
    {
        $this->ensurePermission('pricing_package_addon_update');
        abort_unless($request->ajax(), 404);

        $pricingPackageAddon->load(['pricingAddon.media']);
        $packages = $this->packageOptions();
        $addons = $this->addonOptions();

        return response()->json([
            'html' => view(
                'backoffice.admin.pricing_package_addons.partials.form',
                compact('pricingPackageAddon', 'packages', 'addons')
            )->render(),
        ]);
    }

    public function update(Request $request, PricingPackageAddon $pricingPackageAddon)
    {
        $this->ensurePermission('pricing_package_addon_update');

        $validated = $this->validateMapping($request, $pricingPackageAddon);
        $overrideData = $this->decodeOverrideData($validated['override_data'] ?? null);

        try {
            DB::transaction(function () use ($validated, $overrideData, $pricingPackageAddon): void {
                $pricingPackageAddon->update([
                    'pricing_package_id' => (int) $validated['pricing_package_id'],
                    'pricing_addon_id' => (int) $validated['pricing_addon_id'],
                    'override_data' => $overrideData,
                ]);
            });
        } catch (QueryException $exception) {
            if ($this->isDuplicateKeyException($exception)) {
                throw ValidationException::withMessages([
                    'pricing_addon_id' => 'This pricing add-on is already attached to the selected package.',
                ]);
            }

            throw $exception;
        }

        return response()->json([
            'success' => true,
            'message' => 'Pricing package add-on mapping updated successfully.',
        ]);
    }

    public function destroy(PricingPackageAddon $pricingPackageAddon)
    {
        $this->ensurePermission('pricing_package_addon_delete');

        $pricingPackageAddon->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pricing package add-on mapping moved to trash.',
        ]);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['delete', 'restore', 'force_delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $ids = collect($validated['ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

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
        $this->ensurePermission('pricing_package_addon_trash');

        $query = PricingPackageAddon::onlyTrashed()
            ->with([
                'pricingPackage.service:id,name',
                'pricingPackage.location:id,name',
                'pricingAddon.media',
            ])
            ->orderByDesc('deleted_at');

        $this->applyFilters($query, $request);

        $mappings = $query->paginate(15)->withQueryString();
        $packages = $this->packageOptions(true);
        $addons = $this->addonOptions(true);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.pricing_package_addons.partials.table', [
                    'mappings' => $mappings,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        $title = 'Trashed Pricing Package Add-ons';
        $breadcrumb = [
            ['text' => 'CMS', 'url' => null],
            ['text' => 'Pricing Package Add-ons', 'url' => route('admin.pricing_package_addons.index')],
            ['text' => 'Trash', 'url' => null],
        ];

        return view(
            'backoffice.admin.pricing_package_addons.trash',
            compact('mappings', 'packages', 'addons', 'title', 'breadcrumb')
        );
    }

    public function restore(int $pricingPackageAddon)
    {
        $this->ensurePermission('pricing_package_addon_restore');

        $mapping = PricingPackageAddon::onlyTrashed()
            ->with(['pricingPackage', 'pricingAddon'])
            ->findOrFail($pricingPackageAddon);

        $this->ensureRestorable($mapping);
        $mapping->restore();

        return response()->json([
            'success' => true,
            'message' => 'Pricing package add-on mapping restored successfully.',
        ]);
    }

    public function forceDelete(int $pricingPackageAddon)
    {
        $this->ensurePermission('pricing_package_addon_force_delete');

        PricingPackageAddon::onlyTrashed()->findOrFail($pricingPackageAddon)->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Pricing package add-on mapping permanently deleted.',
        ]);
    }

    private function validateMapping(
        Request $request,
        ?PricingPackageAddon $pricingPackageAddon = null
    ): array {
        return $request->validate([
            'pricing_package_id' => [
                'required',
                'integer',
                Rule::exists('pricing_packages', 'id')->whereNull('deleted_at'),
            ],
            'pricing_addon_id' => [
                'required',
                'integer',
                Rule::exists('pricing_addons', 'id')->whereNull('deleted_at'),
                function (string $attribute, mixed $value, \Closure $fail) use ($request, $pricingPackageAddon): void {
                    $packageId = $request->integer('pricing_package_id');

                    if (! $packageId || ! is_numeric($value)) {
                        return;
                    }

                    $query = PricingPackageAddon::withTrashed()
                        ->where('pricing_package_id', $packageId)
                        ->where('pricing_addon_id', (int) $value);

                    if ($pricingPackageAddon) {
                        $query->where('id', '<>', $pricingPackageAddon->getKey());

                        if ($query->exists()) {
                            $fail('This pricing add-on is already linked to the selected package, including a trashed mapping.');
                        }

                        return;
                    }

                    if ($query->whereNull('deleted_at')->exists()) {
                        $fail('This pricing add-on is already attached to the selected package.');
                    }
                },
            ],
            'override_data' => [
                'nullable',
                'string',
                'max:65535',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $json = trim((string) $value);

                    if ($json === '') {
                        return;
                    }

                    try {
                        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
                    } catch (JsonException) {
                        $fail('Override data must contain valid JSON.');
                        return;
                    }

                    if (! is_array($decoded)) {
                        $fail('Override data must be a JSON object.');
                        return;
                    }

                    if ($decoded !== [] && array_is_list($decoded)) {
                        $fail('Override data must be a JSON object, not a JSON list.');
                    }
                },
            ],
        ]);
    }

    private function decodeOverrideData(?string $value): ?array
    {
        $json = trim((string) $value);

        if ($json === '' || $json === '{}') {
            return null;
        }

        return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('pricing_package_id')) {
            $query->where('pricing_package_id', $request->integer('pricing_package_id'));
        }

        if ($request->filled('pricing_addon_id')) {
            $query->where('pricing_addon_id', $request->integer('pricing_addon_id'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function (Builder $builder) use ($search): void {
                $builder
                    ->whereHas('pricingPackage', function (Builder $packageQuery) use ($search): void {
                        $packageQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('pricingAddon', function (Builder $addonQuery) use ($search): void {
                        $addonQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('unit', 'like', "%{$search}%");
                    });
            });
        }
    }

    private function packageOptions(bool $withTrashed = false)
    {
        $query = PricingPackage::query()
            ->with(['service:id,name', 'location:id,name']);

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->orderBy('name')->orderBy('id')->get();
    }

    private function addonOptions(bool $withTrashed = false)
    {
        $query = PricingAddon::query()->with('media');

        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->ordered()->get();
    }

    private function bulkDelete(array $ids): string
    {
        $this->ensurePermission('pricing_package_addon_delete');

        PricingPackageAddon::whereIn('id', $ids)->delete();

        return 'Selected pricing package add-on mappings moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        $this->ensurePermission('pricing_package_addon_restore');

        DB::transaction(function () use ($ids): void {
            $mappings = PricingPackageAddon::onlyTrashed()
                ->with(['pricingPackage', 'pricingAddon'])
                ->whereIn('id', $ids)
                ->get();

            $mappings->each(fn (PricingPackageAddon $mapping) => $this->ensureRestorable($mapping));
            PricingPackageAddon::onlyTrashed()->whereIn('id', $ids)->restore();
        });

        return 'Selected pricing package add-on mappings restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        $this->ensurePermission('pricing_package_addon_force_delete');

        PricingPackageAddon::onlyTrashed()->whereIn('id', $ids)->forceDelete();

        return 'Selected pricing package add-on mappings permanently deleted.';
    }

    private function ensureRestorable(PricingPackageAddon $mapping): void
    {
        if (! $mapping->pricingPackage || $mapping->pricingPackage->trashed()) {
            throw ValidationException::withMessages([
                'pricing_package_id' => 'Restore the related pricing package before restoring this mapping.',
            ]);
        }

        if (! $mapping->pricingAddon || $mapping->pricingAddon->trashed()) {
            throw ValidationException::withMessages([
                'pricing_addon_id' => 'Restore the related pricing add-on before restoring this mapping.',
            ]);
        }
    }

    private function isDuplicateKeyException(QueryException $exception): bool
    {
        return (int) ($exception->errorInfo[1] ?? 0) === 1062;
    }

    private function ensurePermission(string $permission): void
    {
        abort_unless(auth('admin')->user()?->can($permission), 403);
    }
}
