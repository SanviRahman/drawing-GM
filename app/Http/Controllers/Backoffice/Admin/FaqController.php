<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Location;
use App\Models\MenuItem;
use App\Services\Frontend\NavbarFaqs;
use App\Models\Page;
use App\Models\Service;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePermission('faq_list');

        $query = Faq::query()->withCount(['pages', 'services', 'locations', 'menuItems']);
        $this->applyFilters($query, $request);
        $faqs = $query->latest('id')->paginate(15)->withQueryString();
        $navItems = app(NavbarFaqs::class)->navigationItems(includeInactive: true);
        $pageItems = Page::query()->withCount('faqs')->orderByDesc('is_homepage')->orderBy('title')->get();
        $selectedPageId = (int) $request->query('page_id', 0);
        $selectedNavId = (int) $request->query('menu_item_id', 0);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.faqs.partials.table', [
                    'faqs' => $faqs,
                    'isTrash' => false,
                ])->render(),
            ]);
        }

        $title = 'FAQs Management';
        $breadcrumb = [
            ['text' => 'Content', 'url' => null],
            ['text' => 'FAQs', 'url' => route('admin.faqs.index')],
        ];

        return view('backoffice.admin.faqs.index', compact('faqs', 'title', 'breadcrumb', 'navItems', 'selectedNavId', 'pageItems', 'selectedPageId'));
    }

    public function list(Request $request)
    {
        $this->ensurePermission('faq_list');

        $query = Faq::query()->select('id', 'question', 'is_active');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where('question', 'like', "%{$search}%");
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        $data = $query->latest('id')->limit(100)->get()->map(function (Faq $faq): array {
            return [
                'id' => $faq->id,
                'question' => Str::limit($this->plainText($faq->question), 160),
                'is_active' => $faq->is_active,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('faq_create');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.faqs.partials.form', array_merge($this->targetOptions(), [
                'preselectedMenuItemId' => (int) $request->query('menu_item_id', 0),
                'preselectedPageId' => (int) $request->query('page_id', 0),
            ]))->render(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('faq_create');
        $validated = $this->validateFaq($request);

        $faq = DB::transaction(function () use ($validated): Faq {
            $faq = Faq::create($this->faqAttributes($validated));
            $this->syncMappings($faq, $validated);

            return $faq;
        });

        return response()->json([
            'success' => true,
            'message' => 'FAQ created successfully.',
            'data' => ['id' => $faq->id],
        ]);
    }

    public function show(Request $request, Faq $faq)
    {
        $this->ensurePermission('faq_view');
        abort_unless($request->ajax(), 404);

        $faq->load([
            'pages:id,title',
            'services:id,name',
            'locations:id,name',
            'menuItems:id,label',
        ]);

        return response()->json([
            'html' => view('backoffice.admin.faqs.partials.show', compact('faq'))->render(),
        ]);
    }

    public function edit(Request $request, Faq $faq)
    {
        $this->ensurePermission('faq_update');
        abort_unless($request->ajax(), 404);

        $faq->load([
            'pages:id,title',
            'services:id,name',
            'locations:id,name',
            'menuItems:id,label',
        ]);

        return response()->json([
            'html' => view('backoffice.admin.faqs.partials.form', array_merge(
                ['faq' => $faq],
                $this->targetOptions()
            ))->render(),
        ]);
    }

    public function update(Request $request, Faq $faq)
    {
        $this->ensurePermission('faq_update');
        $validated = $this->validateFaq($request);

        DB::transaction(function () use ($faq, $validated): void {
            $faq->update($this->faqAttributes($validated));
            $this->syncMappings($faq, $validated);
        });

        return response()->json([
            'success' => true,
            'message' => 'FAQ updated successfully.',
        ]);
    }

    public function destroy(Faq $faq)
    {
        $this->ensurePermission('faq_delete');
        $faq->delete();

        return response()->json([
            'success' => true,
            'message' => 'FAQ moved to trash.',
        ]);
    }

    public function toggle(Faq $faq)
    {
        $this->ensurePermission('faq_toggle');
        $faq->update(['is_active' => ! $faq->is_active]);

        return response()->json([
            'success' => true,
            'message' => $faq->is_active ? 'FAQ activated.' : 'FAQ deactivated.',
        ]);
    }

    public function trash(Request $request)
    {
        $this->ensurePermission('faq_trash');

        $query = Faq::onlyTrashed()->withCount(['pages', 'services', 'locations', 'menuItems']);
        $this->applyFilters($query, $request);
        $faqs = $query->latest('deleted_at')->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.faqs.partials.table', [
                    'faqs' => $faqs,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        $title = 'Trashed FAQs';
        $breadcrumb = [
            ['text' => 'Content', 'url' => null],
            ['text' => 'FAQs', 'url' => route('admin.faqs.index')],
            ['text' => 'Trash', 'url' => null],
        ];

        return view('backoffice.admin.faqs.trash', compact('faqs', 'title', 'breadcrumb'));
    }

    public function restore(int $faq)
    {
        $this->ensurePermission('faq_restore');
        Faq::onlyTrashed()->findOrFail($faq)->restore();

        return response()->json([
            'success' => true,
            'message' => 'FAQ restored successfully.',
        ]);
    }

    public function forceDelete(int $faq)
    {
        $this->ensurePermission('faq_force_delete');

        DB::transaction(function () use ($faq): void {
            Faq::onlyTrashed()->findOrFail($faq)->forceDelete();
        });

        return response()->json([
            'success' => true,
            'message' => 'FAQ permanently deleted.',
        ]);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['activate', 'deactivate', 'delete', 'restore', 'force_delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $ids = collect($validated['ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

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

    public function reorderMappings(Request $request)
    {
        $this->ensurePermission('faq_reorder');

        $validated = $request->validate([
            'target_type' => ['required', Rule::in(['page', 'service', 'location', 'menu_item'])],
            'target_id' => ['required', 'integer'],
            'faq_ids' => ['required', 'array', 'min:1'],
            'faq_ids.*' => ['required', 'integer', 'distinct', Rule::exists('faqs', 'id')->whereNull('deleted_at')],
        ]);

        [$modelClass, $targetLabel] = $this->targetModelMeta($validated['target_type']);
        $target = $modelClass::query()->find($validated['target_id']);

        if (! $target) {
            throw ValidationException::withMessages([
                'target_id' => "The selected {$targetLabel} is unavailable.",
            ]);
        }

        if ($validated['target_type'] === 'menu_item'
            && ! app(NavbarFaqs::class)->navigationItems(includeInactive: true)->contains('id', $target->getKey())) {
            throw ValidationException::withMessages([
                'target_id' => 'The chosen navigation destination is not an internal primary menu item.',
            ]);
        }

        $morphType = $target->getMorphClass();
        $currentIds = DB::table('faqables')
            ->where('faqable_type', $morphType)
            ->where('faqable_id', $target->getKey())
            ->orderBy('sort_order')
            ->orderBy('faq_id')
            ->pluck('faq_id')
            ->map(fn ($id) => (int) $id)
            ->values();

        $requestedIds = collect($validated['faq_ids'])->map(fn ($id) => (int) $id)->values();

        if ($currentIds->sort()->values()->all() !== $requestedIds->sort()->values()->all()) {
            throw ValidationException::withMessages([
                'faq_ids' => 'The reorder list must contain every FAQ currently attached to the selected target exactly once.',
            ]);
        }

        DB::transaction(function () use ($morphType, $target, $requestedIds): void {
            $requestedIds->each(function (int $faqId, int $index) use ($morphType, $target): void {
                DB::table('faqables')
                    ->where('faq_id', $faqId)
                    ->where('faqable_type', $morphType)
                    ->where('faqable_id', $target->getKey())
                    ->update([
                        'sort_order' => ($index + 1) * 10,
                        'updated_at' => now(),
                    ]);
            });
        });

        return response()->json([
            'success' => true,
            'message' => 'FAQ order updated for the selected target.',
        ]);
    }

    public function toggleMenuSection(MenuItem $menuItem)
    {
        $this->ensurePermission('faq_toggle');
        abort_unless(app(NavbarFaqs::class)->navigationItems(includeInactive: true)->contains('id', $menuItem->id), 404);

        $menuItem->update(['faq_section_enabled' => ! $menuItem->faq_section_enabled]);

        return response()->json([
            'success' => true,
            'enabled' => (bool) $menuItem->faq_section_enabled,
            'message' => $menuItem->faq_section_enabled ? 'Navigation FAQ section enabled.' : 'Navigation FAQ section disabled.',
        ]);
    }

    public function togglePageSection(Page $page)
    {
        $this->ensurePermission('faq_toggle');

        $page->update(['faq_section_enabled' => ! $page->faq_section_enabled]);

        return response()->json([
            'success' => true,
            'enabled' => (bool) $page->faq_section_enabled,
            'message' => $page->faq_section_enabled ? 'Page FAQ section enabled.' : 'Page FAQ section disabled.',
        ]);
    }

    private function validateFaq(Request $request): array
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:60000'],
            'answer' => ['required', 'string', 'max:2000000'],
            'is_active' => ['required', 'boolean'],
            'page_ids' => ['nullable', 'array'],
            'page_ids.*' => ['integer', 'distinct', Rule::exists('pages', 'id')->whereNull('deleted_at')],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'distinct', Rule::exists('services', 'id')->whereNull('deleted_at')],
            'location_ids' => ['nullable', 'array'],
            'location_ids.*' => ['integer', 'distinct', Rule::exists('locations', 'id')->whereNull('deleted_at')],
            'menu_item_ids' => ['nullable', 'array'],
            'menu_item_ids.*' => ['integer', 'distinct', Rule::in(app(NavbarFaqs::class)->navigationItems(includeInactive: true)->pluck('id')->all())],
        ]);

        if (! $this->hasRichTextContent($validated['question'])) {
            throw ValidationException::withMessages([
                'question' => 'Question is required.',
            ]);
        }

        if (! $this->hasRichTextContent($validated['answer'])) {
            throw ValidationException::withMessages([
                'answer' => 'Answer is required.',
            ]);
        }

        return $validated;
    }

    private function faqAttributes(array $validated): array
    {
        return [
            'question' => $this->sanitizeRichText($validated['question']),
            'answer' => $this->sanitizeRichText($validated['answer']),
            'is_active' => (bool) $validated['is_active'],
        ];
    }

    private function targetOptions(): array
    {
        return [
            'pageOptions' => Page::query()->orderBy('title')->pluck('title', 'id'),
            'serviceOptions' => Service::query()->orderBy('name')->pluck('name', 'id'),
            'locationOptions' => Location::query()->orderBy('name')->pluck('name', 'id'),
            'navOptions' => app(NavbarFaqs::class)->navigationItems(includeInactive: true)->pluck('label', 'id'),
        ];
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('target_type')) {
            match ((string) $request->target_type) {
                'page' => $query->whereHas('pages'),
                'service' => $query->whereHas('services'),
                'location' => $query->whereHas('locations'),
                'menu_item' => $query->whereHas('menuItems'),
                'unassigned' => $query
                    ->whereDoesntHave('pages')
                    ->whereDoesntHave('services')
                    ->whereDoesntHave('locations')
                    ->whereDoesntHave('menuItems'),
                default => null,
            };
        }

        if ($request->filled('page_id')) {
            $pageId = (int) $request->input('page_id');
            $query->whereHas('pages', fn (Builder $builder) => $builder->whereKey($pageId));
        }

        if ($request->filled('menu_item_id')) {
            $menuItemId = (int) $request->input('menu_item_id');
            $query->whereHas('menuItems', fn (Builder $builder) => $builder->whereKey($menuItemId));
        }
    }

    private function syncMappings(Faq $faq, array $validated): void
    {
        $this->syncTargetMappings($faq, Page::class, $validated['page_ids'] ?? []);
        $this->syncTargetMappings($faq, Service::class, $validated['service_ids'] ?? []);
        $this->syncTargetMappings($faq, Location::class, $validated['location_ids'] ?? []);

        // Preserve assignments to navbar destinations that are temporarily inactive,
        // or hidden from the editor. Editing the FAQ must not silently erase them.
        $morphType = (new MenuItem())->getMorphClass();
        $existingMenuIds = DB::table('faqables')
            ->where('faq_id', $faq->id)
            ->where('faqable_type', $morphType)
            ->pluck('faqable_id')
            ->map(fn ($id) => (int) $id);
        $editableMenuIds = app(NavbarFaqs::class)->navigationItems(includeInactive: true)->pluck('id')
            ->map(fn ($id) => (int) $id);
        $keepUnavailableMenuIds = $existingMenuIds->diff($editableMenuIds)->all();

        $this->syncTargetMappings(
            $faq,
            MenuItem::class,
            array_merge($validated['menu_item_ids'] ?? [], $keepUnavailableMenuIds)
        );
    }

    /**
     * Preserve target-specific order for existing mappings and append new mappings.
     *
     * @param array<int, int|string> $targetIds
     */
    private function syncTargetMappings(Faq $faq, string $modelClass, array $targetIds): void
    {
        $targetIds = collect($targetIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        $morphType = (new $modelClass())->getMorphClass();

        $existing = DB::table('faqables')
            ->where('faq_id', $faq->id)
            ->where('faqable_type', $morphType)
            ->pluck('sort_order', 'faqable_id');

        $deleteQuery = DB::table('faqables')
            ->where('faq_id', $faq->id)
            ->where('faqable_type', $morphType);

        if ($targetIds->isEmpty()) {
            $deleteQuery->delete();
        } else {
            $deleteQuery->whereNotIn('faqable_id', $targetIds->all())->delete();
        }

        foreach ($targetIds as $targetId) {
            if ($existing->has($targetId)) {
                continue;
            }

            DB::table('faqables')->insert([
                'faq_id' => $faq->id,
                'faqable_type' => $morphType,
                'faqable_id' => $targetId,
                'sort_order' => $this->nextTargetSortOrder($morphType, $targetId),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function nextTargetSortOrder(string $morphType, int $targetId): int
    {
        $max = DB::table('faqables')
            ->where('faqable_type', $morphType)
            ->where('faqable_id', $targetId)
            ->max('sort_order');

        return ((int) $max) + 10;
    }

    /**
     * @return array{0: class-string, 1: string}
     */
    private function targetModelMeta(string $targetType): array
    {
        return match ($targetType) {
            'page' => [Page::class, 'page'],
            'service' => [Service::class, 'service'],
            'location' => [Location::class, 'location'],
            'menu_item' => [MenuItem::class, 'navigation item'],
            default => throw ValidationException::withMessages([
                'target_type' => 'Unsupported FAQ target type.',
            ]),
        };
    }

    private function bulkStatus(array $ids, bool $status): string
    {
        $this->ensurePermission('faq_toggle');
        Faq::whereIn('id', $ids)->update(['is_active' => $status]);

        return $status ? 'Selected FAQs activated.' : 'Selected FAQs deactivated.';
    }

    private function bulkDelete(array $ids): string
    {
        $this->ensurePermission('faq_delete');
        Faq::whereIn('id', $ids)->delete();

        return 'Selected FAQs moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        $this->ensurePermission('faq_restore');
        Faq::onlyTrashed()->whereIn('id', $ids)->restore();

        return 'Selected FAQs restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        $this->ensurePermission('faq_force_delete');

        DB::transaction(function () use ($ids): void {
            Faq::onlyTrashed()->whereIn('id', $ids)->get()->each(
                fn (Faq $faq) => $faq->forceDelete()
            );
        });

        return 'Selected FAQs permanently deleted.';
    }

    private function hasRichTextContent(mixed $value): bool
    {
        $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\u{00A0}", ' ', $text);

        return trim($text) !== '';
    }

    private function plainText(mixed $value): string
    {
        $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/u', ' ', $text) ?? $text;

        return trim($text);
    }

    private function sanitizeRichText(mixed $value): string
    {
        $html = trim((string) $value);

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

        return trim($html);
    }

    private function ensurePermission(string $permission): void
    {
        abort_unless(
            auth('admin')->user()?->can($permission),
            403,
            'You do not have permission to perform this FAQ action.'
        );
    }
}
