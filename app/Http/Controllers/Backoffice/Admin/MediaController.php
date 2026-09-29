<?php
namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Services\Media\MediaLibraryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MediaController extends Controller
{
    public function __construct(private readonly MediaLibraryService $mediaService)
    {}

    public function index(Request $request)
    {
        $this->ensurePermission('media_list');

        $media   = $this->mediaService->paginate($request->all(), false, false);
        $stats   = $this->mediaService->stats();
        $filters = $this->mediaService->filters(false);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.media.partials.table', compact('media'))->render(),
            ]);
        }

        $title      = 'Media Management';
        $breadcrumb = [
            ['text' => 'Site Configuration', 'url' => null],
            ['text' => 'Media Management', 'url' => route('admin.media.index')],
        ];

        return view('backoffice.admin.media.index', compact('media', 'stats', 'filters', 'title', 'breadcrumb'));
    }

    public function list(Request $request)
    {
        $this->ensurePermission('media_list');

        $trashed = $request->boolean('trash');
        $picker  = $request->boolean('picker', true);
        $media   = $this->mediaService->paginate($request->all(), $trashed, $picker);
        $filters = $this->mediaService->filters($picker);

        return response()->json([
            'success' => true,
            'data'    => collect($media->items())->map(fn(Media $item) => $this->mediaService->serialize($item))->values(),
            'meta'    => [
                'current_page' => $media->currentPage(),
                'last_page'    => $media->lastPage(),
                'per_page'     => $media->perPage(),
                'total'        => $media->total(),
                'from'         => $media->firstItem(),
                'to'           => $media->lastItem(),
            ],
            'filters' => $filters,
        ]);
    }

    public function create(Request $request)
    {
        $this->ensurePermission('media_upload');
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.media.partials.form')->render(),
        ]);
    }

    public function store(Request $request)
    {
        $this->ensurePermission('media_upload');

        $validated = $request->validate([
            'files'   => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => [
                'required',
                'file',
                'max:10240',
                'mimes:jpg,jpeg,png,webp,gif,ico,mp4,webm,pdf',
            ],
        ]);

        $uploaded = $this->mediaService->upload(auth('admin')->user(), $validated['files']);

        return response()->json([
            'success' => true,
            'message' => $uploaded->count() . ' media file(s) uploaded successfully.',
            'data'    => $uploaded->map(fn(Media $media) => $this->mediaService->serialize($media))->values(),
        ]);
    }

    public function show(Request $request, Media $media)
    {
        $this->ensurePermission('media_view');
        abort_unless($request->ajax(), 404);

        $data = $this->mediaService->serialize($media);

        return response()->json([
            'html' => view('backoffice.admin.media.partials.show', compact('media', 'data'))->render(),
        ]);
    }

    public function edit(Request $request, Media $media)
    {
        $this->ensurePermission('media_update');
        abort_unless($request->ajax(), 404);

        $data = $this->mediaService->serialize($media);

        return response()->json([
            'html' => view('backoffice.admin.media.partials.form', compact('media', 'data'))->render(),
        ]);
    }

    public function update(Request $request, Media $media)
    {
        $this->ensurePermission('media_update');

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:190'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'caption'  => ['nullable', 'string', 'max:1000'],
        ]);

        $this->mediaService->updateMetadata($media, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Media metadata updated successfully.',
        ]);
    }

    public function destroy(Media $media)
    {
        $this->ensurePermission('media_delete');
        $this->mediaService->trash($media);

        return response()->json([
            'success' => true,
            'message' => 'Media moved to trash. Physical file was retained.',
        ]);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['delete', 'restore', 'force_delete'])],
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['required', 'integer', 'distinct'],
        ]);

        $permission = match ($validated['action']) {
            'delete'       => 'media_delete',
            'restore'      => 'media_restore',
            'force_delete' => 'media_force_delete',
        };

        $this->ensurePermission($permission);

        $message = $this->mediaService->bulk($validated['action'], $validated['ids']);

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function trash(Request $request)
    {
        $this->ensurePermission('media_trash');

        $media   = $this->mediaService->paginate($request->all(), true, false);
        $filters = $this->mediaService->filters(false);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.media.partials.table', [
                    'media'   => $media,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        $title      = 'Media Trash Bin';
        $breadcrumb = [
            ['text' => 'Site Configuration', 'url' => null],
            ['text' => 'Media Management', 'url' => route('admin.media.index')],
            ['text' => 'Trash Bin', 'url' => null],
        ];

        return view('backoffice.admin.media.trash', compact('media', 'filters', 'title', 'breadcrumb'));
    }

    public function restore(int $media)
    {
        $this->ensurePermission('media_restore');
        $this->mediaService->restore($media);

        return response()->json([
            'success' => true,
            'message' => 'Media restored successfully.',
        ]);
    }

    public function forceDelete(int $media)
    {
        $this->ensurePermission('media_force_delete');
        $this->mediaService->forceDelete($media);

        return response()->json([
            'success' => true,
            'message' => 'Media permanently deleted from database and storage.',
        ]);
    }

    public function download(Media $media)
    {
        $this->ensurePermission('media_download');

        return $this->mediaService->download($media);
    }

    private function ensurePermission(string $permission): void
    {
        $admin = auth('admin')->user();

        abort_unless($admin && $admin->can($permission), 403, 'You do not have permission to perform this media action.');
    }
}
