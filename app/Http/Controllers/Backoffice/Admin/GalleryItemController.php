<?php
namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\GalleryItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GalleryItemController extends Controller
{
    public function index(Request $request)
    {
        $query = GalleryItem::query()->with('gallery')->latest('id');

        if ($request->filled('gallery_id')) {
            $query->where('gallery_id', $request->gallery_id);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('caption', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', (bool) $request->status);
        }

        $items = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.gallery_items.partials.table', compact('items'))->render(),
            ]);
        }

        $galleries = Gallery::orderBy('name')->pluck('name', 'id');

        $title = 'Gallery Items Management';

        $breadcrumb = [
            ['text' => 'Content', 'url' => null],
            ['text' => 'Gallery Items', 'url' => route('admin.gallery_items.index')],
        ];

        return view('backoffice.admin.gallery_items.index', compact('items', 'galleries', 'title', 'breadcrumb'));
    }

    public function list(Request $request)
    {
        $query = GalleryItem::query()->select('id', 'gallery_id', 'title', 'item_type', 'is_active');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where('title', 'like', "%{$search}%");
        }

        return response()->json([
            'success' => true,
            'data'    => $query->latest('id')->limit(50)->get(),
        ]);
    }

    public function create(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $galleries = Gallery::orderBy('name')->pluck('name', 'id');

        return response()->json([
            'html' => view('backoffice.admin.gallery_items.partials.form', compact('galleries'))->render(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateGalleryItem($request);

        $galleryItem = GalleryItem::create($validated);

        $this->uploadGalleryMedia($request, $galleryItem);

        return response()->json([
            'success' => true,
            'message' => 'Gallery item created successfully.',
        ]);
    }

    public function show(Request $request, GalleryItem $galleryItem)
    {
        abort_unless($request->ajax(), 404);

        $galleryItem->load('gallery');

        return response()->json([
            'html' => view('backoffice.admin.gallery_items.partials.show', compact('galleryItem'))->render(),
        ]);
    }

    public function edit(Request $request, GalleryItem $galleryItem)
    {
        abort_unless($request->ajax(), 404);

        $galleries = Gallery::orderBy('name')->pluck('name', 'id');

        return response()->json([
            'html' => view('backoffice.admin.gallery_items.partials.form', compact('galleryItem', 'galleries'))->render(),
        ]);
    }

    public function update(Request $request, GalleryItem $galleryItem)
    {
        $validated = $this->validateGalleryItem($request);

        $galleryItem->update($validated);

        $this->uploadGalleryMedia($request, $galleryItem);

        return response()->json([
            'success' => true,
            'message' => 'Gallery item updated successfully.',
        ]);
    }

    public function destroy(GalleryItem $galleryItem)
    {
        $galleryItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gallery item moved to trash.',
        ]);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['active', 'inactive', 'delete', 'restore', 'force_delete'])],
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['required', 'integer', 'distinct'],
        ]);

        $ids = collect($validated['ids'])
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $message = match ($validated['action']) {
            'active'       => $this->bulkStatus($ids, true),
            'inactive'     => $this->bulkStatus($ids, false),
            'delete'       => $this->bulkDelete($ids),
            'restore'      => $this->bulkRestore($ids),
            'force_delete' => $this->bulkForceDelete($ids),
        };

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function trash(Request $request)
    {
        $query = GalleryItem::onlyTrashed()->with('gallery')->latest('deleted_at');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where('title', 'like', "%{$search}%");
        }

        $items = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.gallery_items.partials.table', [
                    'items'   => $items,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        $title = 'Trashed Gallery Items';

        $breadcrumb = [
            ['text' => 'Content', 'url' => null],
            ['text' => 'Gallery Items', 'url' => route('admin.gallery_items.index')],
            ['text' => 'Trash', 'url' => null],
        ];

        return view('backoffice.admin.gallery_items.trash', compact('items', 'title', 'breadcrumb'));
    }

    public function restore(int $galleryItem)
    {
        GalleryItem::onlyTrashed()->findOrFail($galleryItem)->restore();

        return response()->json([
            'success' => true,
            'message' => 'Gallery item restored successfully.',
        ]);
    }

    public function forceDelete(int $galleryItem)
    {
        GalleryItem::onlyTrashed()->findOrFail($galleryItem)->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Gallery item permanently deleted.',
        ]);
    }

    private function validateGalleryItem(Request $request, ?GalleryItem $galleryItem = null): array
    {
        return $request->validate([
            'gallery_id' => ['required', 'exists:galleries,id'],
            'item_type'  => ['required', 'string', 'max:50'],
            'title'      => ['nullable', 'string', 'max:255'],
            'caption'    => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'is_active'  => ['required', 'boolean'],
            'image'      => ['nullable', 'image', 'max:5120'],
            'before'     => ['nullable', 'image', 'max:5120'],
            'after'      => ['nullable', 'image', 'max:5120'],
        ]);
    }

    private function bulkStatus(array $ids, bool $status): string
    {
        GalleryItem::whereIn('id', $ids)->update([
            'is_active' => $status,
        ]);

        return $status ? 'Selected gallery items activated.' : 'Selected gallery items deactivated.';
    }

    private function bulkDelete(array $ids): string
    {
        GalleryItem::whereIn('id', $ids)->delete();

        return 'Selected gallery items moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        GalleryItem::onlyTrashed()->whereIn('id', $ids)->restore();

        return 'Selected gallery items restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        GalleryItem::onlyTrashed()->whereIn('id', $ids)->forceDelete();

        return 'Selected gallery items permanently deleted.';
    }

    private function uploadGalleryMedia(Request $request, GalleryItem $galleryItem)
    {
        foreach (['image', 'before', 'after'] as $collection) {
            $mediaId = $request->input($collection . '_media_id');
            if ($mediaId) {
                $galleryItem->clearMediaCollection($collection);
                $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($mediaId);
                if ($media && file_exists($media->getPath())) {
                    $galleryItem->addMedia($media->getPath())->toMediaCollection($collection);
                }
            } elseif ($request->hasFile($collection)) {
                $galleryItem->clearMediaCollection($collection);
                $galleryItem->addMediaFromRequest($collection)->toMediaCollection($collection);
            }
        }
    }
}
