<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use App\Services\LocationMediaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LocationController extends Controller
{
    public function __construct(private readonly LocationMediaService $locationMediaService) {}

    public function index(Request $request)
    {
        $this->ensurePermission('location_list');

        $query = Location::query()->with('media');
        $this->applyFilters($query, $request);
        $locations = $query->ordered()->paginate(15)->withQueryString();
        $regions = Location::query()->whereNotNull('region')->where('region', '!=', '')->distinct()->orderBy('region')->pluck('region');

        if ($request->ajax()) {
            return response()->json(['html' => view('backoffice.admin.locations.partials.table', compact('locations'))->render()]);
        }

        $title = 'Locations Management';
        $breadcrumb = [['text' => 'CMS', 'url' => null], ['text' => 'Locations', 'url' => route('admin.locations.index')]];

        return view('backoffice.admin.locations.index', compact('locations', 'regions', 'title', 'breadcrumb'));
    }

    public function list(Request $request)
    {
        $this->ensurePermission('location_list');

        $query = Location::query()->select('id', 'name', 'slug', 'region', 'status', 'sort_order');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(fn (Builder $builder) => $builder->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")->orWhere('region', 'like', "%{$search}%"));
        }

        if ($request->boolean('published_only')) {
            $query->published();
        }

        return response()->json(['success' => true, 'data' => $query->ordered()->limit(100)->get()]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('location_create');
        abort_unless($request->ajax(), 404);

        return response()->json(['html' => view('backoffice.admin.locations.partials.form')->render()]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('location_create');
        $validated = $this->validateLocation($request);

        if (! array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = ((int) Location::query()->max('sort_order')) + 10;
        }

        $location = DB::transaction(function () use ($request, $validated): Location {
            $location = Location::create($this->locationAttributes($validated));
            $this->locationMediaService->syncFromRequest($request, $location);

            return $location;
        });

        return response()->json(['success' => true, 'message' => 'Location created successfully.', 'data' => ['id' => $location->id]]);
    }

    public function show(Request $request, Location $location)
    {
        $this->ensurePermission('location_view');
        abort_unless($request->ajax(), 404);

        $location->load('media');

        return response()->json(['html' => view('backoffice.admin.locations.partials.show', compact('location'))->render()]);
    }

    public function edit(Request $request, Location $location)
    {
        $this->ensurePermission('location_update');
        abort_unless($request->ajax(), 404);

        $location->load('media');

        return response()->json(['html' => view('backoffice.admin.locations.partials.form', compact('location'))->render()]);
    }

    public function update(Request $request, Location $location)
    {
        $this->ensurePermission('location_update');
        $validated = $this->validateLocation($request, $location);

        if (! array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = $location->sort_order;
        }

        DB::transaction(function () use ($request, $validated, $location): void {
            $location->update($this->locationAttributes($validated, $location));
            $this->locationMediaService->syncFromRequest($request, $location);
        });

        return response()->json(['success' => true, 'message' => 'Location updated successfully.']);
    }

    public function destroy(Location $location)
    {
        $this->ensurePermission('location_delete');
        $location->delete();

        return response()->json(['success' => true, 'message' => 'Location moved to trash.']);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['publish', 'unpublish', 'archive', 'delete', 'restore', 'force_delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $permission = match ($validated['action']) {
            'publish' => 'location_publish',
            'unpublish' => 'location_unpublish',
            'archive' => 'location_update',
            'delete' => 'location_delete',
            'restore' => 'location_restore',
            'force_delete' => 'location_force_delete',
        };

        $this->ensurePermission($permission);
        $ids = collect($validated['ids'])->map(fn ($id) => (int) $id)->unique()->values()->all();

        $message = match ($validated['action']) {
            'publish' => $this->bulkPublish($ids),
            'unpublish' => $this->bulkUnpublish($ids),
            'archive' => $this->bulkArchive($ids),
            'delete' => $this->bulkDelete($ids),
            'restore' => $this->bulkRestore($ids),
            'force_delete' => $this->bulkForceDelete($ids),
        };

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function trash(Request $request)
    {
        $this->ensurePermission('location_trash');

        $query = Location::onlyTrashed()->with('media');
        $this->applyFilters($query, $request);
        $locations = $query->orderByDesc('deleted_at')->paginate(15)->withQueryString();
        $regions = Location::withTrashed()->whereNotNull('region')->where('region', '!=', '')->distinct()->orderBy('region')->pluck('region');

        if ($request->ajax()) {
            return response()->json(['html' => view('backoffice.admin.locations.partials.table', ['locations' => $locations, 'isTrash' => true])->render()]);
        }

        $title = 'Trashed Locations';
        $breadcrumb = [['text' => 'CMS', 'url' => null], ['text' => 'Locations', 'url' => route('admin.locations.index')], ['text' => 'Trash', 'url' => null]];

        return view('backoffice.admin.locations.trash', compact('locations', 'regions', 'title', 'breadcrumb'));
    }

    public function restore(int $location)
    {
        $this->ensurePermission('location_restore');
        Location::onlyTrashed()->findOrFail($location)->restore();

        return response()->json(['success' => true, 'message' => 'Location restored successfully.']);
    }

    public function forceDelete(int $location)
    {
        $this->ensurePermission('location_force_delete');

        DB::transaction(function () use ($location): void {
            $record = Location::onlyTrashed()->findOrFail($location);
            $this->ensureForceDeletable($record);
            $this->locationMediaService->purgeAll($record);
            $record->forceDelete();
        });

        return response()->json(['success' => true, 'message' => 'Location permanently deleted.']);
    }

    public function publish(Location $location)
    {
        $this->ensurePermission('location_publish');
        $location->update(['status' => 'published', 'published_at' => $location->published_at ?? now()]);

        return response()->json(['success' => true, 'message' => 'Location published successfully.']);
    }

    public function unpublish(Location $location)
    {
        $this->ensurePermission('location_unpublish');
        $location->update(['status' => 'draft', 'published_at' => null]);

        return response()->json(['success' => true, 'message' => 'Location moved back to draft.']);
    }

    public function duplicate(Location $location)
    {
        $this->ensurePermission('location_duplicate');

        $copy = DB::transaction(function () use ($location): Location {
            $copy = Location::create([
                'name' => Str::limit($location->name . ' Copy', 190, ''),
                'slug' => $this->uniqueCopySlug($location->slug),
                'region' => $location->region,
                'postal_codes' => $location->postal_codes ?? [],
                'summary' => $location->summary,
                'content' => $location->content,
                'hero_config' => $location->hero_config ?? [],
                'status' => 'draft',
                'sort_order' => ((int) Location::query()->max('sort_order')) + 10,
                'published_at' => null,
            ]);

            $this->locationMediaService->duplicateMedia($location, $copy);

            return $copy;
        });

        return response()->json(['success' => true, 'message' => 'Location duplicated as draft.', 'data' => ['id' => $copy->id]]);
    }

    private function validateLocation(Request $request, ?Location $location = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'slug' => ['required', 'string', 'max:190', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('locations', 'slug')->ignore($location?->id)],
            'region' => ['nullable', 'string', 'max:120'],
            'postal_codes' => ['nullable', 'string', 'max:10000'],
            'summary' => ['nullable', 'string', 'max:60000'],
            'content' => ['nullable', 'string', 'max:500000'],
            'status' => ['required', Rule::in(array_keys(Location::STATUSES))],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'published_at' => ['nullable', 'date'],
            'hero_heading' => ['nullable', 'string', 'max:190'],
            'hero_overlay' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'hero_focal_position' => ['nullable', 'string', 'max:50'],
            'hero_cta_label' => ['nullable', 'string', 'max:100'],
            'hero_cta_url' => ['nullable', 'string', 'max:500', function (string $attribute, mixed $value, \Closure $fail): void {
                $value = strtolower(trim((string) $value));

                if (Str::startsWith($value, ['javascript:', 'data:', 'vbscript:'])) {
                    $fail('The hero CTA URL contains a disallowed scheme.');
                }
            }],
            'hero_desktop' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'hero_mobile' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'gallery' => ['nullable', 'array', 'max:20'],
            'gallery.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'hero_desktop_media_ids' => ['nullable', 'string', 'max:200'],
            'hero_mobile_media_ids' => ['nullable', 'string', 'max:200'],
            'gallery_media_ids' => ['nullable', 'string', 'max:1000'],
            'hero_desktop_clear' => ['nullable', 'boolean'],
            'hero_mobile_clear' => ['nullable', 'boolean'],
            'gallery_clear' => ['nullable', 'boolean'],
            'hero_desktop_remove_ids' => ['nullable', 'string', 'max:500'],
            'hero_mobile_remove_ids' => ['nullable', 'string', 'max:500'],
            'gallery_remove_ids' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function locationAttributes(array $validated, ?Location $location = null): array
    {
        $status = $validated['status'];
        $publishedAt = $validated['published_at'] ?? null;

        if ($status === 'published' && ! $publishedAt) {
            $publishedAt = $location?->published_at ?? now();
        }

        if ($status === 'draft') {
            $publishedAt = null;
        }

        return [
            'name' => trim($validated['name']),
            'slug' => strtolower(trim($validated['slug'])),
            'region' => filled($validated['region'] ?? null) ? trim((string) $validated['region']) : null,
            'postal_codes' => $this->parsePostalCodes($validated['postal_codes'] ?? null),
            'summary' => $this->sanitizeRichText($validated['summary'] ?? null),
            'content' => $this->sanitizeRichText($validated['content'] ?? null),
            'hero_config' => $this->heroConfig($validated),
            'status' => $status,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'published_at' => $publishedAt,
        ];
    }

    private function parsePostalCodes(mixed $value): ?array
    {
        $raw = trim((string) ($value ?? ''));

        if ($raw === '') {
            return null;
        }

        $codes = collect(preg_split('/[\r\n,;]+/', $raw) ?: [])
            ->map(fn ($code) => trim((string) $code))
            ->filter()
            ->unique()
            ->values();

        if ($codes->count() > 500) {
            throw ValidationException::withMessages(['postal_codes' => 'A maximum of 500 postal codes may be stored for one location.']);
        }

        foreach ($codes as $code) {
            if (mb_strlen($code) > 30) {
                throw ValidationException::withMessages(['postal_codes' => 'Each postal code must be 30 characters or fewer.']);
            }
        }

        return $codes->isEmpty() ? null : $codes->all();
    }

    private function heroConfig(array $validated): array
    {
        return [
            'heading' => filled($validated['hero_heading'] ?? null) ? trim((string) $validated['hero_heading']) : null,
            'overlay' => isset($validated['hero_overlay']) && $validated['hero_overlay'] !== '' ? (float) $validated['hero_overlay'] : null,
            'focal_position' => filled($validated['hero_focal_position'] ?? null) ? trim((string) $validated['hero_focal_position']) : null,
            'cta' => [
                'label' => filled($validated['hero_cta_label'] ?? null) ? trim((string) $validated['hero_cta_label']) : null,
                'url' => filled($validated['hero_cta_url'] ?? null) ? trim((string) $validated['hero_cta_url']) : null,
            ],
            'slider_mode' => false,
        ];
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

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(fn (Builder $builder) => $builder->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")->orWhere('region', 'like', "%{$search}%")->orWhere('summary', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->status);
        }

        if ($request->filled('region')) {
            $query->where('region', (string) $request->region);
        }
    }

    private function uniqueCopySlug(string $baseSlug): string
    {
        $base = Str::limit(Str::slug($baseSlug . '-copy'), 170, '');
        $slug = $base;
        $counter = 2;

        while (Location::withTrashed()->where('slug', $slug)->exists()) {
            $suffix = '-' . $counter++;
            $slug = Str::limit($base, 190 - strlen($suffix), '') . $suffix;
        }

        return $slug;
    }

    private function ensureForceDeletable(Location $location): void
    {
        $directReferences = [
            ['table' => 'service_location', 'column' => 'location_id', 'label' => 'service/location mappings'],
            ['table' => 'pricing_packages', 'column' => 'location_id', 'label' => 'pricing packages'],
            ['table' => 'leads', 'column' => 'location_id', 'label' => 'leads'],
        ];

        foreach ($directReferences as $reference) {
            if (Schema::hasTable($reference['table']) && DB::table($reference['table'])->where($reference['column'], $location->id)->exists()) {
                throw ValidationException::withMessages(['location' => 'This location still has related ' . $reference['label'] . '. Remove those records before permanently deleting the location.']);
            }
        }

        $morphType = $location->getMorphClass();
        $polymorphicReferences = [
            ['table' => 'menu_items', 'type' => 'linkable_type', 'id' => 'linkable_id', 'label' => 'menu links'],
            ['table' => 'galleries', 'type' => 'attachable_type', 'id' => 'attachable_id', 'label' => 'galleries'],
            ['table' => 'videos', 'type' => 'attachable_type', 'id' => 'attachable_id', 'label' => 'videos'],
            ['table' => 'testimonialables', 'type' => 'testimonialable_type', 'id' => 'testimonialable_id', 'label' => 'testimonial mappings'],
            ['table' => 'faqables', 'type' => 'faqable_type', 'id' => 'faqable_id', 'label' => 'FAQ mappings'],
            ['table' => 'contact_targets', 'type' => 'targetable_type', 'id' => 'targetable_id', 'label' => 'contact targets'],
            ['table' => 'seo_metas', 'type' => 'seoable_type', 'id' => 'seoable_id', 'label' => 'SEO metadata'],
        ];

        foreach ($polymorphicReferences as $reference) {
            if (Schema::hasTable($reference['table']) && DB::table($reference['table'])->where($reference['type'], $morphType)->where($reference['id'], $location->id)->exists()) {
                throw ValidationException::withMessages(['location' => 'This location still has related ' . $reference['label'] . '. Remove those records before permanently deleting the location.']);
            }
        }
    }

    private function bulkPublish(array $ids): string
    {
        Location::query()->whereIn('id', $ids)->update(['status' => 'published', 'published_at' => now(), 'updated_at' => now()]);

        return 'Selected locations published.';
    }

    private function bulkUnpublish(array $ids): string
    {
        Location::query()->whereIn('id', $ids)->update(['status' => 'draft', 'published_at' => null, 'updated_at' => now()]);

        return 'Selected locations moved to draft.';
    }

    private function bulkArchive(array $ids): string
    {
        Location::query()->whereIn('id', $ids)->update(['status' => 'archived', 'updated_at' => now()]);

        return 'Selected locations archived.';
    }

    private function bulkDelete(array $ids): string
    {
        Location::query()->whereIn('id', $ids)->get()->each(fn (Location $location) => $location->delete());

        return 'Selected locations moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        Location::onlyTrashed()->whereIn('id', $ids)->get()->each(fn (Location $location) => $location->restore());

        return 'Selected locations restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        DB::transaction(function () use ($ids): void {
            $locations = Location::onlyTrashed()->whereIn('id', $ids)->get();

            foreach ($locations as $location) {
                $this->ensureForceDeletable($location);
            }

            $locations->each(function (Location $location): void {
                $this->locationMediaService->purgeAll($location);
                $location->forceDelete();
            });
        });

        return 'Selected locations permanently deleted.';
    }

    private function ensurePermission(string $permission): void
    {
        $admin = auth('admin')->user();
        abort_unless($admin && $admin->can($permission), 403, 'You do not have permission to perform this location action.');
    }
}
