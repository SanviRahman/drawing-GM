<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Services\ServiceMediaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceController extends Controller
{
    public function __construct(private readonly ServiceMediaService $serviceMediaService) {}

    public function index(Request $request)
    {
        $this->ensurePermission('service_list');

        $query = Service::query();
        $this->applyFilters($query, $request);
        $services = $query->ordered()->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json(['html' => view('backoffice.admin.services.partials.table', compact('services'))->render()]);
        }

        $title = 'Services Management';
        $breadcrumb = [['text' => 'CMS', 'url' => null], ['text' => 'Services', 'url' => route('admin.services.index')]];

        return view('backoffice.admin.services.index', compact('services', 'title', 'breadcrumb'));
    }

    public function list(Request $request)
    {
        $this->ensurePermission('service_list');

        $query = Service::query()->select('id', 'name', 'slug', 'status', 'is_featured', 'sort_order');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(fn (Builder $builder) => $builder->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"));
        }

        if ($request->boolean('published_only')) {
            $query->published();
        }

        return response()->json(['success' => true, 'data' => $query->ordered()->limit(100)->get()]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('service_create');
        abort_unless($request->ajax(), 404);

        return response()->json(['html' => view('backoffice.admin.services.partials.form')->render()]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('service_create');
        $validated = $this->validateService($request);

        if (! array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = ((int) Service::query()->max('sort_order')) + 10;
        }

        $service = DB::transaction(function () use ($request, $validated): Service {
            $service = Service::create($this->serviceAttributes($validated));
            $this->serviceMediaService->syncFromRequest($request, $service);

            return $service;
        });

        return response()->json(['success' => true, 'message' => 'Service created successfully.', 'data' => ['id' => $service->id]]);
    }

    public function show(Request $request, Service $service)
    {
        $this->ensurePermission('service_view');
        abort_unless($request->ajax(), 404);

        $service->load('media');

        return response()->json(['html' => view('backoffice.admin.services.partials.show', compact('service'))->render()]);
    }

    public function edit(Request $request, Service $service)
    {
        $this->ensurePermission('service_update');
        abort_unless($request->ajax(), 404);

        $service->load('media');

        return response()->json(['html' => view('backoffice.admin.services.partials.form', compact('service'))->render()]);
    }

    public function update(Request $request, Service $service)
    {
        $this->ensurePermission('service_update');
        $validated = $this->validateService($request, $service);

        if (! array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = $service->sort_order;
        }

        DB::transaction(function () use ($request, $validated, $service): void {
            $service->update($this->serviceAttributes($validated, $service));
            $this->serviceMediaService->syncFromRequest($request, $service);
        });

        return response()->json(['success' => true, 'message' => 'Service updated successfully.']);
    }

    public function destroy(Service $service)
    {
        $this->ensurePermission('service_delete');
        $service->delete();

        return response()->json(['success' => true, 'message' => 'Service moved to trash.']);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['publish', 'unpublish', 'archive', 'delete', 'restore', 'force_delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $permission = match ($validated['action']) {
            'publish' => 'service_publish',
            'unpublish' => 'service_unpublish',
            'archive' => 'service_update',
            'delete' => 'service_delete',
            'restore' => 'service_restore',
            'force_delete' => 'service_force_delete',
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
        $this->ensurePermission('service_trash');

        $query = Service::onlyTrashed();
        $this->applyFilters($query, $request);
        $services = $query->orderByDesc('deleted_at')->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json(['html' => view('backoffice.admin.services.partials.table', ['services' => $services, 'isTrash' => true])->render()]);
        }

        $title = 'Trashed Services';
        $breadcrumb = [['text' => 'CMS', 'url' => null], ['text' => 'Services', 'url' => route('admin.services.index')], ['text' => 'Trash', 'url' => null]];

        return view('backoffice.admin.services.trash', compact('services', 'title', 'breadcrumb'));
    }

    public function restore(int $service)
    {
        $this->ensurePermission('service_restore');
        Service::onlyTrashed()->findOrFail($service)->restore();

        return response()->json(['success' => true, 'message' => 'Service restored successfully.']);
    }

    public function forceDelete(int $service)
    {
        $this->ensurePermission('service_force_delete');

        DB::transaction(function () use ($service): void {
            $record = Service::onlyTrashed()->findOrFail($service);
            $this->ensureForceDeletable($record);
            $this->serviceMediaService->purgeAll($record);
            $record->forceDelete();
        });

        return response()->json(['success' => true, 'message' => 'Service permanently deleted.']);
    }

    public function publish(Service $service)
    {
        $this->ensurePermission('service_publish');
        $service->update(['status' => 'published', 'published_at' => $service->published_at ?? now()]);

        return response()->json(['success' => true, 'message' => 'Service published successfully.']);
    }

    public function unpublish(Service $service)
    {
        $this->ensurePermission('service_unpublish');
        $service->update(['status' => 'draft', 'published_at' => null]);

        return response()->json(['success' => true, 'message' => 'Service moved back to draft.']);
    }

    public function duplicate(Service $service)
    {
        $this->ensurePermission('service_duplicate');

        $copy = DB::transaction(function () use ($service): Service {
            $copy = Service::create([
                'name' => Str::limit($service->name . ' Copy', 190, ''),
                'slug' => $this->uniqueCopySlug($service->slug),
                'summary' => $service->summary,
                'content' => $service->content,
                'icon' => $service->icon,
                'hero_config' => $service->hero_config ?? [],
                'status' => 'draft',
                'is_featured' => false,
                'sort_order' => ((int) Service::query()->max('sort_order')) + 10,
                'published_at' => null,
            ]);

            $this->serviceMediaService->duplicateMedia($service, $copy);

            return $copy;
        });

        return response()->json(['success' => true, 'message' => 'Service duplicated as draft.', 'data' => ['id' => $copy->id]]);
    }

    private function validateService(Request $request, ?Service $service = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'slug' => ['required', 'string', 'max:190', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('services', 'slug')->ignore($service?->id)],
            'summary' => ['nullable', 'string', 'max:60000'],
            'content' => ['nullable', 'string', 'max:500000'],
            'icon' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9 _-]+$/'],
            'status' => ['required', Rule::in(array_keys(Service::STATUSES))],
            'is_featured' => ['required', 'boolean'],
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
        ]);
    }

    private function serviceAttributes(array $validated, ?Service $service = null): array
    {
        $status = $validated['status'];
        $publishedAt = $validated['published_at'] ?? null;

        if ($status === 'published' && ! $publishedAt) {
            $publishedAt = $service?->published_at ?? now();
        }

        if ($status === 'draft') {
            $publishedAt = null;
        }

        return [
            'name' => trim($validated['name']),
            'slug' => strtolower(trim($validated['slug'])),
            'summary' => $this->sanitizeRichText($validated['summary'] ?? null),
            'content' => $this->sanitizeRichText($validated['content'] ?? null),
            'icon' => filled($validated['icon'] ?? null) ? trim((string) $validated['icon']) : null,
            'hero_config' => $this->heroConfig($validated),
            'status' => $status,
            'is_featured' => (bool) $validated['is_featured'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'published_at' => $publishedAt,
        ];
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
            $query->where(fn (Builder $builder) => $builder->where('name', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")->orWhere('summary', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->status);
        }

        if ($request->filled('featured')) {
            $query->where('is_featured', $request->boolean('featured'));
        }
    }

    private function uniqueCopySlug(string $baseSlug): string
    {
        $base = Str::limit(Str::slug($baseSlug . '-copy'), 170, '');
        $slug = $base;
        $counter = 2;

        while (Service::withTrashed()->where('slug', $slug)->exists()) {
            $suffix = '-' . $counter++;
            $slug = Str::limit($base, 190 - strlen($suffix), '') . $suffix;
        }

        return $slug;
    }

    private function ensureForceDeletable(Service $service): void
    {
        $directReferences = [
            ['table' => 'service_features', 'column' => 'service_id', 'label' => 'service features'],
            ['table' => 'service_location', 'column' => 'service_id', 'label' => 'service/location mappings'],
            ['table' => 'pricing_packages', 'column' => 'service_id', 'label' => 'pricing packages'],
            ['table' => 'lead_services', 'column' => 'service_id', 'label' => 'lead/service mappings'],
        ];

        foreach ($directReferences as $reference) {
            if (Schema::hasTable($reference['table']) && DB::table($reference['table'])->where($reference['column'], $service->id)->exists()) {
                throw ValidationException::withMessages(['service' => 'This service still has related ' . $reference['label'] . '. Remove those records before permanently deleting the service.']);
            }
        }

        $morphType = $service->getMorphClass();
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
            if (Schema::hasTable($reference['table']) && DB::table($reference['table'])->where($reference['type'], $morphType)->where($reference['id'], $service->id)->exists()) {
                throw ValidationException::withMessages(['service' => 'This service still has related ' . $reference['label'] . '. Remove those records before permanently deleting the service.']);
            }
        }
    }

    private function bulkPublish(array $ids): string
    {
        Service::query()->whereIn('id', $ids)->update(['status' => 'published', 'published_at' => now(), 'updated_at' => now()]);

        return 'Selected services published.';
    }

    private function bulkUnpublish(array $ids): string
    {
        Service::query()->whereIn('id', $ids)->update(['status' => 'draft', 'published_at' => null, 'updated_at' => now()]);

        return 'Selected services moved to draft.';
    }

    private function bulkArchive(array $ids): string
    {
        Service::query()->whereIn('id', $ids)->update(['status' => 'archived', 'updated_at' => now()]);

        return 'Selected services archived.';
    }

    private function bulkDelete(array $ids): string
    {
        Service::query()->whereIn('id', $ids)->get()->each(fn (Service $service) => $service->delete());

        return 'Selected services moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        Service::onlyTrashed()->whereIn('id', $ids)->get()->each(fn (Service $service) => $service->restore());

        return 'Selected services restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        DB::transaction(function () use ($ids): void {
            $services = Service::onlyTrashed()->whereIn('id', $ids)->get();

            foreach ($services as $service) {
                $this->ensureForceDeletable($service);
            }

            $services->each(function (Service $service): void {
                $this->serviceMediaService->purgeAll($service);
                $service->forceDelete();
            });
        });

        return 'Selected services permanently deleted.';
    }

    private function ensurePermission(string $permission): void
    {
        $admin = auth('admin')->user();
        abort_unless($admin && $admin->can($permission), 403, 'You do not have permission to perform this service action.');
    }
}
