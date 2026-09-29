<?php
namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Services\PageMediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    public function __construct(private readonly PageMediaService $pageMediaService)
    {}

    public function index(Request $request)
    {
        $this->ensurePermission('page_list');

        $query = Page::query()->with(['createdBy:id,name', 'updatedBy:id,name']);
        $this->applyFilters($query, $request);

        $pages     = $query->latest('id')->paginate(15)->withQueryString();
        $templates = Page::query()->select('template')->whereNotNull('template')->distinct()->orderBy('template')->pluck('template');

        if ($request->ajax()) {
            return response()->json(['html' => view('backoffice.admin.pages.partials.table', compact('pages'))->render()]);
        }

        $title      = 'Pages Management';
        $breadcrumb = [['text' => 'CMS', 'url' => null],['text' => 'Pages', 'url' => route('admin.pages.index')]];

        return view('backoffice.admin.pages.index', compact('pages', 'templates', 'title', 'breadcrumb'));
    }

    public function list(Request $request)
    {
        $this->ensurePermission('page_list');

        $query = Page::query()->select('id', 'title', 'slug', 'status', 'is_homepage');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(fn($q) => $q->where('title', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"));
        }

        return response()->json(['success' => true, 'data' => $query->orderBy('title')->limit(50)->get()]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('page_create');
        abort_unless($request->ajax(), 404);

        return response()->json(['html' => view('backoffice.admin.pages.partials.form')->render()]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('page_create');
        $validated = $this->validatePage($request);
        $adminId   = (int) auth('admin')->id();

        $page = DB::transaction(function () use ($request, $validated, $adminId): Page {
            $attributes               = $this->pageAttributes($validated, null);
            $attributes['created_by'] = $adminId;
            $attributes['updated_by'] = $adminId;

            if ($attributes['is_homepage']) {
                Page::withTrashed()->where('is_homepage', true)->update(['is_homepage' => false]);
            }

            $page = Page::create($attributes);
            $this->pageMediaService->syncFromRequest($request, $page);

            return $page;
        });

        return response()->json(['success' => true, 'message' => 'Page created successfully.', 'data' => ['id' => $page->id]]);
    }

    public function show(Request $request, Page $page)
    {
        $this->ensurePermission('page_view');
        abort_unless($request->ajax(), 404);

        $page->load(['createdBy:id,name', 'updatedBy:id,name', 'media']);

        return response()->json(['html' => view('backoffice.admin.pages.partials.show', compact('page'))->render()]);
    }

    public function edit(Request $request, Page $page)
    {
        $this->ensurePermission('page_update');
        abort_unless($request->ajax(), 404);

        $page->load('media');

        return response()->json(['html' => view('backoffice.admin.pages.partials.form', compact('page'))->render()]);
    }

    public function update(Request $request, Page $page)
    {
        $this->ensurePermission('page_update');
        $validated = $this->validatePage($request, $page);
        $adminId   = (int) auth('admin')->id();

        DB::transaction(function () use ($request, $validated, $page, $adminId): void {
            $attributes               = $this->pageAttributes($validated, $page);
            $attributes['updated_by'] = $adminId;

            if ($attributes['is_homepage']) {
                Page::withTrashed()->where('id', '!=', $page->getKey())->where('is_homepage', true)->update(['is_homepage' => false]);
            }

            $page->update($attributes);
            $this->pageMediaService->syncFromRequest($request, $page);
        });

        return response()->json(['success' => true, 'message' => 'Page updated successfully.']);
    }

    public function destroy(Page $page)
    {
        $this->ensurePermission('page_delete');
        $page->delete();

        return response()->json(['success' => true, 'message' => 'Page moved to trash.']);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['publish', 'unpublish', 'archive', 'delete', 'restore', 'force_delete'])],
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['required', 'integer', 'distinct'],
        ]);

        $permission = match ($validated['action']) {
            'publish'      => 'page_publish',
            'unpublish'    => 'page_unpublish',
            'archive'      => 'page_update',
            'delete'       => 'page_delete',
            'restore'      => 'page_restore',
            'force_delete' => 'page_force_delete',
        };

        $this->ensurePermission($permission);
        $ids = collect($validated['ids'])->map(fn($id) => (int) $id)->unique()->values()->all();

        $message = match ($validated['action']) {
            'publish'      => $this->bulkPublish($ids),
            'unpublish'    => $this->bulkUnpublish($ids),
            'archive'      => $this->bulkArchive($ids),
            'delete'       => $this->bulkDelete($ids),
            'restore'      => $this->bulkRestore($ids),
            'force_delete' => $this->bulkForceDelete($ids),
        };

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function trash(Request $request)
    {
        $this->ensurePermission('page_trash');

        $query = Page::onlyTrashed()->with(['createdBy:id,name', 'updatedBy:id,name']);
        $this->applyFilters($query, $request);

        $pages     = $query->latest('deleted_at')->paginate(15)->withQueryString();
        $templates = Page::onlyTrashed()->select('template')->whereNotNull('template')->distinct()->orderBy('template')->pluck('template');

        if ($request->ajax()) {
            return response()->json(['html' => view('backoffice.admin.pages.partials.table', ['pages' => $pages, 'isTrash' => true])->render()]);
        }

        $title      = 'Trashed Pages';
        $breadcrumb = [['text' => 'CMS', 'url' => null],['text' => 'Pages', 'url' => route('admin.pages.index')], ['text' => 'Trash', 'url' => null]];

        return view('backoffice.admin.pages.trash', compact('pages', 'templates', 'title', 'breadcrumb'));
    }

    public function restore(int $page)
    {
        $this->ensurePermission('page_restore');

        DB::transaction(function () use ($page): void {
            $record = Page::onlyTrashed()->findOrFail($page);

            if ($record->is_homepage) {
                Page::withTrashed()->where('id', '!=', $record->getKey())->where('is_homepage', true)->update(['is_homepage' => false]);
            }

            $record->restore();
        });

        return response()->json(['success' => true, 'message' => 'Page restored successfully.']);
    }

    public function forceDelete(int $page)
    {
        $this->ensurePermission('page_force_delete');

        DB::transaction(function () use ($page): void {
            $record = Page::onlyTrashed()->findOrFail($page);

            if (Schema::hasTable('page_sections') && DB::table('page_sections')->where('page_id', $record->id)->exists()) {
                throw ValidationException::withMessages([
                    'page' => 'This page still contains page sections. Permanently delete its page sections first.',
                ]);
            }

            $this->pageMediaService->purgeAll($record);
            $record->forceDelete();
        });

        return response()->json(['success' => true, 'message' => 'Page permanently deleted.']);
    }

    public function publish(Page $page)
    {
        $this->ensurePermission('page_publish');
        $page->update(['status' => 'published', 'published_at' => $page->published_at ?? now(), 'updated_by' => auth('admin')->id()]);

        return response()->json(['success' => true, 'message' => 'Page published successfully.']);
    }

    public function unpublish(Page $page)
    {
        $this->ensurePermission('page_unpublish');
        $page->update(['status' => 'draft', 'published_at' => null, 'updated_by' => auth('admin')->id()]);

        return response()->json(['success' => true, 'message' => 'Page moved back to draft.']);
    }

    public function duplicate(Page $page)
    {
        $this->ensurePermission('page_duplicate');
        $adminId = (int) auth('admin')->id();

        $copy = DB::transaction(function () use ($page, $adminId): Page {
            $copy = Page::create([
                'title'        => Str::limit($page->title . ' Copy', 190, ''),
                'slug'         => $this->uniqueCopySlug($page->slug),
                'excerpt'      => $page->excerpt,
                'template'     => $page->template,
                'hero_config'  => $page->hero_config ?? [],
                'status'       => 'draft',
                'published_at' => null,
                'is_homepage'  => false,
                'show_header'  => $page->show_header,
                'show_footer'  => $page->show_footer,
                'created_by'   => $adminId,
                'updated_by'   => $adminId,
            ]);

            $this->pageMediaService->duplicateHeroMedia($page, $copy);

            if (Schema::hasTable('page_sections')) {
                $page->sections()->with('mediaItems')->get()->each(function (PageSection $section) use ($copy): void {
                    $newSection = $copy->sections()->create([
                        'section_definition_id' => $section->section_definition_id,
                        'heading'               => $section->heading,
                        'subheading'            => $section->subheading,
                        'payload'               => $section->payload ?? [],
                        'theme'                 => $section->theme,
                        'sort_order'            => $section->sort_order,
                        'is_active'             => $section->is_active,
                        'starts_at'             => $section->starts_at,
                        'ends_at'               => $section->ends_at,
                    ]);

                    if (Schema::hasTable('section_media')) {
                        $section->mediaItems->each(function ($mediaItem) use ($newSection): void {
                            $newSection->mediaItems()->create([
                                'media_id'         => $mediaItem->media_id,
                                'role'             => $mediaItem->role,
                                'caption_override' => $mediaItem->caption_override,
                                'sort_order'       => $mediaItem->sort_order,
                            ]);
                        });
                    }
                });
            }

            return $copy;

        });

        return response()->json(['success' => true, 'message' => 'Page duplicated as draft.', 'data' => ['id' => $copy->id]]);
    }

    private function validatePage(Request $request, ?Page $page = null): array
    {
        return $request->validate([
            'title'                  => ['required', 'string', 'max:190'],
            'slug'                   => ['required', 'string', 'max:190', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('pages', 'slug')->ignore($page?->id)],
            'excerpt'                => ['nullable', 'string', 'max:60000'],
            'template'               => ['required', 'string', 'max:100', 'regex:/^[A-Za-z0-9_-]+$/'],
            'status'                 => ['required', Rule::in(array_keys(Page::STATUSES))],
            'published_at'           => ['nullable', 'date'],
            'is_homepage'            => ['required', 'boolean'],
            'show_header'            => ['required', 'boolean'],
            'show_footer'            => ['required', 'boolean'],
            'hero_heading'           => ['nullable', 'string', 'max:190'],
            'hero_overlay'           => ['nullable', 'numeric', 'min:0', 'max:1'],
            'hero_focal_position'    => ['nullable', 'string', 'max:50'],
            'hero_cta_label'         => ['nullable', 'string', 'max:100'],
            'hero_cta_url'           => ['nullable', 'string', 'max:500', function (string $attribute, mixed $value, \Closure $fail): void {
                $value = strtolower(trim((string) $value));
                if (Str::startsWith($value, ['javascript:', 'data:', 'vbscript:'])) {
                    $fail('The hero CTA URL contains a disallowed scheme.');
                }
            }],
            'hero_slider_mode'       => ['required', 'boolean'],
            'hero_desktop'           => ['nullable', 'array', 'max:10'],
            'hero_desktop.*'         => ['file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'hero_mobile'            => ['nullable', 'array', 'max:10'],
            'hero_mobile.*'          => ['file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'hero_desktop_media_ids' => ['nullable', 'string', 'max:500'],
            'hero_mobile_media_ids'  => ['nullable', 'string', 'max:500'],
            'hero_desktop_clear'     => ['nullable', 'boolean'],
            'hero_mobile_clear'      => ['nullable', 'boolean'],
        ]);
    }

    private function pageAttributes(array $validated, ?Page $page): array
    {
        $status      = $validated['status'];
        $publishedAt = $validated['published_at'] ?? null;

        if ($status === 'published' && ! $publishedAt) {
            $publishedAt = $page?->published_at ?? now();
        }

        if ($status === 'draft') {
            $publishedAt = null;
        }

        return [
            'title'        => trim($validated['title']),
            'slug'         => strtolower(trim($validated['slug'])),
            'excerpt'      => $this->sanitizeRichText($validated['excerpt'] ?? null),
            'template'     => trim($validated['template']),
            'hero_config'  => $this->heroConfig($validated),
            'status'       => $status,
            'published_at' => $publishedAt,
            'is_homepage'  => (bool) $validated['is_homepage'],
            'show_header'  => (bool) $validated['show_header'],
            'show_footer'  => (bool) $validated['show_footer'],
        ];
    }

    private function sanitizeRichText(mixed $value): ?string
    {
        $html = trim((string) ($value ?? ''));

        if ($html === '') {
            return null;
        }

        $allowedTags = '<p><br><strong><b><em><i><u><s><ul><ol><li><blockquote><h1><h2><h3><h4><h5><h6><a><hr><pre><code><span><table><thead><tbody><tfoot><tr><th><td><img>';
        $html        = strip_tags($html, $allowedTags);
        $html        = preg_replace('/\s(?:on\w+|style|srcdoc|formaction)\s*=\s*(["\']).*?\1/isu', '', $html) ?? $html;
        $html        = preg_replace('/\s(?:on\w+|style|srcdoc|formaction)\s*=\s*[^\s>]+/iu', '', $html) ?? $html;
        $html        = preg_replace_callback('/\s(href|src)\s*=\s*(["\'])(.*?)\2/isu', function (array $matches): string {
            $url = trim(html_entity_decode($matches[3], ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            if ($url === '' || Str::startsWith(strtolower($url), ['javascript:', 'data:', 'vbscript:'])) {
                return '';
            }

            return ' ' . strtolower($matches[1]) . '=' . $matches[2] . e($url) . $matches[2];
        }, $html) ?? $html;

        return trim($html) !== '' ? trim($html) : null;
    }

    private function heroConfig(array $validated): array
    {
        return [
            'heading'        => filled($validated['hero_heading'] ?? null) ? trim((string) $validated['hero_heading']) : null,
            'overlay'        => isset($validated['hero_overlay']) && $validated['hero_overlay'] !== '' ? (float) $validated['hero_overlay'] : null,
            'focal_position' => filled($validated['hero_focal_position'] ?? null) ? trim((string) $validated['hero_focal_position']) : null,
            'cta'            => [
                'label' => filled($validated['hero_cta_label'] ?? null) ? trim((string) $validated['hero_cta_label']) : null,
                'url'   => filled($validated['hero_cta_url'] ?? null) ? trim((string) $validated['hero_cta_url']) : null,
            ],
            'slider_mode'    => (bool) $validated['hero_slider_mode'],
        ];
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(fn($q) => $q->where('title', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%")->orWhere('excerpt', 'like', "%{$search}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->status);
        }

        if ($request->filled('template')) {
            $query->where('template', (string) $request->template);
        }

        if ($request->filled('homepage')) {
            $query->where('is_homepage', $request->boolean('homepage'));
        }
    }

    private function uniqueCopySlug(string $baseSlug): string
    {
        $base    = Str::limit(Str::slug($baseSlug . '-copy'), 170, '');
        $slug    = $base;
        $counter = 2;

        while (Page::withTrashed()->where('slug', $slug)->exists()) {
            $suffix = '-' . $counter++;
            $slug   = Str::limit($base, 190 - strlen($suffix), '') . $suffix;
        }

        return $slug;
    }

    private function bulkPublish(array $ids): string
    {
        Page::query()->whereIn('id', $ids)->update(['status' => 'published', 'published_at' => now(), 'updated_by' => auth('admin')->id(), 'updated_at' => now()]);
        return 'Selected pages published.';
    }

    private function bulkUnpublish(array $ids): string
    {
        Page::query()->whereIn('id', $ids)->update(['status' => 'draft', 'published_at' => null, 'updated_by' => auth('admin')->id(), 'updated_at' => now()]);
        return 'Selected pages moved to draft.';
    }

    private function bulkArchive(array $ids): string
    {
        Page::query()->whereIn('id', $ids)->update(['status' => 'archived', 'updated_by' => auth('admin')->id(), 'updated_at' => now()]);
        return 'Selected pages archived.';
    }

    private function bulkDelete(array $ids): string
    {
        Page::query()->whereIn('id', $ids)->get()->each(fn(Page $page) => $page->delete());
        return 'Selected pages moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        DB::transaction(function () use ($ids): void {
            Page::onlyTrashed()->whereIn('id', $ids)->get()->each(function (Page $page): void {
                if ($page->is_homepage) {
                    Page::withTrashed()->where('id', '!=', $page->getKey())->where('is_homepage', true)->update(['is_homepage' => false]);
                }
                $page->restore();
            });
        });

        return 'Selected pages restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        DB::transaction(function () use ($ids): void {
            $pages = Page::onlyTrashed()->whereIn('id', $ids)->get();

            if (Schema::hasTable('page_sections')) {
                $blockedPageIds = DB::table('page_sections')->whereIn('page_id', $pages->pluck('id')->all())->pluck('page_id')->unique()->values();

                if ($blockedPageIds->isNotEmpty()) {
                    throw ValidationException::withMessages([
                        'pages' => 'One or more selected pages still contain page sections. Permanently delete those page sections first.',
                    ]);
                }
            }

            $pages->each(function (Page $page): void {
                $this->pageMediaService->purgeAll($page);
                $page->forceDelete();
            });
        });

        return 'Selected pages permanently deleted.';
    }

    private function ensurePermission(string $permission): void
    {
        $admin = auth('admin')->user();
        abort_unless($admin && $admin->can($permission), 403, 'You do not have permission to perform this page action.');
    }
}
