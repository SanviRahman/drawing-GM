<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\SectionDefinition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SectionDefinitionController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePermission('section_definition_list');

        $query = SectionDefinition::query();
        $this->applyFilters($query, $request);

        $sectionDefinitions = $query->ordered()->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json(['html' => view('backoffice.admin.section_definitions.partials.table', compact('sectionDefinitions'))->render()]);
        }

        $title = 'Section Definitions';
        $breadcrumb = [['text' => 'CMS', 'url' => null], ['text' => 'Sections', 'url' => route('admin.section_definitions.index')]];

        return view('backoffice.admin.section_definitions.index', compact('sectionDefinitions', 'title', 'breadcrumb'));
    }

    public function list(Request $request)
    {
        $this->ensurePermission('section_definition_list');

        $query = SectionDefinition::query()->select('id', 'key', 'name', 'is_active');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(fn (Builder $builder) => $builder->where('key', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
        }

        if (! $request->boolean('include_inactive')) {
            $query->active();
        }

        return response()->json(['success' => true, 'data' => $query->ordered()->limit(100)->get()]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('section_definition_create');
        abort_unless($request->ajax(), 404);

        return response()->json(['html' => view('backoffice.admin.section_definitions.partials.form')->render()]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('section_definition_create');
        $validated = $this->validateModel($request);

        $sectionDefinition = SectionDefinition::create($this->attributes($validated));

        return response()->json(['success' => true, 'message' => 'Section definition created successfully.', 'data' => ['id' => $sectionDefinition->id]]);
    }

    public function show(Request $request, SectionDefinition $sectionDefinition)
    {
        $this->ensurePermission('section_definition_view');
        abort_unless($request->ajax(), 404);

        return response()->json(['html' => view('backoffice.admin.section_definitions.partials.show', compact('sectionDefinition'))->render()]);
    }

    public function edit(Request $request, SectionDefinition $sectionDefinition)
    {
        $this->ensurePermission('section_definition_update');
        abort_unless($request->ajax(), 404);

        return response()->json(['html' => view('backoffice.admin.section_definitions.partials.form', compact('sectionDefinition'))->render()]);
    }

    public function update(Request $request, SectionDefinition $sectionDefinition)
    {
        $this->ensurePermission('section_definition_update');
        $validated = $this->validateModel($request, $sectionDefinition);

        $sectionDefinition->update($this->attributes($validated));

        return response()->json(['success' => true, 'message' => 'Section definition updated successfully.']);
    }

    public function destroy(SectionDefinition $sectionDefinition)
    {
        $this->ensurePermission('section_definition_delete');
        $sectionDefinition->delete();

        return response()->json(['success' => true, 'message' => 'Section definition moved to trash.']);
    }

    public function toggle(SectionDefinition $sectionDefinition)
    {
        $this->ensurePermission('section_definition_toggle');
        $sectionDefinition->update(['is_active' => ! $sectionDefinition->is_active]);

        return response()->json(['success' => true, 'message' => $sectionDefinition->fresh()->is_active ? 'Section definition activated.' : 'Section definition deactivated.']);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['activate', 'deactivate', 'delete', 'restore', 'force_delete'])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $permission = match ($validated['action']) {
            'activate', 'deactivate' => 'section_definition_toggle',
            'delete' => 'section_definition_delete',
            'restore' => 'section_definition_restore',
            'force_delete' => 'section_definition_force_delete',
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
        $this->ensurePermission('section_definition_trash');

        $query = SectionDefinition::onlyTrashed();
        $this->applyFilters($query, $request, true);
        $sectionDefinitions = $query->latest('deleted_at')->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json(['html' => view('backoffice.admin.section_definitions.partials.table', ['sectionDefinitions' => $sectionDefinitions, 'isTrash' => true])->render()]);
        }

        $title = 'Trashed Section Definitions';
        $breadcrumb = [['text' => 'CMS', 'url' => null], ['text' => 'Sections', 'url' => route('admin.section_definitions.index')], ['text' => 'Trash', 'url' => null]];

        return view('backoffice.admin.section_definitions.trash', compact('sectionDefinitions', 'title', 'breadcrumb'));
    }

    public function restore(int $sectionDefinition)
    {
        $this->ensurePermission('section_definition_restore');
        SectionDefinition::onlyTrashed()->findOrFail($sectionDefinition)->restore();

        return response()->json(['success' => true, 'message' => 'Section definition restored successfully.']);
    }

    public function forceDelete(int $sectionDefinition)
    {
        $this->ensurePermission('section_definition_force_delete');

        $record = SectionDefinition::onlyTrashed()->findOrFail($sectionDefinition);

        if (Schema::hasTable('page_sections') && DB::table('page_sections')->where('section_definition_id', $record->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'This section definition is still referenced by page sections and cannot be permanently deleted.'], 422);
        }

        $record->forceDelete();

        return response()->json(['success' => true, 'message' => 'Section definition permanently deleted.']);
    }

    private function validateModel(Request $request, ?SectionDefinition $sectionDefinition = null): array
    {
        $validated = $request->validate([
            'key' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9]*(?:_[a-z0-9]+)*$/', Rule::unique('section_definitions', 'key')->ignore($sectionDefinition?->id)],
            'name' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:60000'],
            'schema_json' => ['required', 'string', 'json', 'max:60000'],
            'is_active' => ['required', 'boolean'],
        ]);

        $decodedSchema = json_decode($validated['schema_json'], true);

        if (! is_array($decodedSchema)) {
            throw ValidationException::withMessages(['schema_json' => 'Schema JSON must decode to a JSON object or array.']);
        }

        $validated['schema_json'] = $decodedSchema;
        $validated['description'] = $this->sanitizeRichText($validated['description'] ?? null);

        return $validated;
    }

    private function attributes(array $validated): array
    {
        return [
            'key' => $validated['key'],
            'name' => trim((string) $validated['name']),
            'description' => $validated['description'],
            'schema_json' => $validated['schema_json'],
            'is_active' => (bool) $validated['is_active'],
        ];
    }

    private function applyFilters(Builder $query, Request $request, bool $trash = false): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('key', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (! $trash && $request->filled('active')) {
            $query->where('is_active', $request->boolean('active'));
        }
    }

    private function bulkActive(array $ids, bool $active): string
    {
        SectionDefinition::query()->whereIn('id', $ids)->update(['is_active' => $active, 'updated_at' => now()]);

        return $active ? 'Selected section definitions activated.' : 'Selected section definitions deactivated.';
    }

    private function bulkDelete(array $ids): string
    {
        SectionDefinition::query()->whereIn('id', $ids)->get()->each(fn (SectionDefinition $item) => $item->delete());

        return 'Selected section definitions moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        SectionDefinition::onlyTrashed()->whereIn('id', $ids)->get()->each(fn (SectionDefinition $item) => $item->restore());

        return 'Selected section definitions restored successfully.';
    }

    private function bulkForceDelete(array $ids): string
    {
        $blocked = [];

        SectionDefinition::onlyTrashed()->whereIn('id', $ids)->get()->each(function (SectionDefinition $item) use (&$blocked): void {
            if (Schema::hasTable('page_sections') && DB::table('page_sections')->where('section_definition_id', $item->id)->exists()) {
                $blocked[] = $item->key;
                return;
            }

            $item->forceDelete();
        });

        return empty($blocked) ? 'Selected section definitions permanently deleted.' : 'Deleted unreferenced definitions. Skipped referenced keys: ' . implode(', ', $blocked) . '.';
    }

    private function ensurePermission(string $permission): void
    {
        $admin = auth('admin')->user();
        abort_unless($admin && $admin->can($permission), 403, 'You do not have permission to perform this section definition action.');
    }

    private function sanitizeRichText(?string $html): ?string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return null;
        }

        $allowedTags = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'ul', 'ol', 'li', 'blockquote', 'h2', 'h3', 'h4', 'h5', 'h6', 'a', 'table', 'thead', 'tbody', 'tr', 'th', 'td'];
        $allowedAttributes = ['href', 'target', 'rel', 'title', 'colspan', 'rowspan'];

        if (! class_exists(\DOMDocument::class)) {
            return strip_tags($html, '<p><br><strong><b><em><i><u><s><ul><ol><li><blockquote><h2><h3><h4><h5><h6><a><table><thead><tbody><tr><th><td>');
        }

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?><div id="section-description-root">' . $html . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $root = $dom->getElementById('section-description-root');

        if (! $root) {
            return null;
        }

        $this->sanitizeDomNode($root, $allowedTags, $allowedAttributes);

        $output = '';
        foreach ($root->childNodes as $child) {
            $output .= $dom->saveHTML($child);
        }

        return trim($output) !== '' ? trim($output) : null;
    }

    private function sanitizeDomNode(\DOMNode $parent, array $allowedTags, array $allowedAttributes): void
    {
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if ($node instanceof \DOMElement) {
                $tag = strtolower($node->tagName);

                if (! in_array($tag, $allowedTags, true)) {
                    while ($node->firstChild) {
                        $parent->insertBefore($node->firstChild, $node);
                    }
                    $parent->removeChild($node);
                    continue;
                }

                foreach (iterator_to_array($node->attributes ?? []) as $attribute) {
                    $name = strtolower($attribute->name);
                    if (! in_array($name, $allowedAttributes, true) || str_starts_with($name, 'on')) {
                        $node->removeAttribute($attribute->name);
                    }
                }

                if ($node->hasAttribute('href')) {
                    $href = trim($node->getAttribute('href'));
                    $scheme = strtolower((string) parse_url($href, PHP_URL_SCHEME));
                    if ($scheme !== '' && ! in_array($scheme, ['http', 'https', 'mailto', 'tel'], true)) {
                        $node->removeAttribute('href');
                    }
                }

                if ($node->hasAttribute('target') && ! in_array($node->getAttribute('target'), ['_self', '_blank'], true)) {
                    $node->removeAttribute('target');
                }

                if ($node->getAttribute('target') === '_blank') {
                    $node->setAttribute('rel', 'noopener noreferrer');
                }

                $this->sanitizeDomNode($node, $allowedTags, $allowedAttributes);
            }
        }
    }
}
