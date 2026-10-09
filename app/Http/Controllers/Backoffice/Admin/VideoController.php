<?php
namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Video;
use App\Services\VideoMediaService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VideoController extends Controller
{
    public function __construct(private readonly VideoMediaService $videoMediaService)
    {}
    public function index(Request $request)
    {
        $this->ensurePermission('video_list');
        $query = Video::query()->with('media')->ordered();
        $this->applyFilters($query, $request);
        $videos = $query->paginate(15)->withQueryString();
        if ($request->ajax()) {
            return response()->json(['html' => view('backoffice.admin.videos.partials.table', ['videos' => $videos, 'isTrash' => false])->render()]);
        }

        $title      = 'Videos Management';
        $breadcrumb = [['text' => 'Content', 'url' => null], ['text' => 'Videos', 'url' => route('admin.videos.index')]];
        return view('backoffice.admin.videos.index', compact('videos', 'title', 'breadcrumb'));
    }
    public function list(Request $request)
    {
        $this->ensurePermission('video_list');
        $query = Video::query()->with('media')->select('id', 'title', 'source_type', 'provider', 'provider_video_id', 'source_url', 'processing_status', 'sort_order', 'is_active');
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(fn(Builder $builder) => $builder->where('title', 'like', "%{$search}%")->orWhere('provider_video_id', 'like', "%{$search}%")->orWhere('source_url', 'like', "%{$search}%"));
        }
        if ($request->boolean('active_only')) {
            $query->active();
        }

        return response()->json(['success' => true, 'data' => $query->ordered()->limit(100)->get()->map(fn(Video $video) => ['id' => $video->id, 'title' => $video->title, 'source_type' => $video->resolved_source_type, 'source_label' => $video->source_type_label, 'is_active' => $video->is_active])]);
    }
    public function create(Request $request)
    {
        $this->ensurePermission('video_create');
        abort_unless($request->ajax(), 404);
        return response()->json(['html' => view('backoffice.admin.videos.partials.form')->render()]);
    }
    public function store(Request $request)
    {
        $this->ensurePermission('video_create');
        $validated  = $this->validateVideo($request);
        $normalized = $this->normalizeSources($request, null);
        $this->ensureSourcePermissions($request, $normalized);
        $this->ensureAtLeastOneSource($request, null, $normalized);
        if (! array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = $this->nextSortOrder();
        }

        $video = DB::transaction(function () use ($request, $validated, $normalized): Video {
            $video = Video::create($this->videoAttributes($validated, $normalized));
            $this->videoMediaService->syncFromRequest($request, $video);
            $video->load('media');
            $video->forceFill(['source_type' => $video->resolved_source_type ?? $video->source_type])->save();
            return $video;
        });
        return response()->json(['success' => true, 'message' => 'Video created successfully.', 'data' => ['id' => $video->id]]);
    }
    public function show(Request $request, Video $video)
    {
        $this->ensurePermission('video_view');
        abort_unless($request->ajax(), 404);
        $video->load('media');
        return response()->json(['html' => view('backoffice.admin.videos.partials.show', compact('video'))->render()]);
    }
    public function edit(Request $request, Video $video)
    {
        $this->ensurePermission('video_update');
        abort_unless($request->ajax(), 404);
        $video->load('media');
        return response()->json(['html' => view('backoffice.admin.videos.partials.form', compact('video'))->render()]);
    }
    public function update(Request $request, Video $video)
    {
        $this->ensurePermission('video_update');
        $video->load('media');
        $validated  = $this->validateVideo($request);
        $normalized = $this->normalizeSources($request, $video);
        $this->ensureSourcePermissions($request, $normalized);
        $this->ensureAtLeastOneSource($request, $video, $normalized);
        if (! array_key_exists('sort_order', $validated) || $validated['sort_order'] === null) {
            $validated['sort_order'] = $video->sort_order;
        }

        DB::transaction(function () use ($request, $validated, $normalized, $video): void {
            $video->update($this->videoAttributes($validated, $normalized));
            $this->videoMediaService->syncFromRequest($request, $video);
            $video->refresh()->load('media');
            $video->forceFill(['source_type' => $video->resolved_source_type ?? $video->source_type])->save();
        });
        return response()->json(['success' => true, 'message' => 'Video updated successfully.']);
    }
    public function destroy(Video $video)
    {
        $this->ensurePermission('video_delete');
        $video->delete();
        return response()->json(['success' => true, 'message' => 'Video moved to trash.']);
    }
    public function toggle(Video $video)
    {
        $this->ensurePermission('video_toggle');
        $video->update(['is_active' => ! $video->is_active]);
        return response()->json(['success' => true, 'message' => $video->is_active ? 'Video activated.' : 'Video deactivated.']);
    }
    public function reorder(Request $request)
    {
        $this->ensurePermission('video_reorder');
        $validated = $request->validate(['items' => ['required', 'array', 'min:1'], 'items.*.id' => ['required', 'integer', 'distinct', 'exists:videos,id'], 'items.*.sort_order' => ['required', 'integer', 'min:0', 'max:2147483647']]);
        DB::transaction(function () use ($validated): void {foreach ($validated['items'] as $item) {
            Video::whereKey((int) $item['id'])->update(['sort_order' => (int) $item['sort_order']]);
        }
        });
        return response()->json(['success' => true, 'message' => 'Video order updated successfully.']);
    }
    public function trash(Request $request)
    {
        $this->ensurePermission('video_trash');
        $query = Video::onlyTrashed()->with('media')->orderByDesc('deleted_at');
        $this->applyFilters($query, $request);
        $videos = $query->paginate(15)->withQueryString();
        if ($request->ajax()) {
            return response()->json(['html' => view('backoffice.admin.videos.partials.table', ['videos' => $videos, 'isTrash' => true])->render()]);
        }

        $title      = 'Trashed Videos';
        $breadcrumb = [['text' => 'Content', 'url' => null], ['text' => 'Videos', 'url' => route('admin.videos.index')], ['text' => 'Trash', 'url' => null]];
        return view('backoffice.admin.videos.trash', compact('videos', 'title', 'breadcrumb'));
    }
    public function restore(int $video)
    {
        $this->ensurePermission('video_restore');
        Video::onlyTrashed()->findOrFail($video)->restore();
        return response()->json(['success' => true, 'message' => 'Video restored successfully.']);
    }
    public function forceDelete(int $video)
    {
        $this->ensurePermission('video_force_delete');
        DB::transaction(function () use ($video): void {$record = Video::onlyTrashed()->findOrFail($video); $this->videoMediaService->purgeAll($record); $record->forceDelete();});
        return response()->json(['success' => true, 'message' => 'Video permanently deleted.']);
    }
    public function multipleAction(Request $request)
    {
        $validated = $request->validate(['action' => ['required', Rule::in(['activate', 'deactivate', 'delete', 'restore', 'force_delete'])], 'ids' => ['required', 'array', 'min:1'], 'ids.*' => ['required', 'integer', 'distinct']]);
        $ids       = collect($validated['ids'])->map(fn($id) => (int) $id)->unique()->values()->all();
        $message   = match ($validated['action']) {'activate' => $this->bulkStatus($ids, true), 'deactivate' => $this->bulkStatus($ids, false), 'delete' => $this->bulkDelete($ids), 'restore' => $this->bulkRestore($ids), 'force_delete' => $this->bulkForceDelete($ids)};
        return response()->json(['success' => true, 'message' => $message]);
    }
    private function validateVideo(Request $request): array
    {
        return $request->validate([
            'title'                 => ['required', 'string', 'max:190'],
            'caption'               => ['nullable', 'string', 'max:60000'],
            'youtube_input'         => ['nullable', 'string', 'max:2048'],
            'youtube_remove'        => ['nullable', 'boolean'],
            'embed_input'           => ['nullable', 'string', 'max:5000'],
            'embed_remove'          => ['nullable', 'boolean'],
            'duration_seconds'      => ['nullable', 'integer', 'min:0', 'max:4294967295'],
            'autoplay'              => ['required', 'boolean'],
            'muted'                 => ['required', 'boolean'],
            'controls'              => ['required', 'boolean'],
            'loop'                  => ['required', 'boolean'],
            'sort_order'            => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'is_active'             => ['required', 'boolean'],
            'video_file'            => ['nullable', 'file', 'mimes:mp4,webm', 'mimetypes:video/mp4,video/webm', 'max:102400'],
            'video_file_media_id'   => ['nullable', 'integer', Rule::exists('media', 'id')->whereNull('deleted_at'), $this->reusableVideoRule()],
            'video_file_remove'     => ['nullable', 'boolean'],
            'video_poster'          => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
            'video_poster_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')->whereNull('deleted_at'), $this->reusableImageRule()],
            'video_poster_remove'   => ['nullable', 'boolean'],
        ]);
    }
    private function normalizeSources(Request $request, ?Video $video): array
    {
        $youtubeFieldSubmitted = $request->exists('youtube_input') || $request->exists('youtube_remove');
        $embedFieldSubmitted   = $request->exists('embed_input') || $request->exists('embed_remove');
        if ($video && ! $youtubeFieldSubmitted) {
            $youtubeId = $video->provider_video_id;
        } else {
            $youtubeInput = $request->boolean('youtube_remove') ? '' : trim((string) $request->input('youtube_input', ''));
            $youtubeId    = $youtubeInput !== '' ? $this->extractYoutubeId($youtubeInput) : null;
            if ($youtubeInput !== '' && ! $youtubeId) {
                throw ValidationException::withMessages(['youtube_input' => 'Enter a valid YouTube URL or 11-character YouTube video ID.']);
            }

        }
        if ($video && ! $embedFieldSubmitted) {
            $embedUrl = $video->source_url;
        } else {
            $embedInput = $request->boolean('embed_remove') ? '' : trim((string) $request->input('embed_input', ''));
            $embedUrl   = $embedInput !== '' ? $this->normalizeEmbedInput($embedInput) : null;
            if ($embedInput !== '' && ! $embedUrl) {
                throw ValidationException::withMessages(['embed_input' => 'Use a valid YouTube/Vimeo embed URL or iframe code. Only approved embed providers are allowed.']);
            }

        }
        $hasUploadReplacement = $request->hasFile('video_file') || $request->filled('video_file_media_id');
        $hasExistingUpload    = (bool) ($video?->hasMedia(Video::VIDEO_COLLECTION));
        $hasUpload            = $hasUploadReplacement || ($hasExistingUpload && ! $request->boolean('video_file_remove'));
        $sourceType           = $youtubeId ? 'youtube' : ($embedUrl ? 'embed' : ($hasUpload ? 'upload' : null));
        return ['youtube_id' => $youtubeId, 'embed_url' => $embedUrl, 'has_upload' => $hasUpload, 'source_type' => $sourceType];
    }
    private function ensureAtLeastOneSource(Request $request, ?Video $video, array $normalized): void
    {
        if ($normalized['source_type']) {
            return;
        }

        throw ValidationException::withMessages(['youtube_input' => 'Add at least one source: YouTube first, embedded video second, or recording/upload video third.']);
    }
    private function ensureSourcePermissions(Request $request, array $normalized): void
    {
        if ($request->filled('youtube_input') || $request->boolean('youtube_remove') || $request->filled('embed_input') || $request->boolean('embed_remove')) {
            $this->ensurePermission('video_embed');
        }

        if ($request->hasFile('video_file') || $request->filled('video_file_media_id') || $request->boolean('video_file_remove')) {
            $this->ensurePermission('video_upload');
        }

    }
    private function videoAttributes(array $validated, array $normalized): array
    {
        $autoplay = (bool) $validated['autoplay'];
        // Browsers allow autoplay reliably only when muted. Persist the setting
        // instead of silently discarding the user's selected Autoplay value.
        $muted    = $autoplay ? true : (bool) $validated['muted'];
        $controls = (bool) $validated['controls'];
        $provider = $normalized['youtube_id'] ? 'youtube' : ($normalized['embed_url'] ? $this->detectEmbedProvider($normalized['embed_url']) : null);
        return ['title' => trim($validated['title']), 'caption' => $this->sanitizeRichText($validated['caption'] ?? null), 'source_type' => $normalized['source_type'] ?? 'upload', 'provider' => $provider, 'provider_video_id' => $normalized['youtube_id'], 'source_url' => $normalized['embed_url'], 'duration_seconds' => $validated['duration_seconds'] ?? null, 'autoplay' => $autoplay, 'muted' => $muted, 'controls' => $controls, 'loop' => (bool) $validated['loop'], 'processing_status' => 'ready', 'processing_error' => null, 'sort_order' => (int) ($validated['sort_order'] ?? 0), 'is_active' => (bool) $validated['is_active']];
    }
    private function applyFilters(Builder $query, Request $request): void
    {
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function (Builder $builder) use ($search): void {$builder->where('title', 'like', "%{$search}%")->orWhere('caption', 'like', "%{$search}%")->orWhere('provider_video_id', 'like', "%{$search}%")->orWhere('source_url', 'like', "%{$search}%");});
        }
        if ($request->filled('source_type')) {
            $query->where('source_type', (string) $request->source_type);
        }

        if ($request->filled('processing_status')) {
            $query->where('processing_status', (string) $request->processing_status);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

    }
    private function extractYoutubeId(string $input): ?string
    {
        $input = trim(html_entity_decode($input, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $input)) {
            return $input;
        }

        if (str_contains($input, '<iframe')) {
            if (! preg_match('/\bsrc\s*=\s*(["\'])(.*?)\1/is', $input, $match)) {
                return null;
            }

            $input = trim($match[2]);
        }
        $parts = parse_url($input);
        if (! is_array($parts) || empty($parts['host'])) {
            return null;
        }

        $host = strtolower($parts['host']);
        $path = trim((string) ($parts['path'] ?? ''), '/');
        if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $id = explode('/', $path)[0] ?? '';
            return preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? $id : null;
        }
        if (! in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true)) {
            return null;
        }

        parse_str((string) ($parts['query'] ?? ''), $query);
        $candidate = (string) ($query['v'] ?? '');
        if (! $candidate && preg_match('~(?:embed|shorts|live)/([A-Za-z0-9_-]{11})~', $path, $match)) {
            $candidate = $match[1];
        }

        return preg_match('/^[A-Za-z0-9_-]{11}$/', $candidate) ? $candidate : null;
    }
    private function normalizeEmbedInput(string $input): ?string
    {
        $input = trim(html_entity_decode($input, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
        if (str_contains($input, '<iframe')) {
            if (! preg_match('/\bsrc\s*=\s*(["\'])(.*?)\1/is', $input, $match)) {
                return null;
            }

            $input = trim($match[2]);
        }
        if (! filter_var($input, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts  = parse_url($input);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host   = strtolower((string) ($parts['host'] ?? ''));
        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        if (in_array($host, ['youtu.be', 'www.youtu.be', 'youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtube-nocookie.com', 'www.youtube-nocookie.com'], true)) {
            $id = $this->extractYoutubeId($input);
            return $id ? 'https://www.youtube-nocookie.com/embed/' . $id : null;
        }
        if (in_array($host, ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'], true)) {
            $path = (string) ($parts['path'] ?? '');
            if (! preg_match('~(?:/video)?/([0-9]{5,20})(?:/|$)~', $path, $match)) {
                return null;
            }

            return 'https://player.vimeo.com/video/' . $match[1];
        }
        return null;
    }
    private function detectEmbedProvider(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if (str_contains($host, 'youtube')) {
            return 'youtube';
        }

        if (str_contains($host, 'vimeo')) {
            return 'vimeo';
        }

        return null;
    }
    private function reusableVideoRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {if ($value === null || $value === '') {
            return;
        }

            $media = Media::query()->find((int) $value);if (! $media || ! $media->isPickerSafe() || ! in_array((string) $media->mime_type, ['video/mp4', 'video/webm'], true)) {
                $fail('The selected media must be an active reusable MP4 or WebM video.');
            }
        };
    }
    private function reusableImageRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {if ($value === null || $value === '') {
            return;
        }

            $media = Media::query()->find((int) $value);if (! $media || ! $media->isPickerSafe() || ! $media->isImage()) {
                $fail('The selected media must be an active reusable image.');
            }
        };
    }
    private function nextSortOrder(): int
    {
        return ((int) Video::withTrashed()->max('sort_order')) + 10;
    }
    private function bulkStatus(array $ids, bool $status): string
    {
        $this->ensurePermission('video_toggle');
        Video::whereIn('id', $ids)->update(['is_active' => $status]);return $status ? 'Selected videos activated.' : 'Selected videos deactivated.';
    }
    private function bulkDelete(array $ids): string
    {
        $this->ensurePermission('video_delete');
        Video::whereIn('id', $ids)->delete();return 'Selected videos moved to trash.';
    }
    private function bulkRestore(array $ids): string
    {
        $this->ensurePermission('video_restore');
        Video::onlyTrashed()->whereIn('id', $ids)->restore();return 'Selected videos restored.';
    }
    private function bulkForceDelete(array $ids): string
    {
        $this->ensurePermission('video_force_delete');
        DB::transaction(function () use ($ids): void {Video::onlyTrashed()->whereIn('id', $ids)->get()->each(function (Video $video): void {$this->videoMediaService->purgeAll($video); $video->forceDelete();});});return 'Selected videos permanently deleted.';
    }
    private function sanitizeRichText(mixed $value): ?string
    {
        $html = trim((string) ($value ?? ''));if ($html === '') {
            return null;
        }

        $allowedTags = '<p><br><strong><b><em><i><u><s><ul><ol><li><blockquote><h1><h2><h3><h4><h5><h6><a><hr><pre><code><span><table><thead><tbody><tfoot><tr><th><td><img>';
        $html        = strip_tags($html, $allowedTags);
        $html        = preg_replace('/\s(?:on\w+|style|srcdoc|formaction)\s*=\s*(["\']).*?\1/isu', '', $html) ?? $html;
        $html        = preg_replace('/\s(?:on\w+|style|srcdoc|formaction)\s*=\s*[^\s>]+/iu', '', $html) ?? $html;
        $html        = preg_replace_callback('/\s(href|src)\s*=\s*(["\'])(.*?)\2/isu', function (array $matches): string {$url = trim(html_entity_decode($matches[3], ENT_QUOTES | ENT_HTML5, 'UTF-8'));if ($url === '' || Str::startsWith(strtolower($url), ['javascript:', 'data:', 'vbscript:'])) {
            return '';
        }
            return ' ' . strtolower($matches[1]) . '=' . $matches[2] . e($url) . $matches[2];}, $html) ?? $html;return trim($html) !== '' ? trim($html) : null;
    }
    private function ensurePermission(string $permission): void
    {
        abort_unless(auth('admin')->user()?->can($permission), 403);
    }
}
