<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Post;
use App\Services\PostMediaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PostController extends Controller
{
    public function __construct(private readonly PostMediaService $postMediaService) {}

    public function index(Request $request)
    {
        $this->ensurePermission('blog_post_list');

        $query = Post::query()->with([
            'author:id,name,email,status,deleted_at',
            'category:id,name,slug,is_active,deleted_at',
            'media',
        ]);
        $this->applyFilters($query, $request);

        $posts = $query->latest('id')->paginate(15)->withQueryString();
        [$authors, $categories] = $this->filterOptions();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.posts.partials.table', [
                    'posts' => $posts,
                    'isTrash' => false,
                ])->render(),
            ]);
        }

        $title = 'Blog Posts Management';
        $breadcrumb = [
            ['text' => 'Blog', 'url' => null],
            ['text' => 'Posts', 'url' => route('admin.posts.index')],
        ];

        return view('backoffice.admin.posts.index', compact(
            'posts',
            'authors',
            'categories',
            'title',
            'breadcrumb'
        ));
    }

    public function list(Request $request)
    {
        $this->ensurePermission('blog_post_list');

        $query = Post::query()->select(
            'id',
            'title',
            'slug',
            'status',
            'category_id',
            'published_at'
        );

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->status);
        }

        if ($request->boolean('published_only')) {
            $query->published();
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('id')->limit(100)->get(),
        ]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('blog_post_create');
        abort_unless($request->ajax(), 404);

        [$authors, $categories] = $this->formOptions();

        return response()->json([
            'html' => view('backoffice.admin.posts.partials.form', compact(
                'authors',
                'categories'
            ))->render(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('blog_post_create');

        $validated = $this->validatePost($request);
        $validated['slug'] = $this->resolveSlug($validated);

        $post = DB::transaction(function () use ($request, $validated): Post {
            $post = Post::create($this->postAttributes($validated));
            $this->postMediaService->syncFromRequest($request, $post);

            return $post;
        });

        return response()->json([
            'success' => true,
            'message' => 'Blog post created successfully.',
            'data' => ['id' => $post->id],
        ]);
    }

    public function show(Request $request, Post $post)
    {
        $this->ensurePermission('blog_post_view');
        abort_unless($request->ajax(), 404);

        $post->load(['author', 'category', 'media']);

        return response()->json([
            'html' => view('backoffice.admin.posts.partials.show', compact('post'))->render(),
        ]);
    }

    public function preview(Request $request, Post $post)
    {
        $this->ensurePermission('blog_post_preview');
        abort_unless($request->ajax(), 404);

        $post->load(['author', 'category', 'media']);

        return response()->json([
            'html' => view('backoffice.admin.posts.partials.preview', compact('post'))->render(),
        ]);
    }

    public function edit(Request $request, Post $post)
    {
        $this->ensurePermission('blog_post_update');
        abort_unless($request->ajax(), 404);

        $post->load(['author', 'category', 'media']);
        [$authors, $categories] = $this->formOptions($post);

        return response()->json([
            'html' => view('backoffice.admin.posts.partials.form', compact(
                'post',
                'authors',
                'categories'
            ))->render(),
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $this->ensurePermission('blog_post_update');

        $validated = $this->validatePost($request, $post);
        $validated['slug'] = $this->resolveSlug($validated, $post);

        DB::transaction(function () use ($request, $validated, $post): void {
            $post->update($this->postAttributes($validated, $post));
            $this->postMediaService->syncFromRequest($request, $post);
        });

        return response()->json([
            'success' => true,
            'message' => 'Blog post updated successfully.',
        ]);
    }

    public function destroy(Post $post)
    {
        $this->ensurePermission('blog_post_delete');

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Blog post moved to trash.',
        ]);
    }

    public function publish(Post $post)
    {
        $this->ensurePermission('blog_post_publish');

        $post->update([
            'status' => 'published',
            'published_at' => $post->published_at ?? now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $post->published_at?->isFuture()
                ? 'Blog post scheduled successfully.'
                : 'Blog post published successfully.',
        ]);
    }

    public function unpublish(Post $post)
    {
        $this->ensurePermission('blog_post_unpublish');

        $post->update([
            'status' => 'draft',
            'published_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Blog post moved back to draft.',
        ]);
    }

    public function duplicate(Post $post)
    {
        $this->ensurePermission('blog_post_duplicate');

        $copy = DB::transaction(function () use ($post): Post {
            $copy = Post::create([
                'author_id' => (int) auth('admin')->id(),
                'category_id' => $post->category_id,
                'title' => Str::limit($post->title . ' Copy', 190, ''),
                'slug' => $this->uniqueCopySlug($post->slug),
                'excerpt' => $post->excerpt,
                'body' => $post->body,
                'status' => 'draft',
                'published_at' => null,
                'reading_minutes' => $post->reading_minutes,
                'allow_comments' => $post->allow_comments,
            ]);

            $this->postMediaService->duplicateMedia($post, $copy);

            return $copy;
        });

        return response()->json([
            'success' => true,
            'message' => 'Blog post duplicated as draft.',
            'data' => ['id' => $copy->id],
        ]);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in([
                'publish',
                'unpublish',
                'archive',
                'delete',
                'restore',
                'force_delete',
            ])],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $permission = match ($validated['action']) {
            'publish' => 'blog_post_publish',
            'unpublish' => 'blog_post_unpublish',
            'archive' => 'blog_post_update',
            'delete' => 'blog_post_delete',
            'restore' => 'blog_post_restore',
            'force_delete' => 'blog_post_force_delete',
        };

        $this->ensurePermission($permission);

        $ids = collect($validated['ids'])
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($ids === []) {
            throw ValidationException::withMessages([
                'ids' => 'Please select at least one valid blog post.',
            ]);
        }

        $message = match ($validated['action']) {
            'publish' => $this->bulkPublish($ids),
            'unpublish' => $this->bulkUnpublish($ids),
            'archive' => $this->bulkArchive($ids),
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
        $this->ensurePermission('blog_post_trash');

        $query = Post::onlyTrashed()->with([
            'author:id,name,email,status,deleted_at',
            'category:id,name,slug,is_active,deleted_at',
            'media',
        ]);
        $this->applyFilters($query, $request);

        $posts = $query->latest('deleted_at')->paginate(15)->withQueryString();
        [$authors, $categories] = $this->filterOptions(true);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.posts.partials.table', [
                    'posts' => $posts,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        $title = 'Trashed Blog Posts';
        $breadcrumb = [
            ['text' => 'Blog', 'url' => null],
            ['text' => 'Posts', 'url' => route('admin.posts.index')],
            ['text' => 'Trash', 'url' => null],
        ];

        return view('backoffice.admin.posts.trash', compact(
            'posts',
            'authors',
            'categories',
            'title',
            'breadcrumb'
        ));
    }

    public function restore(int $post)
    {
        $this->ensurePermission('blog_post_restore');

        Post::onlyTrashed()->findOrFail($post)->restore();

        return response()->json([
            'success' => true,
            'message' => 'Blog post restored successfully.',
        ]);
    }

    public function forceDelete(int $post)
    {
        $this->ensurePermission('blog_post_force_delete');

        DB::transaction(function () use ($post): void {
            $record = Post::onlyTrashed()->findOrFail($post);
            $this->ensureForceDeletable([$record->id]);
            $this->postMediaService->purgeAll($record);
            $record->forceDelete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Blog post permanently deleted.',
        ]);
    }

    private function validatePost(Request $request, ?Post $post = null): array
    {
        return $request->validate([
            'author_id' => [
                'required',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail) use ($post): void {
                    $author = Admin::withTrashed()->find((int) $value);

                    if (! $author) {
                        $fail('The selected author does not exist.');
                        return;
                    }

                    if ($author->trashed() && (int) $post?->author_id !== (int) $author->id) {
                        $fail('A trashed admin cannot be assigned as a new post author.');
                    }
                },
            ],
            'category_id' => [
                'nullable',
                'integer',
                function (string $attribute, mixed $value, \Closure $fail) use ($post): void {
                    if ($value === null || $value === '') {
                        return;
                    }

                    $category = Category::withTrashed()->find((int) $value);

                    if (! $category) {
                        $fail('The selected category does not exist.');
                        return;
                    }

                    if ($category->trashed() && (int) $post?->category_id !== (int) $category->id) {
                        $fail('A trashed category cannot be newly assigned to a blog post.');
                    }
                },
            ],
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190'],
            'excerpt' => ['nullable', 'string', 'max:60000'],
            'body' => ['required', 'string'],
            'status' => ['required', Rule::in(array_keys(Post::STATUSES))],
            'published_at' => ['nullable', 'date'],
            'reading_minutes' => ['nullable', 'integer', 'min:1', 'max:4294967295'],
            'allow_comments' => ['required', 'boolean'],

            'featured' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'featured_media_id' => [
                'nullable',
                'integer',
                Rule::exists('media', 'id')->whereNull('deleted_at'),
            ],
            'featured_remove' => ['nullable', 'boolean'],

            'content_images' => ['nullable', 'array'],
            'content_images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'content_images_media_ids' => ['nullable', 'string', 'max:5000'],
            'content_images_remove_ids' => ['nullable', 'string', 'max:5000'],
            'content_images_clear' => ['nullable', 'boolean'],
        ]);
    }

    private function postAttributes(array $validated, ?Post $post = null): array
    {
        $status = $validated['status'];
        $publishedAt = $validated['published_at'] ?? null;

        if ($status === 'published' && ! $publishedAt) {
            $publishedAt = $post?->published_at ?? now();
        }

        if ($status === 'draft') {
            $publishedAt = null;
        }

        $body = $this->sanitizeRichText($validated['body'] ?? null);
        if ($body === null) {
            throw ValidationException::withMessages([
                'body' => 'The blog post body must contain valid readable content.',
            ]);
        }

        return [
            'author_id' => (int) $validated['author_id'],
            'category_id' => filled($validated['category_id'] ?? null)
                ? (int) $validated['category_id']
                : null,
            'title' => trim($validated['title']),
            'slug' => $validated['slug'],
            'excerpt' => $this->sanitizeRichText($validated['excerpt'] ?? null),
            'body' => $body,
            'status' => $status,
            'published_at' => $publishedAt,
            'reading_minutes' => filled($validated['reading_minutes'] ?? null)
                ? (int) $validated['reading_minutes']
                : null,
            'allow_comments' => (bool) $validated['allow_comments'],
        ];
    }

    private function resolveSlug(array $validated, ?Post $post = null): string
    {
        $providedSlug = trim((string) ($validated['slug'] ?? ''));

        if ($providedSlug === '' && $post !== null) {
            return $post->slug;
        }

        $source = $providedSlug !== '' ? $providedSlug : (string) $validated['title'];
        $baseSlug = Str::slug($source);

        if ($baseSlug === '') {
            throw ValidationException::withMessages([
                'slug' => 'A valid slug could not be generated. Enter a slug using letters, numbers and hyphens.',
            ]);
        }

        $baseSlug = mb_substr($baseSlug, 0, 190);

        if ($providedSlug !== '') {
            $this->assertSlugAvailable($baseSlug, $post?->id);
            return $baseSlug;
        }

        return $this->generateUniqueSlug($baseSlug, $post?->id);
    }

    private function generateUniqueSlug(string $baseSlug, ?int $ignoreId = null): string
    {
        $candidate = $baseSlug;
        $suffix = 2;

        while ($this->slugExists($candidate, $ignoreId)) {
            $suffixText = '-' . $suffix;
            $candidate = mb_substr(
                $baseSlug,
                0,
                190 - mb_strlen($suffixText)
            ) . $suffixText;
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
            'slug' => 'This slug is already used by another blog post, including posts currently in Trash.',
        ]);
    }

    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        return Post::withTrashed()
            ->where('slug', $slug)
            ->when(
                $ignoreId !== null,
                fn (Builder $query) => $query->where('id', '!=', $ignoreId)
            )
            ->exists();
    }

    private function uniqueCopySlug(string $baseSlug): string
    {
        $base = Str::limit(Str::slug($baseSlug . '-copy'), 170, '');

        return $this->generateUniqueSlug($base !== '' ? $base : 'post-copy');
    }

    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function (Builder $builder) use ($search): void {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', (string) $request->status);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->category_id);
        }

        if ($request->filled('author_id')) {
            $query->where('author_id', (int) $request->author_id);
        }
    }

    private function formOptions(?Post $post = null): array
    {
        $authors = Admin::query()
            ->where('status', true)
            ->orderBy('name')
            ->get();

        if ($post?->author_id && ! $authors->contains('id', $post->author_id)) {
            $author = Admin::withTrashed()->find($post->author_id);
            if ($author) {
                $authors->push($author);
            }
        }

        $categories = Category::query()->active()->ordered()->get();

        if ($post?->category_id && ! $categories->contains('id', $post->category_id)) {
            $category = Category::withTrashed()->find($post->category_id);
            if ($category) {
                $categories->push($category);
            }
        }

        return [
            $authors->sortBy('name')->values(),
            $categories->sortBy('name')->values(),
        ];
    }

    private function filterOptions(bool $withTrashed = false): array
    {
        $authorQuery = $withTrashed ? Admin::withTrashed() : Admin::query();
        $categoryQuery = $withTrashed ? Category::withTrashed() : Category::query();

        return [
            $authorQuery->orderBy('name')->get(),
            $categoryQuery->orderBy('name')->get(),
        ];
    }

    private function bulkPublish(array $ids): string
    {
        DB::transaction(function () use ($ids): void {
            Post::query()->whereIn('id', $ids)->get()->each(function (Post $post): void {
                $post->update([
                    'status' => 'published',
                    'published_at' => $post->published_at ?? now(),
                ]);
            });
        });

        return 'Selected blog posts published/scheduled successfully.';
    }

    private function bulkUnpublish(array $ids): string
    {
        Post::query()->whereIn('id', $ids)->update([
            'status' => 'draft',
            'published_at' => null,
            'updated_at' => now(),
        ]);

        return 'Selected blog posts moved to draft.';
    }

    private function bulkArchive(array $ids): string
    {
        Post::query()->whereIn('id', $ids)->update([
            'status' => 'archived',
            'updated_at' => now(),
        ]);

        return 'Selected blog posts archived.';
    }

    private function bulkDelete(array $ids): string
    {
        Post::query()->whereIn('id', $ids)->get()->each(
            fn (Post $post) => $post->delete()
        );

        return 'Selected blog posts moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        Post::onlyTrashed()->whereIn('id', $ids)->restore();

        return 'Selected blog posts restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        DB::transaction(function () use ($ids): void {
            $posts = Post::onlyTrashed()->whereIn('id', $ids)->get();
            $postIds = $posts->pluck('id')->all();

            $this->ensureForceDeletable($postIds);

            $posts->each(function (Post $post): void {
                $this->postMediaService->purgeAll($post);
                $post->forceDelete();
            });
        });

        return 'Selected blog posts permanently deleted.';
    }

    private function ensureForceDeletable(array $postIds): void
    {
        if ($postIds === [] || ! Schema::hasTable('seo_metas')) {
            return;
        }

        $postMorph = (new Post())->getMorphClass();

        if (DB::table('seo_metas')
            ->where('seoable_type', $postMorph)
            ->whereIn('seoable_id', $postIds)
            ->exists()) {
            throw ValidationException::withMessages([
                'posts' => 'One or more blog posts still have SEO metadata. Remove those SEO records before permanently deleting the posts.',
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
            $url = trim(html_entity_decode(
                $matches[3],
                ENT_QUOTES | ENT_HTML5,
                'UTF-8'
            ));

            if ($url === '' || Str::startsWith(strtolower($url), [
                'javascript:',
                'data:',
                'vbscript:',
            ])) {
                return '';
            }

            return ' ' . strtolower($matches[1]) . '=' . $matches[2] . e($url) . $matches[2];
        }, $html) ?? $html;

        return trim($html) !== '' ? trim($html) : null;
    }

    private function ensurePermission(string $permission): void
    {
        $admin = auth('admin')->user();

        abort_unless(
            $admin && $admin->can($permission),
            403,
            'You do not have permission to perform this blog post action.'
        );
    }
}
