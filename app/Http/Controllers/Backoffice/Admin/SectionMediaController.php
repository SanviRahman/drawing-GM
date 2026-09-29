<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\SectionMedia;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SectionMediaController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePermission('section_media_list');

        $query = SectionMedia::query()->with(['pageSection.page', 'pageSection.sectionDefinition', 'media']);
        $this->applyFilters($query, $request);
        $sectionMedia = $query->ordered()->paginate(15)->withQueryString();
        $filters = $this->filterOptions();

        if ($request->ajax()) {
            return response()->json(['html' => view('backoffice.admin.section_media.partials.table', compact('sectionMedia'))->render()]);
        }

        $title = 'Section Media';
        $breadcrumb = [['text' => 'CMS', 'url' => null], ['text' => 'Section Media', 'url' => route('admin.section_media.index')]];

        return view('backoffice.admin.section_media.index', compact('sectionMedia', 'filters', 'title', 'breadcrumb'));
    }

    public function create(Request $request)
    {
        $this->ensurePermission('section_media_create');
        abort_unless($request->ajax(), 404);

        $pageSections = $this->pageSectionOptions();
        $selectedPageSectionId = $request->integer('page_section_id') ?: null;

        return response()->json(['html' => view('backoffice.admin.section_media.partials.form', compact('pageSections', 'selectedPageSectionId'))->render()]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('section_media_create');
        $validated = $this->validateModel($request);

        if (! array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = ((int) SectionMedia::query()->where('page_section_id', $validated['page_section_id'])->where('role', $validated['role'])->max('sort_order')) + 10;
        }

        $sectionMedia = SectionMedia::create($this->attributes($validated));

        return response()->json(['success' => true, 'message' => 'Section media attached successfully.', 'data' => ['id' => $sectionMedia->id]]);
    }

    public function show(Request $request, SectionMedia $sectionMedia)
    {
        $this->ensurePermission('section_media_view');
        abort_unless($request->ajax(), 404);

        $sectionMedia->load(['pageSection.page', 'pageSection.sectionDefinition', 'media']);

        return response()->json(['html' => view('backoffice.admin.section_media.partials.show', compact('sectionMedia'))->render()]);
    }

    public function edit(Request $request, SectionMedia $sectionMedia)
    {
        $this->ensurePermission('section_media_update');
        abort_unless($request->ajax(), 404);

        $sectionMedia->load('media');
        $pageSections = $this->pageSectionOptions($sectionMedia->page_section_id);
        $selectedPageSectionId = null;

        return response()->json(['html' => view('backoffice.admin.section_media.partials.form', compact('sectionMedia', 'pageSections', 'selectedPageSectionId'))->render()]);
    }

    public function update(Request $request, SectionMedia $sectionMedia)
    {
        $this->ensurePermission('section_media_update');
        $validated = $this->validateModel($request, $sectionMedia);
        $sectionMedia->update($this->attributes($validated));

        return response()->json(['success' => true, 'message' => 'Section media updated successfully.']);
    }

    public function destroy(SectionMedia $sectionMedia)
    {
        $this->ensurePermission('section_media_delete');
        $sectionMedia->delete();

        return response()->json(['success' => true, 'message' => 'Section media moved to trash.']);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['delete', 'restore', 'force_delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $permission = match ($validated['action']) {
            'delete' => 'section_media_delete',
            'restore' => 'section_media_restore',
            'force_delete' => 'section_media_force_delete',
        };

        $this->ensurePermission($permission);
        $ids = collect($validated['ids'])->map(fn ($id) => (int) $id)->unique()->values()->all();

        $message = match ($validated['action']) {
            'delete' => $this->bulkDelete($ids),
            'restore' => $this->bulkRestore($ids),
            'force_delete' => $this->bulkForceDelete($ids),
        };

        return response()->json(['success' => true, 'message' => $message]);
    }

    public function trash(Request $request)
    {
        $this->ensurePermission('section_media_trash');

        $query = SectionMedia::onlyTrashed()->with(['pageSection.page', 'pageSection.sectionDefinition', 'media']);
        $this->applyFilters($query, $request);
        $sectionMedia = $query->latest('deleted_at')->paginate(15)->withQueryString();
        $filters = $this->filterOptions(true);

        if ($request->ajax()) {
            return response()->json(['html' => view('backoffice.admin.section_media.partials.table', ['sectionMedia' => $sectionMedia, 'isTrash' => true])->render()]);
        }

        $title = 'Trashed Section Media';
        $breadcrumb = [['text' => 'CMS', 'url' => null], ['text' => 'Section Media', 'url' => route('admin.section_media.index')], ['text' => 'Trash', 'url' => null]];

        return view('backoffice.admin.section_media.trash', compact('sectionMedia', 'filters', 'title', 'breadcrumb'));
    }

    public function restore(int $sectionMedia)
    {
        $this->ensurePermission('section_media_restore');
        $record = SectionMedia::onlyTrashed()->findOrFail($sectionMedia);
        $this->ensureRestorable($record);
        $record->restore();

        return response()->json(['success' => true, 'message' => 'Section media restored successfully.']);
    }

    public function forceDelete(int $sectionMedia)
    {
        $this->ensurePermission('section_media_force_delete');
        SectionMedia::onlyTrashed()->findOrFail($sectionMedia)->forceDelete();

        return response()->json(['success' => true, 'message' => 'Section media permanently deleted.']);
    }

    private function validateModel(Request $request, ?SectionMedia $sectionMedia = null): array
    {
        $validated = $request->validate([
            'page_section_id' => ['required', 'integer', Rule::exists('page_sections', 'id')->where(fn ($query) => $query->whereNull('deleted_at'))],
            'media_id' => ['required', 'integer'],
            'role' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9][a-z0-9_-]*$/i'],
            'caption_override' => ['nullable', 'string', 'max:60000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:4294967295'],
        ]);

        $validated['page_section_id'] = (int) $validated['page_section_id'];
        $validated['media_id'] = (int) $validated['media_id'];
        $validated['role'] = strtolower(trim((string) $validated['role']));
        $validated['caption_override'] = $this->sanitizeRichText($validated['caption_override'] ?? null);
        $validated['sort_order'] = array_key_exists('sort_order', $validated) && $validated['sort_order'] !== null ? (int) $validated['sort_order'] : null;

        $safeMediaExists = Media::query()->pickerSafe()->whereKey($validated['media_id'])->exists();

        if (! $safeMediaExists) {
            throw ValidationException::withMessages(['media_id' => 'Select an available non-private media item from the Media Picker.']);
        }

        $duplicate = SectionMedia::withTrashed()
            ->where('page_section_id', $validated['page_section_id'])
            ->where('media_id', $validated['media_id'])
            ->where('role', $validated['role'])
            ->when($sectionMedia, fn (Builder $query) => $query->where('id', '!=', $sectionMedia->getKey()))
            ->first();

        if ($duplicate) {
            $message = $duplicate->trashed()
                ? 'This media assignment already exists in Trash. Restore that record instead of creating a duplicate.'
                : 'This media item is already attached to the selected page section with the same role.';

            throw ValidationException::withMessages(['media_id' => $message]);
        }

        return $validated;
    }

    private function attributes(array $validated): array
    {
        return [
            'page_section_id' => $validated['page_section_id'],
            'media_id' => $validated['media_id'],
            'role' => $validated['role'],
            'caption_override' => $validated['caption_override'],
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
        ];
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('role', 'like', "%{$search}%")
                    ->orWhere('caption_override', 'like', "%{$search}%")
                    ->orWhereHas('media', fn (Builder $media) => $media->where('name', 'like', "%{$search}%")->orWhere('file_name', 'like', "%{$search}%"))
                    ->orWhereHas('pageSection', function (Builder $section) use ($search): void {
                        $section->where('heading', 'like', "%{$search}%")
                            ->orWhere('subheading', 'like', "%{$search}%")
                            ->orWhereHas('page', fn (Builder $page) => $page->where('title', 'like', "%{$search}%")->orWhere('slug', 'like', "%{$search}%"))
                            ->orWhereHas('sectionDefinition', fn (Builder $definition) => $definition->where('name', 'like', "%{$search}%")->orWhere('key', 'like', "%{$search}%"));
                    });
            });
        }

        if ($request->filled('page_id')) {
            $pageId = (int) $request->page_id;
            $query->whereHas('pageSection', fn (Builder $section) => $section->where('page_id', $pageId));
        }

        if ($request->filled('page_section_id')) {
            $query->where('page_section_id', (int) $request->page_section_id);
        }

        if ($request->filled('role')) {
            $query->where('role', trim((string) $request->role));
        }
    }

    private function filterOptions(bool $trash = false): array
    {
        $rolesQuery = $trash ? SectionMedia::onlyTrashed() : SectionMedia::query();

        return [
            'pages' => Page::query()->select('id', 'title')->ordered()->get(),
            'page_sections' => PageSection::query()->with(['page:id,title', 'sectionDefinition:id,name,key'])->ordered()->get(['id', 'page_id', 'section_definition_id', 'heading', 'sort_order']),
            'roles' => $rolesQuery->select('role')->whereNotNull('role')->distinct()->orderBy('role')->pluck('role'),
        ];
    }

    private function pageSectionOptions(?int $includeId = null)
    {
        return PageSection::withTrashed()
            ->with(['page:id,title,slug', 'sectionDefinition:id,name,key'])
            ->where(function (Builder $query) use ($includeId): void {
                $query->whereNull('deleted_at');

                if ($includeId) {
                    $query->orWhereKey($includeId);
                }
            })
            ->ordered()
            ->get();
    }

    private function ensureRestorable(SectionMedia $record): void
    {
        if (! PageSection::query()->whereKey($record->page_section_id)->exists()) {
            throw ValidationException::withMessages(['section_media' => 'Restore the parent page section before restoring this media assignment.']);
        }

        if (! Media::query()->pickerSafe()->whereKey($record->media_id)->exists()) {
            throw ValidationException::withMessages(['section_media' => 'The referenced media is deleted, private, or unavailable. Restore/replace the media first.']);
        }
    }

    private function bulkDelete(array $ids): string
    {
        SectionMedia::query()->whereIn('id', $ids)->get()->each(fn (SectionMedia $item) => $item->delete());

        return 'Selected section media moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        $restored = 0;
        $blocked = 0;

        SectionMedia::onlyTrashed()->whereIn('id', $ids)->get()->each(function (SectionMedia $item) use (&$restored, &$blocked): void {
            try {
                $this->ensureRestorable($item);
                $item->restore();
                $restored++;
            } catch (ValidationException) {
                $blocked++;
            }
        });

        return $blocked > 0
            ? "{$restored} section media record(s) restored; {$blocked} skipped because the parent section or media is unavailable."
            : 'Selected section media restored successfully.';
    }

    private function bulkForceDelete(array $ids): string
    {
        SectionMedia::onlyTrashed()->whereIn('id', $ids)->get()->each(fn (SectionMedia $item) => $item->forceDelete());

        return 'Selected section media permanently deleted.';
    }

    private function sanitizeRichText(mixed $value): ?string
    {
        $html = trim((string) ($value ?? ''));

        if ($html === '') {
            return null;
        }

        $allowedTags = '<p><br><strong><b><em><i><u><s><ul><ol><li><blockquote><h1><h2><h3><h4><h5><h6><a><hr><pre><code><span>';
        $html = strip_tags($html, $allowedTags);
        $html = preg_replace('/\s(?:on\w+|style|srcdoc|formaction)\s*=\s*(["\']).*?\1/isu', '', $html) ?? $html;
        $html = preg_replace('/\s(?:on\w+|style|srcdoc|formaction)\s*=\s*[^\s>]+/iu', '', $html) ?? $html;
        $html = preg_replace_callback('/\s(href)\s*=\s*(["\'])(.*?)\2/isu', function (array $matches): string {
            $url = trim(html_entity_decode($matches[3], ENT_QUOTES | ENT_HTML5, 'UTF-8'));

            if ($url === '' || Str::startsWith(strtolower($url), ['javascript:', 'data:', 'vbscript:'])) {
                return '';
            }

            return ' href=' . $matches[2] . e($url) . $matches[2];
        }, $html) ?? $html;

        return trim($html) !== '' ? trim($html) : null;
    }

    private function ensurePermission(string $permission): void
    {
        $admin = auth('admin')->user();
        abort_unless($admin && $admin->can($permission), 403, 'You do not have permission to perform this section media action.');
    }
}
