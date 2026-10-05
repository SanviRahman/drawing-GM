<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $this->ensurePermission('blog_category_list');

        $query = Category::query()->ordered();
        $this->applyFilters($query, $request);

        $categories = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.categories.partials.table', [
                    'categories' => $categories,
                    'isTrash' => false,
                ])->render(),
            ]);
        }

        $title = 'Blog Categories Management';
        $breadcrumb = [
            ['text' => 'Blog', 'url' => null],
            ['text' => 'Categories', 'url' => route('admin.categories.index')],
        ];

        return view('backoffice.admin.categories.index', compact('categories', 'title', 'breadcrumb'));
    }

    public function list(Request $request)
    {
        $this->ensurePermission('blog_category_list');

        $query = Category::query()->select('id', 'name', 'slug', 'is_active');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        return response()->json([
            'success' => true,
            'data' => $query->ordered()->limit(100)->get(),
        ]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('blog_category_create');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.categories.partials.form')->render(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('blog_category_create');

        $validated = $this->validateCategory($request);
        $validated['slug'] = $this->resolveSlug($validated);

        $category = Category::create($this->categoryAttributes($validated));

        return response()->json([
            'success' => true,
            'message' => 'Category created successfully.',
            'data' => ['id' => $category->id],
        ]);
    }

    public function show(Request $request, Category $category)
    {
        $this->ensurePermission('blog_category_view');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.categories.partials.show', compact('category'))->render(),
        ]);
    }

    public function edit(Request $request, Category $category)
    {
        $this->ensurePermission('blog_category_update');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.categories.partials.form', compact('category'))->render(),
        ]);
    }

    public function update(Request $request, Category $category)
    {
        $this->ensurePermission('blog_category_update');

        $validated = $this->validateCategory($request);
        $validated['slug'] = $this->resolveSlug($validated, $category);

        $category->update($this->categoryAttributes($validated));

        return response()->json([
            'success' => true,
            'message' => 'Category updated successfully.',
        ]);
    }

    public function destroy(Category $category)
    {
        $this->ensurePermission('blog_category_delete');

        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Category moved to trash.',
        ]);
    }

    public function toggle(Category $category)
    {
        $this->ensurePermission('blog_category_toggle');

        $category->update([
            'is_active' => ! $category->is_active,
        ]);

        return response()->json([
            'success' => true,
            'message' => $category->is_active ? 'Category activated.' : 'Category deactivated.',
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
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            throw ValidationException::withMessages([
                'ids' => 'Please select at least one valid category.',
            ]);
        }

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

    public function trash(Request $request)
    {
        $this->ensurePermission('blog_category_trash');

        $query = Category::onlyTrashed()->orderByDesc('deleted_at');
        $this->applyFilters($query, $request);

        $categories = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.categories.partials.table', [
                    'categories' => $categories,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        $title = 'Trashed Blog Categories';
        $breadcrumb = [
            ['text' => 'Blog', 'url' => null],
            ['text' => 'Categories', 'url' => route('admin.categories.index')],
            ['text' => 'Trash', 'url' => null],
        ];

        return view('backoffice.admin.categories.trash', compact('categories', 'title', 'breadcrumb'));
    }

    public function restore(int $category)
    {
        $this->ensurePermission('blog_category_restore');

        Category::onlyTrashed()->findOrFail($category)->restore();

        return response()->json([
            'success' => true,
            'message' => 'Category restored successfully.',
        ]);
    }

    public function forceDelete(int $category)
    {
        $this->ensurePermission('blog_category_force_delete');

        DB::transaction(function () use ($category): void {
            $record = Category::onlyTrashed()->findOrFail($category);
            $this->ensureForceDeletable([$record->id]);
            $record->forceDelete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Category permanently deleted.',
        ]);
    }

    private function validateCategory(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:60000'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function categoryAttributes(array $validated): array
    {
        return [
            'name' => trim($validated['name']),
            'slug' => $validated['slug'],
            'description' => $this->sanitizeRichText($validated['description'] ?? null),
            'is_active' => (bool) $validated['is_active'],
        ];
    }

    private function resolveSlug(array $validated, ?Category $category = null): string
    {
        $providedSlug = trim((string) ($validated['slug'] ?? ''));

        if ($providedSlug === '' && $category !== null) {
            return $category->slug;
        }

        $source = $providedSlug !== '' ? $providedSlug : (string) $validated['name'];
        $baseSlug = Str::slug($source);

        if ($baseSlug === '') {
            throw ValidationException::withMessages([
                'slug' => 'A valid slug could not be generated. Please enter the slug manually using letters, numbers and hyphens.',
            ]);
        }

        $baseSlug = mb_substr($baseSlug, 0, 190);

        if ($providedSlug !== '') {
            $this->assertSlugAvailable($baseSlug, $category?->id);

            return $baseSlug;
        }

        return $this->generateUniqueSlug($baseSlug, $category?->id);
    }

    private function generateUniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $candidate = $baseSlug;
        $suffix = 2;

        while ($this->slugExists($candidate, $ignoreId)) {
            $suffixText = '-' . $suffix;
            $candidate = mb_substr($baseSlug, 0, 190 - mb_strlen($suffixText)) . $suffixText;
            $suffix++;
        }

        return $candidate;
    }

    private function assertSlugAvailable(string $slug, ?int $ignoreId = null): void
    {
        if (! $this->slugExists($slug, $ignoreId)) {
            return;
        }

        throw ValidationException::withMessages([
            'slug' => 'This slug is already used by another category, including categories currently in Trash.',
        ]);
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Category::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->exists();
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
    }

    private function bulkStatus(array $ids, bool $status): string
    {
        $this->ensurePermission('blog_category_toggle');

        Category::whereIn('id', $ids)->update([
            'is_active' => $status,
        ]);

        return $status ? 'Selected categories activated.' : 'Selected categories deactivated.';
    }

    private function bulkDelete(array $ids): string
    {
        $this->ensurePermission('blog_category_delete');

        Category::whereIn('id', $ids)->delete();

        return 'Selected categories moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        $this->ensurePermission('blog_category_restore');

        Category::onlyTrashed()->whereIn('id', $ids)->restore();

        return 'Selected categories restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        $this->ensurePermission('blog_category_force_delete');

        DB::transaction(function () use ($ids): void {
            $categories = Category::onlyTrashed()->whereIn('id', $ids)->get();
            $this->ensureForceDeletable($categories->pluck('id')->all());

            $categories->each(fn (Category $category) => $category->forceDelete());
        });

        return 'Selected categories permanently deleted.';
    }

    private function ensureForceDeletable(array $ids): void
    {
        if ($ids === [] || ! Schema::hasTable('posts')) {
            return;
        }

        if (DB::table('posts')->whereIn('category_id', $ids)->exists()) {
            throw ValidationException::withMessages([
                'category' => 'One or more categories are still assigned to blog posts. Reassign or clear those posts before permanently deleting the category.',
            ]);
        }
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

    private function ensurePermission(string $permission): void
    {
        abort_unless(auth('admin')->user()?->can($permission), 403);
    }
}
