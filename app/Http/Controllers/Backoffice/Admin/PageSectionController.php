<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\SectionDefinition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PageSectionController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePermission('page_section_list');

        $query = PageSection::query()->with(['page', 'sectionDefinition']);
        $this->applyFilters($query, $request);
        $pageSections = $query->ordered()->paginate(15)->withQueryString();
        $filters = $this->filterOptions();

        if ($request->ajax()) {
            return response()->json(['html' => view('backoffice.admin.page_sections.partials.table', compact('pageSections'))->render()]);
        }

        $title = 'Page Sections';
        $breadcrumb = [['text' => 'CMS', 'url' => null], ['text' => 'Page Sections', 'url' => route('admin.page_sections.index')]];

        return view('backoffice.admin.page_sections.index', compact('pageSections', 'filters', 'title', 'breadcrumb'));
    }

    public function create(Request $request)
    {
        $this->ensurePermission('page_section_create');
        abort_unless($request->ajax(), 404);

        $pages = Page::query()->select('id', 'title', 'slug')->ordered()->get();
        $sectionDefinitions = SectionDefinition::query()->select('id', 'key', 'name')->active()->ordered()->get();
        $selectedPageId = $request->integer('page_id') ?: null;

        return response()->json(['html' => view('backoffice.admin.page_sections.partials.form', compact('pages', 'sectionDefinitions', 'selectedPageId'))->render()]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('page_section_create');
        $validated = $this->validateModel($request);

        if (! array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = ((int) PageSection::query()->where('page_id', $validated['page_id'])->max('sort_order')) + 10;
        }

        $pageSection = PageSection::create($this->attributes($validated));

        return response()->json(['success' => true, 'message' => 'Page section created successfully.', 'data' => ['id' => $pageSection->id]]);
    }

    public function show(Request $request, PageSection $pageSection)
    {
        $this->ensurePermission('page_section_view');
        abort_unless($request->ajax(), 404);

        $pageSection->load(['page', 'sectionDefinition']);

        return response()->json(['html' => view('backoffice.admin.page_sections.partials.show', compact('pageSection'))->render()]);
    }

    public function edit(Request $request, PageSection $pageSection)
    {
        $this->ensurePermission('page_section_update');
        abort_unless($request->ajax(), 404);

        $pages = Page::query()->select('id', 'title', 'slug')->ordered()->get();
        $sectionDefinitions = SectionDefinition::query()->select('id', 'key', 'name', 'is_active')->where(function (Builder $query) use ($pageSection): void {
            $query->where('is_active', true)->orWhereKey($pageSection->section_definition_id);
        })->ordered()->get();
        $selectedPageId = null;

        return response()->json(['html' => view('backoffice.admin.page_sections.partials.form', compact('pageSection', 'pages', 'sectionDefinitions', 'selectedPageId'))->render()]);
    }

    public function update(Request $request, PageSection $pageSection)
    {
        $this->ensurePermission('page_section_update');
        $validated = $this->validateModel($request, $pageSection);
        $pageSection->update($this->attributes($validated));

        return response()->json(['success' => true, 'message' => 'Page section updated successfully.']);
    }

    public function destroy(PageSection $pageSection)
    {
        $this->ensurePermission('page_section_delete');
        $pageSection->delete();

        return response()->json(['success' => true, 'message' => 'Page section moved to trash.']);
    }

    public function toggle(PageSection $pageSection)
    {
        $this->ensurePermission('page_section_toggle');
        $pageSection->update(['is_active' => ! $pageSection->is_active]);

        return response()->json(['success' => true, 'message' => $pageSection->fresh()->is_active ? 'Page section activated.' : 'Page section deactivated.']);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['activate', 'deactivate', 'delete', 'restore', 'force_delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $permission = match ($validated['action']) {
            'activate', 'deactivate' => 'page_section_toggle',
            'delete' => 'page_section_delete',
            'restore' => 'page_section_restore',
            'force_delete' => 'page_section_force_delete',
        };

        $this->ensurePermission($permission);
        $ids = collect($validated['ids'])->map(fn ($id) => (int) $id)->unique()->values()->all();

        $message = match ($validated['action']) {
            'activate' => $this->bulkActive($ids, true),
            'deactivate' => $this->bulkActive($ids, false),
            'delete' => $this->bulkDelete($ids),
            'restore' => $this->bulkRestore($ids),
            'force_delete' => $this->bulkForceDelete($ids),
        };

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function trash(Request $request)
    {
        $this->ensurePermission('page_section_trash');

        $query = PageSection::onlyTrashed()->with(['page', 'sectionDefinition']);
        $this->applyFilters($query, $request, true);
        $pageSections = $query->latest('deleted_at')->paginate(15)->withQueryString();
        $filters = $this->filterOptions(true);

        if ($request->ajax()) {
            return response()->json(['html' => view('backoffice.admin.page_sections.partials.table', ['pageSections' => $pageSections, 'isTrash' => true])->render()]);
        }

        $title = 'Trashed Page Sections';
        $breadcrumb = [['text' => 'CMS', 'url' => null], ['text' => 'Page Sections', 'url' => route('admin.page_sections.index')], ['text' => 'Trash', 'url' => null]];

        return view('backoffice.admin.page_sections.trash', compact('pageSections', 'filters', 'title', 'breadcrumb'));
    }

    public function restore(int $pageSection)
    {
        $this->ensurePermission('page_section_restore');
        PageSection::onlyTrashed()->findOrFail($pageSection)->restore();

        return response()->json(['success' => true, 'message' => 'Page section restored successfully.']);
    }

    public function forceDelete(int $pageSection)
    {
        $this->ensurePermission('page_section_force_delete');
        $record = PageSection::onlyTrashed()->findOrFail($pageSection);

        if (Schema::hasTable('section_media') && DB::table('section_media')->where('page_section_id', $record->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'This page section still has section media records. Remove them before permanent deletion.'], 422);
        }

        $record->forceDelete();

        return response()->json(['success' => true, 'message' => 'Page section permanently deleted.']);
    }

    private function validateModel(Request $request, ?PageSection $pageSection = null): array
    {
        $validated = $request->validate([
            'page_id' => ['required', 'integer', Rule::exists('pages', 'id')->where(fn ($query) => $query->whereNull('deleted_at'))],
            'section_definition_id' => ['required', 'integer', Rule::exists('section_definitions', 'id')->where(fn ($query) => $query->whereNull('deleted_at'))],
            'heading' => ['nullable', 'string', 'max:190'],
            'subheading' => ['nullable', 'string', 'max:255'],
            'payload' => ['required', 'string', 'json', 'max:65000'],
            'theme' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9][a-z0-9_-]*$/i'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'is_active' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ]);

        $payload = json_decode($validated['payload'], true);

        if (! is_array($payload)) {
            throw ValidationException::withMessages(['payload' => 'Payload must decode to a JSON object or array.']);
        }

        $validated['payload'] = $payload;
        $validated['heading'] = $this->nullableTrim($validated['heading'] ?? null);
        $validated['subheading'] = $this->nullableTrim($validated['subheading'] ?? null);
        $validated['theme'] = strtolower(trim((string) $validated['theme']));
        $validated['sort_order'] = array_key_exists('sort_order', $validated) && $validated['sort_order'] !== null ? (int) $validated['sort_order'] : null;

        return $validated;
    }

    private function attributes(array $validated): array
    {
        return [
            'page_id' => (int) $validated['page_id'],
            'section_definition_id' => (int) $validated['section_definition_id'],
            'heading' => $validated['heading'],
            'subheading' => $validated['subheading'],
            'payload' => $validated['payload'],
            'theme' => $validated['theme'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => (bool) $validated['is_active'],
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ];
    }

    private function applyFilters(Builder $query, Request $request, bool $trash = false): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('heading', 'like', "%{$search}%")
                    ->orWhere('subheading', 'like', "%{$search}%")
                    ->orWhere('theme', 'like', "%{$search}%")
                    ->orWhereHas('page', fn (Builder $pageQuery) => $pageQuery->where('title', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"))
                    ->orWhereHas('sectionDefinition', fn (Builder $definitionQuery) => $definitionQuery->where('name', 'like', "%{$search}%")->orWhere('key', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('page_id')) {
            $query->where('page_id', $request->integer('page_id'));
        }

        if ($request->filled('section_definition_id')) {
            $query->where('section_definition_id', $request->integer('section_definition_id'));
        }

        if (! $trash && $request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }
    }

    private function filterOptions(bool $withTrashedParents = false): array
    {
        $pageQuery = $withTrashedParents ? Page::withTrashed() : Page::query();
        $definitionQuery = $withTrashedParents ? SectionDefinition::withTrashed() : SectionDefinition::query();

        return [
            'pages' => $pageQuery->select('id', 'title', 'slug')->ordered()->get(),
            'section_definitions' => $definitionQuery->select('id', 'key', 'name')->ordered()->get(),
        ];
    }

    private function bulkActive(array $ids, bool $active): string
    {
        PageSection::query()->whereIn('id', $ids)->update(['is_active' => $active, 'updated_at' => now()]);

        return $active ? 'Selected page sections activated.' : 'Selected page sections deactivated.';
    }

    private function bulkDelete(array $ids): string
    {
        PageSection::query()->whereIn('id', $ids)->get()->each(fn (PageSection $item) => $item->delete());

        return 'Selected page sections moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        PageSection::onlyTrashed()->whereIn('id', $ids)->get()->each(fn (PageSection $item) => $item->restore());

        return 'Selected page sections restored successfully.';
    }

    private function bulkForceDelete(array $ids): string
    {
        $blocked = [];

        PageSection::onlyTrashed()->whereIn('id', $ids)->get()->each(function (PageSection $item) use (&$blocked): void {
            if (Schema::hasTable('section_media') && DB::table('section_media')->where('page_section_id', $item->id)->exists()) {
                $blocked[] = '#' . $item->id;
                return;
            }

            $item->forceDelete();
        });

        return empty($blocked) ? 'Selected page sections permanently deleted.' : 'Deleted unreferenced sections. Skipped sections with media: ' . implode(', ', $blocked) . '.';
    }

    private function ensurePermission(string $permission): void
    {
        $admin = auth('admin')->user();
        abort_unless($admin && $admin->can($permission), 403, 'You do not have permission to perform this page section action.');
    }

    private function nullableTrim(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
