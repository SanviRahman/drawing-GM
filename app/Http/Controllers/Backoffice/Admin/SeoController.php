<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\SeoMeta;
use App\Services\Seo\SeoMetaMediaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SeoController extends Controller
{
    public function __construct(
        private readonly SeoMetaMediaService $mediaService,
    ) {}

    public function index(Request $request)
    {
        $this->ensurePermission('seo_meta_list');

        $query = SeoMeta::query()->with(['seoable', 'media']);
        $this->applyFilters($query, $request);

        $seoMetas = $query->latest('id')->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.seo.partials.table', [
                    'seoMetas' => $seoMetas,
                    'isTrash' => false,
                ])->render(),
            ]);
        }

        return view('backoffice.admin.seo.index', [
            'seoMetas' => $seoMetas,
            'typeLabels' => SeoMeta::SEOABLE_LABELS,
            'title' => 'SEO Metadata Management',
            'breadcrumb' => [
                ['text' => 'SEO & Tracking', 'url' => null],
                ['text' => 'SEO Metadata', 'url' => route('admin.seo.index')],
            ],
        ]);
    }

    public function list(Request $request)
    {
        $this->ensurePermission('seo_meta_list');

        $query = SeoMeta::query()->select('id', 'seoable_type', 'seoable_id', 'meta_title', 'canonical_url');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('meta_title', 'like', "%{$search}%")
                    ->orWhere('canonical_url', 'like', "%{$search}%")
                    ->orWhere('og_title', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query->latest('id')->limit(100)->get(),
        ]);
    }

    public function targets(Request $request)
    {
        $this->ensureAnyPermission(['seo_meta_create', 'seo_meta_update']);

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(array_keys(SeoMeta::SEOABLE_TYPES))],
            'search' => ['nullable', 'string', 'max:190'],
            'selected' => ['nullable', 'integer', 'min:1'],
        ]);

        $class = SeoMeta::typeClass((string) $validated['type']);
        abort_unless($class !== null, 422);

        $model = new $class;
        $labelColumn = in_array($validated['type'], ['page', 'post'], true) ? 'title' : 'name';

        $query = $class::query()->select('id', $labelColumn);

        if (! empty($validated['search'])) {
            $query->where($labelColumn, 'like', '%' . trim((string) $validated['search']) . '%');
        }

        if (! empty($validated['selected'])) {
            $query->orWhereKey((int) $validated['selected']);
        }

        $items = $query
            ->orderBy($labelColumn)
            ->limit(100)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->getKey(),
                'text' => (string) $item->{$labelColumn},
            ])
            ->values();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('seo_meta_create');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.seo.partials.form', $this->formOptions())->render(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('seo_meta_create');

        $validated = $this->validateSeoMeta($request);
        $owner = $this->resolveOwner($validated['seoable_type_key'], (int) $validated['seoable_id']);

        $seoMeta = DB::transaction(function () use ($request, $validated, $owner): SeoMeta {
            $morphClass = $owner->getMorphClass();

            $existing = SeoMeta::withTrashed()
                ->where('seoable_type', $morphClass)
                ->where('seoable_id', $owner->getKey())
                ->lockForUpdate()
                ->first();

            if ($existing && ! $existing->trashed()) {
                throw ValidationException::withMessages([
                    'seoable_id' => 'SEO metadata already exists for the selected content item.',
                ]);
            }

            $record = $existing ?: new SeoMeta;

            $record->fill($this->attributes($validated, $morphClass, (int) $owner->getKey()));

            if ($existing?->trashed()) {
                $existing->restore();
            }

            $record->save();
            $this->mediaService->syncFromRequest($request, $record);

            return $record;
        });

        return response()->json([
            'success' => true,
            'message' => 'SEO metadata saved successfully.',
            'data' => ['id' => $seoMeta->id],
        ]);
    }

    public function show(Request $request, SeoMeta $seo)
    {
        $this->ensurePermission('seo_meta_view');
        abort_unless($request->ajax(), 404);

        $seo->load('seoable', 'media');

        return response()->json([
            'html' => view('backoffice.admin.seo.partials.show', ['seoMeta' => $seo])->render(),
        ]);
    }

    public function edit(Request $request, SeoMeta $seo)
    {
        $this->ensurePermission('seo_meta_update');
        abort_unless($request->ajax(), 404);

        $seo->load('seoable', 'media');

        return response()->json([
            'html' => view('backoffice.admin.seo.partials.form', array_merge(
                $this->formOptions(),
                ['seoMeta' => $seo]
            ))->render(),
        ]);
    }

    public function update(Request $request, SeoMeta $seo)
    {
        $this->ensurePermission('seo_meta_update');

        $validated = $this->validateSeoMeta($request, $seo);
        $owner = $this->resolveOwner($validated['seoable_type_key'], (int) $validated['seoable_id']);
        $morphClass = $owner->getMorphClass();

        $duplicate = SeoMeta::withTrashed()
            ->where('seoable_type', $morphClass)
            ->where('seoable_id', $owner->getKey())
            ->where('id', '!=', $seo->id)
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'seoable_id' => 'SEO metadata already exists for the selected content item.',
            ]);
        }

        DB::transaction(function () use ($request, $validated, $owner, $morphClass, $seo): void {
            $seo->update($this->attributes($validated, $morphClass, (int) $owner->getKey()));
            $this->mediaService->syncFromRequest($request, $seo);
        });

        return response()->json([
            'success' => true,
            'message' => 'SEO metadata updated successfully.',
        ]);
    }

    public function destroy(SeoMeta $seo)
    {
        $this->ensurePermission('seo_meta_delete');
        $seo->delete();

        return response()->json([
            'success' => true,
            'message' => 'SEO metadata moved to trash.',
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
            'delete' => 'seo_meta_delete',
            'restore' => 'seo_meta_restore',
            'force_delete' => 'seo_meta_force_delete',
        };

        $this->ensurePermission($permission);

        $ids = collect($validated['ids'])->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();

        $message = match ($validated['action']) {
            'delete' => $this->bulkDelete($ids),
            'restore' => $this->bulkRestore($ids),
            'force_delete' => $this->bulkForceDelete($ids),
        };

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function trash(Request $request)
    {
        $this->ensurePermission('seo_meta_trash');

        $query = SeoMeta::onlyTrashed()->with(['seoable', 'media'])->latest('deleted_at');
        $this->applyFilters($query, $request);

        $seoMetas = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.seo.partials.table', [
                    'seoMetas' => $seoMetas,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        return view('backoffice.admin.seo.trash', [
            'seoMetas' => $seoMetas,
            'typeLabels' => SeoMeta::SEOABLE_LABELS,
            'title' => 'Trashed SEO Metadata',
            'breadcrumb' => [
                ['text' => 'SEO & Tracking', 'url' => null],
                ['text' => 'SEO Metadata', 'url' => route('admin.seo.index')],
                ['text' => 'Trash', 'url' => null],
            ],
        ]);
    }

    public function restore(int $seo)
    {
        $this->ensurePermission('seo_meta_restore');

        $record = SeoMeta::onlyTrashed()->findOrFail($seo);

        $owner = $record->seoable;

        if (! $owner || (method_exists($owner, 'trashed') && $owner->trashed())) {
            throw ValidationException::withMessages([
                'seo' => 'Restore the related content item before restoring this SEO metadata.',
            ]);
        }

        $conflict = SeoMeta::query()
            ->where('seoable_type', $record->seoable_type)
            ->where('seoable_id', $record->seoable_id)
            ->exists();

        if ($conflict) {
            throw ValidationException::withMessages([
                'seo' => 'An active SEO metadata record already exists for this content item.',
            ]);
        }

        $record->restore();

        return response()->json(['success' => true, 'message' => 'SEO metadata restored successfully.']);
    }

    public function forceDelete(int $seo)
    {
        $this->ensurePermission('seo_meta_force_delete');

        $record = SeoMeta::onlyTrashed()->findOrFail($seo);

        DB::transaction(function () use ($record): void {
            $this->mediaService->purgeAll($record);
            $record->forceDelete();
        });

        return response()->json(['success' => true, 'message' => 'SEO metadata permanently deleted.']);
    }

    private function validateSeoMeta(Request $request, ?SeoMeta $seoMeta = null): array
    {
        return $request->validate([
            'seoable_type_key' => ['required', 'string', Rule::in(array_keys(SeoMeta::SEOABLE_TYPES))],
            'seoable_id' => ['required', 'integer', 'min:1'],
            'meta_title' => ['nullable', 'string', 'max:190'],
            'meta_description' => ['nullable', 'string', 'max:10000'],
            'canonical_url' => [
                'nullable',
                'string',
                'max:2048',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $value = trim((string) $value);
                    if ($value !== '' && ! filter_var($value, FILTER_VALIDATE_URL)) {
                        $fail('The canonical URL must be a valid absolute URL.');
                    }
                    if ($value !== '' && ! Str::startsWith(strtolower($value), ['http://', 'https://'])) {
                        $fail('The canonical URL must use HTTP or HTTPS.');
                    }
                },
            ],
            'robots' => ['required', 'string', Rule::in(array_keys(SeoMeta::ROBOTS))],
            'og_title' => ['nullable', 'string', 'max:190'],
            'og_description' => ['nullable', 'string', 'max:10000'],
            'schema_overrides' => ['nullable', 'string', 'max:60000'],
            'include_in_sitemap' => ['required', 'boolean'],
            'sitemap_priority' => ['nullable', 'numeric', 'between:0,1'],
            'sitemap_changefreq' => ['nullable', 'string', Rule::in(array_keys(SeoMeta::CHANGEFREQ))],
            'social_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'social_image_media_id' => [
                'nullable',
                'integer',
                Rule::exists('media', 'id')->whereNull('deleted_at'),
                $this->reusableImageRule(),
            ],
            'social_image_remove' => ['nullable', 'boolean'],
        ]);
    }

    private function attributes(array $validated, string $morphClass, int $ownerId): array
    {
        return [
            'seoable_type' => $morphClass,
            'seoable_id' => $ownerId,
            'meta_title' => $this->nullableTrim($validated['meta_title'] ?? null),
            'meta_description' => $this->sanitizeRichText($validated['meta_description'] ?? null),
            'canonical_url' => $this->nullableTrim($validated['canonical_url'] ?? null),
            'robots' => (string) $validated['robots'],
            'og_title' => $this->nullableTrim($validated['og_title'] ?? null),
            'og_description' => $this->sanitizeRichText($validated['og_description'] ?? null),
            'schema_overrides' => $this->decodeJsonObject($validated['schema_overrides'] ?? null, 'schema_overrides'),
            'include_in_sitemap' => (bool) $validated['include_in_sitemap'],
            'sitemap_priority' => $validated['sitemap_priority'] !== null && $validated['sitemap_priority'] !== ''
                ? number_format((float) $validated['sitemap_priority'], 1, '.', '')
                : null,
            'sitemap_changefreq' => $this->nullableTrim($validated['sitemap_changefreq'] ?? null),
        ];
    }

    private function resolveOwner(string $typeKey, int $id)
    {
        $class = SeoMeta::typeClass($typeKey);

        if (! $class) {
            throw ValidationException::withMessages(['seoable_type_key' => 'Unsupported SEO content type.']);
        }

        $owner = $class::query()->find($id);

        if (! $owner) {
            throw ValidationException::withMessages(['seoable_id' => 'The selected content item is unavailable or in Trash.']);
        }

        return $owner;
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('meta_title', 'like', "%{$search}%")
                    ->orWhere('canonical_url', 'like', "%{$search}%")
                    ->orWhere('og_title', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type') && ($class = SeoMeta::typeClass((string) $request->type))) {
            $morphClass = (new $class)->getMorphClass();
            $query->where('seoable_type', $morphClass);
        }

        if ($request->filled('sitemap')) {
            $query->where('include_in_sitemap', $request->sitemap === 'yes');
        }

        if ($request->filled('robots') && array_key_exists((string) $request->robots, SeoMeta::ROBOTS)) {
            $query->where('robots', (string) $request->robots);
        }
    }

    private function formOptions(): array
    {
        return [
            'typeLabels' => SeoMeta::SEOABLE_LABELS,
            'robotsOptions' => SeoMeta::ROBOTS,
            'changefreqOptions' => SeoMeta::CHANGEFREQ,
        ];
    }

    private function bulkDelete(array $ids): string
    {
        SeoMeta::query()->whereIn('id', $ids)->delete();
        return 'Selected SEO metadata moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        $records = SeoMeta::onlyTrashed()->whereIn('id', $ids)->get();

        foreach ($records as $record) {
            $owner = $record->seoable;

            if (! $owner || (method_exists($owner, 'trashed') && $owner->trashed())) {
                throw ValidationException::withMessages([
                    'seo' => "SEO metadata #{$record->id} cannot be restored because its content owner is unavailable or in Trash.",
                ]);
            }

            $conflict = SeoMeta::query()
                ->where('seoable_type', $record->seoable_type)
                ->where('seoable_id', $record->seoable_id)
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([
                    'seo' => "SEO metadata #{$record->id} conflicts with an active record.",
                ]);
            }
        }

        $records->each->restore();

        return 'Selected SEO metadata restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        DB::transaction(function () use ($ids): void {
            SeoMeta::onlyTrashed()->whereIn('id', $ids)->get()->each(function (SeoMeta $record): void {
                $this->mediaService->purgeAll($record);
                $record->forceDelete();
            });
        });

        return 'Selected SEO metadata permanently deleted.';
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

    private function reusableImageRule(): \Closure
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

    private function sanitizeRichText(mixed $value): ?string
    {
        $html = trim((string) ($value ?? ''));

        if ($html === '') {
            return null;
        }

        $allowedTags = '<p><br><strong><b><em><i><u><s><ul><ol><li><blockquote><a><span>';
        $html = strip_tags($html, $allowedTags);
        $html = preg_replace('/\s(?:on\w+|style|srcdoc|formaction)\s*=\s*(["\']).*?\1/isu', '', $html) ?? $html;
        $html = preg_replace('/\s(?:on\w+|style|srcdoc|formaction)\s*=\s*[^\s>]+/iu', '', $html) ?? $html;

        return trim($html) !== '' ? trim($html) : null;
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

    private function ensureAnyPermission(array $permissions): void
    {
        $admin = auth('admin')->user();
        abort_unless($admin && collect($permissions)->contains(fn ($permission) => $admin->can($permission)), 403);
    }
}
