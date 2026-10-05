<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\TrackingProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TrackingController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePermission('tracking_provider_list');

        $query = TrackingProvider::query()->withCount('eventRules');
        $this->applyFilters($query, $request);

        $trackingProviders = $query->ordered()->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.tracking.partials.table', [
                    'trackingProviders' => $trackingProviders,
                    'isTrash' => false,
                ])->render(),
            ]);
        }

        return view('backoffice.admin.tracking.index', [
            'trackingProviders' => $trackingProviders,
            'providers' => TrackingProvider::PROVIDERS,
            'title' => 'Tracking Providers Management',
            'breadcrumb' => [
                ['text' => 'SEO & Tracking', 'url' => null],
                ['text' => 'Tracking Providers', 'url' => route('admin.tracking.index')],
            ],
        ]);
    }

    public function list(Request $request)
    {
        $this->ensurePermission('tracking_provider_list');

        $query = TrackingProvider::query()->select(
            'id',
            'provider',
            'public_identifier',
            'is_enabled',
            'test_mode'
        );

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('provider', 'like', "%{$search}%")
                    ->orWhere('public_identifier', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('enabled_only')) {
            $query->enabled();
        }

        return response()->json([
            'success' => true,
            'data' => $query->ordered()->limit(100)->get(),
        ]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('tracking_provider_create');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.tracking.partials.form', [
                'providers' => TrackingProvider::PROVIDERS,
            ])->render(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('tracking_provider_create');

        $validated = $this->validateProvider($request);
        $config = $this->buildConfig($validated);

        $trackingProvider = DB::transaction(function () use ($validated, $config): TrackingProvider {
            $providerKey = (string) $validated['provider'];

            $existing = TrackingProvider::withTrashed()
                ->where('provider', $providerKey)
                ->lockForUpdate()
                ->first();

            if ($existing && ! $existing->trashed()) {
                throw ValidationException::withMessages([
                    'provider' => 'This tracking provider already exists.',
                ]);
            }

            $record = $existing ?: new TrackingProvider;
            $record->fill($this->attributes($validated, $config, $record));

            if ($existing?->trashed()) {
                $existing->restore();
            }

            $record->save();

            return $record;
        });

        return response()->json([
            'success' => true,
            'message' => 'Tracking provider created successfully.',
            'data' => ['id' => $trackingProvider->id],
        ]);
    }

    public function show(Request $request, TrackingProvider $tracking)
    {
        $this->ensurePermission('tracking_provider_view');
        abort_unless($request->ajax(), 404);

        $tracking->loadCount('eventRules');

        return response()->json([
            'html' => view('backoffice.admin.tracking.partials.show', [
                'trackingProvider' => $tracking,
            ])->render(),
        ]);
    }

    public function edit(Request $request, TrackingProvider $tracking)
    {
        $this->ensurePermission('tracking_provider_update');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.tracking.partials.form', [
                'trackingProvider' => $tracking,
                'providers' => TrackingProvider::PROVIDERS,
            ])->render(),
        ]);
    }

    public function update(Request $request, TrackingProvider $tracking)
    {
        $this->ensurePermission('tracking_provider_update');

        $validated = $this->validateProvider($request, $tracking);
        $config = $this->buildConfig($validated);

        $conflict = TrackingProvider::withTrashed()
            ->where('provider', (string) $validated['provider'])
            ->where('id', '!=', $tracking->id)
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'provider' => 'Another tracking provider already uses this provider key, including Trash.',
            ]);
        }

        $tracking->update($this->attributes($validated, $config, $tracking));

        return response()->json([
            'success' => true,
            'message' => 'Tracking provider updated successfully.',
        ]);
    }

    public function destroy(TrackingProvider $tracking)
    {
        $this->ensurePermission('tracking_provider_delete');

        $tracking->update(['is_enabled' => false]);
        $tracking->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tracking provider moved to trash.',
        ]);
    }

    public function toggle(TrackingProvider $tracking)
    {
        $this->ensurePermission('tracking_provider_toggle');

        $tracking->update(['is_enabled' => ! $tracking->is_enabled]);

        return response()->json([
            'success' => true,
            'message' => $tracking->fresh()->is_enabled
                ? 'Tracking provider enabled.'
                : 'Tracking provider disabled.',
        ]);
    }

    public function test(TrackingProvider $tracking)
    {
        $this->ensurePermission('tracking_test_event');

        $errors = [];

        if ($tracking->provider === 'meta_pixel' && $tracking->metaPixels() === []) {
            $errors[] = 'Add at least one Meta Pixel ID.';
        }

        if ($tracking->provider !== 'meta_pixel' && ! $tracking->public_identifier) {
            $errors[] = 'Add a public identifier before testing this provider.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages([
                'tracking' => implode(' ', $errors),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tracking configuration is structurally valid. No third-party test request was sent.',
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
            'enable', 'disable' => 'tracking_provider_toggle',
            'delete' => 'tracking_provider_delete',
            'restore' => 'tracking_provider_restore',
            'force_delete' => 'tracking_provider_force_delete',
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
        $this->ensurePermission('tracking_provider_trash');

        $query = TrackingProvider::onlyTrashed()->withCount('eventRules')->latest('deleted_at');
        $this->applyFilters($query, $request);

        $trackingProviders = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.tracking.partials.table', [
                    'trackingProviders' => $trackingProviders,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        return view('backoffice.admin.tracking.trash', [
            'trackingProviders' => $trackingProviders,
            'providers' => TrackingProvider::PROVIDERS,
            'title' => 'Trashed Tracking Providers',
            'breadcrumb' => [
                ['text' => 'SEO & Tracking', 'url' => null],
                ['text' => 'Tracking Providers', 'url' => route('admin.tracking.index')],
                ['text' => 'Trash', 'url' => null],
            ],
        ]);
    }

    public function restore(int $tracking)
    {
        $this->ensurePermission('tracking_provider_restore');

        $record = TrackingProvider::onlyTrashed()->findOrFail($tracking);

        if (TrackingProvider::query()->where('provider', $record->provider)->exists()) {
            throw ValidationException::withMessages([
                'tracking' => 'An active record already exists for this tracking provider.',
            ]);
        }

        $record->forceFill(['is_enabled' => false]);
        $record->restore();
        $record->save();

        return response()->json([
            'success' => true,
            'message' => 'Tracking provider restored as disabled.',
        ]);
    }

    public function forceDelete(int $tracking)
    {
        $this->ensurePermission('tracking_provider_force_delete');

        $record = TrackingProvider::onlyTrashed()->findOrFail($tracking);
        $this->ensureForceDeletable([$record->id]);
        $record->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Tracking provider permanently deleted.',
        ]);
    }

    private function validateProvider(Request $request, ?TrackingProvider $trackingProvider = null): array
    {
        return $request->validate([
            'provider' => ['required', 'string', Rule::in(array_keys(TrackingProvider::PROVIDERS))],
            'public_identifier' => [
                'nullable',
                'string',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                    $value = trim((string) $value);
                    if ($value === '') {
                        return;
                    }

                    $provider = (string) $request->input('provider');

                    $valid = match ($provider) {
                        'meta_pixel', 'meta_capi' => (bool) preg_match('/^[0-9]{5,30}$/', $value),
                        'ga4' => (bool) preg_match('/^G-[A-Z0-9]{4,20}$/i', $value),
                        'gtm' => (bool) preg_match('/^GTM-[A-Z0-9]{4,20}$/i', $value),
                        'tiktok' => (bool) preg_match('/^[A-Z0-9_-]{5,64}$/i', $value),
                        default => false,
                    };

                    if (! $valid) {
                        $fail('The public identifier format does not match the selected provider.');
                    }
                },
            ],
            'secret' => ['nullable', 'string', 'max:1000'],
            'clear_secret' => ['nullable', 'boolean'],
            'config_json' => ['nullable', 'string', 'max:60000'],
            'is_enabled' => ['required', 'boolean'],
            'test_mode' => ['required', 'boolean'],

            'meta_pixels' => ['nullable', 'array', 'max:25'],
            'meta_pixels.*.label' => ['nullable', 'string', 'max:120'],
            'meta_pixels.*.pixel_id' => ['nullable', 'string', 'max:30', 'regex:/^[0-9]{5,30}$/'],
            'meta_pixels.*.script_snippet' => ['nullable', 'string', 'max:20000'],
            'meta_pixels.*.is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function buildConfig(array $validated): ?array
    {
        $config = $this->decodeJsonObject($validated['config_json'] ?? null, 'config_json') ?? [];

        if (($validated['provider'] ?? null) !== 'meta_pixel') {
            return $config !== [] ? $config : null;
        }

        $pixels = [];
        $seenPixelIds = [];

        foreach (($validated['meta_pixels'] ?? []) as $index => $row) {
            $label = trim((string) ($row['label'] ?? ''));
            $pixelId = trim((string) ($row['pixel_id'] ?? ''));
            $snippet = trim((string) ($row['script_snippet'] ?? ''));

            // Ignore a completely empty repeater row.
            if ($pixelId === '' && $snippet === '' && $label === '') {
                continue;
            }

            $snippetPixelIds = $snippet !== ''
                ? $this->extractMetaPixelIds($snippet)
                : [];

            // Script-only rows are supported: derive the Pixel ID from fbq('init', '...').
            if ($pixelId === '' && $snippetPixelIds !== []) {
                $pixelId = $snippetPixelIds[0];
            }

            if (! preg_match('/^[0-9]{5,30}$/', $pixelId)) {
                throw ValidationException::withMessages([
                    "meta_pixels.{$index}.pixel_id" => "Enter a valid numeric Meta Pixel ID, or paste a standard Meta Pixel snippet that contains fbq(\'init\', \'PIXEL_ID\').",
                ]);
            }

            if ($snippet !== '') {
                if ($snippetPixelIds === []) {
                    throw ValidationException::withMessages([
                        "meta_pixels.{$index}.script_snippet" => "The pasted script does not contain a valid Meta Pixel fbq(\'init\', \'PIXEL_ID\') call.",
                    ]);
                }

                if (! in_array($pixelId, $snippetPixelIds, true)) {
                    throw ValidationException::withMessages([
                        "meta_pixels.{$index}.script_snippet" => "The pasted Meta Pixel script initializes Pixel ID {$snippetPixelIds[0]}, but this row contains {$pixelId}. Make both IDs the same.",
                    ]);
                }
            }

            if (isset($seenPixelIds[$pixelId])) {
                throw ValidationException::withMessages([
                    "meta_pixels.{$index}.pixel_id" => 'This Meta Pixel ID is already added in another row.',
                ]);
            }

            $seenPixelIds[$pixelId] = true;

            $pixels[] = [
                'label' => $label !== '' ? $label : 'Meta Pixel '.(count($pixels) + 1),
                'pixel_id' => $pixelId,
                // Stored encrypted for admin reference only; never raw-rendered publicly.
                'script_snippet' => $snippet !== '' ? $snippet : null,
                'is_active' => (bool) ($row['is_active'] ?? true),
            ];
        }

        $config['meta_pixels'] = array_values($pixels);
        $config['raw_script_execution'] = false;

        return $config;
    }

    private function attributes(array $validated, ?array $config, TrackingProvider $record): array
    {
        $attributes = [
            'provider' => (string) $validated['provider'],
            'public_identifier' => $this->nullableTrim($validated['public_identifier'] ?? null),
            'config' => $config,
            'is_enabled' => (bool) $validated['is_enabled'],
            'test_mode' => (bool) $validated['test_mode'],
        ];

        if (($validated['provider'] ?? null) === 'meta_pixel' && empty($attributes['public_identifier'])) {
            $firstActive = collect(data_get($config, 'meta_pixels', []))
                ->first(fn ($pixel) => (bool) ($pixel['is_active'] ?? true) && ! empty($pixel['pixel_id']));

            if ($firstActive) {
                $attributes['public_identifier'] = (string) $firstActive['pixel_id'];
            }
        }

        if ((bool) ($validated['clear_secret'] ?? false)) {
            $attributes['secret'] = null;
        } elseif (! empty($validated['secret'])) {
            $attributes['secret'] = (string) $validated['secret'];
        } elseif (! $record->exists) {
            $attributes['secret'] = null;
        }

        return $attributes;
    }

    private function extractMetaPixelIds(string $snippet): array
    {
        preg_match_all(
            '/fbq\s*\(\s*[\'"]init[\'"]\s*,\s*[\'"]([0-9]{5,30})[\'"]/i',
            $snippet,
            $matches
        );

        return collect($matches[1] ?? [])
            ->map(fn ($id) => trim((string) $id))
            ->filter(fn ($id) => (bool) preg_match('/^[0-9]{5,30}$/', $id))
            ->unique()
            ->values()
            ->all();
    }

    private function extractMetaPixelId(string $snippet): ?string
    {
        return $this->extractMetaPixelIds($snippet)[0] ?? null;
    }

    private function snippetContainsPixelId(string $snippet, string $pixelId): bool
    {
        return in_array($pixelId, $this->extractMetaPixelIds($snippet), true);
    }

    private function decodeJsonObject(mixed $value, string $field): ?array
    {
        $value = trim((string) ($value ?? ''));

        if ($value === '') {
            return null;
        }

        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            throw ValidationException::withMessages([$field => 'The JSON is invalid.']);
        }

        if (! is_array($decoded) || array_is_list($decoded)) {
            throw ValidationException::withMessages([$field => 'The JSON must be an object.']);
        }

        return $decoded;
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('provider', 'like', "%{$search}%")
                    ->orWhere('public_identifier', 'like', "%{$search}%");
            });
        }

        if ($request->filled('provider') && array_key_exists((string) $request->provider, TrackingProvider::PROVIDERS)) {
            $query->where('provider', (string) $request->provider);
        }

        if ($request->filled('status')) {
            $query->where('is_enabled', $request->status === 'enabled');
        }

        if ($request->filled('mode')) {
            $query->where('test_mode', $request->mode === 'test');
        }
    }

    private function ensureForceDeletable(array $ids): void
    {
        if ($ids === []) {
            return;
        }

        if (\App\Models\TrackingEventRule::withTrashed()->whereIn('tracking_provider_id', $ids)->exists()) {
            throw ValidationException::withMessages([
                'tracking' => 'One or more providers still have tracking event rules. Permanently delete those rules first.',
            ]);
        }
    }

    private function bulkStatus(array $ids, bool $enabled): string
    {
        TrackingProvider::query()->whereIn('id', $ids)->update(['is_enabled' => $enabled]);
        return $enabled ? 'Selected tracking providers enabled.' : 'Selected tracking providers disabled.';
    }

    private function bulkDelete(array $ids): string
    {
        TrackingProvider::query()->whereIn('id', $ids)->update(['is_enabled' => false]);
        TrackingProvider::query()->whereIn('id', $ids)->delete();
        return 'Selected tracking providers moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        $records = TrackingProvider::onlyTrashed()->whereIn('id', $ids)->get();

        foreach ($records as $record) {
            if (TrackingProvider::query()->where('provider', $record->provider)->exists()) {
                throw ValidationException::withMessages([
                    'tracking' => "Cannot restore {$record->label()}; an active provider record already exists.",
                ]);
            }
        }

        $records->each(function (TrackingProvider $record): void {
            $record->forceFill(['is_enabled' => false]);
            $record->restore();
            $record->save();
        });

        return 'Selected tracking providers restored as disabled.';
    }

    private function bulkForceDelete(array $ids): string
    {
        $records = TrackingProvider::onlyTrashed()->whereIn('id', $ids)->get();
        $this->ensureForceDeletable($records->pluck('id')->all());
        $records->each->forceDelete();

        return 'Selected tracking providers permanently deleted.';
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));
        return $value !== '' ? $value : null;
    }

    private function ensurePermission(string $permission): void
    {
        abort_unless(auth('admin')->user()?->can($permission), 403);
    }
}
