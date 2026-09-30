<?php
namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GalleryController extends Controller
{
    public function index(Request $request)
    {
        $query = Gallery::query()->with('items')->latest('id');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('layout', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', (bool) $request->status);
        }

        $galleries = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.galleries.partials.table', compact('galleries'))->render(),
            ]);
        }

        $title = 'Gallery Management';

        $breadcrumb = [
            ['text' => 'Content', 'url' => null],
            ['text' => 'Gallery', 'url' => route('admin.galleries.index')],
        ];

        return view('backoffice.admin.galleries.index', compact('galleries', 'title', 'breadcrumb'));
    }

    public function list(Request $request)
    {
        $query = Gallery::query()->select('id', 'name', 'layout', 'is_active');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where('name', 'like', "%{$search}%");
        }

        return response()->json([
            'success' => true,
            'data'    => $query->latest('id')->limit(50)->get(),
        ]);
    }

    public function create(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.galleries.partials.form')->render(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateGallery($request);

        Gallery::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Gallery created successfully.',
        ]);
    }

    public function show(Request $request, Gallery $gallery)
    {
        abort_unless($request->ajax(), 404);

        $gallery->load('items');

        return response()->json([
            'html' => view('backoffice.admin.galleries.partials.show', compact('gallery'))->render(),
        ]);
    }

    public function edit(Request $request, Gallery $gallery)
    {
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.galleries.partials.form', compact('gallery'))->render(),
        ]);
    }

    public function update(Request $request, Gallery $gallery)
    {
        $validated = $this->validateGallery($request, $gallery);

        $gallery->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Gallery updated successfully.',
        ]);
    }

    public function destroy(Gallery $gallery)
    {
        $gallery->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gallery moved to trash.',
        ]);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required',Rule::in(['active','inactive','delete','restore','force_delete',]),],
            'ids'    => ['required','array','min:1'],
            'ids.*'  => ['required','integer','distinct',],
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
        $query = Gallery::onlyTrashed()->with('items')->latest('deleted_at');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where('name', 'like', "%{$search}%");
        }

        $galleries = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.galleries.partials.table', [
                    'galleries' => $galleries,
                    'isTrash'   => true,
                ])->render(),
            ]);
        }

        $title = 'Trashed Galleries';

        $breadcrumb = [
            ['text' => 'Content', 'url' => null],
            ['text' => 'Gallery', 'url' => route('admin.galleries.index')],
            ['text' => 'Trash', 'url' => null],
        ];

        return view('backoffice.admin.galleries.trash', compact('galleries', 'title', 'breadcrumb'));
    }

    public function restore(int $gallery)
    {
        Gallery::onlyTrashed()->findOrFail($gallery)->restore();

        return response()->json([
            'success' => true,
            'message' => 'Gallery restored successfully.',
        ]);
    }

    public function forceDelete(int $gallery)
    {
        Gallery::onlyTrashed()->findOrFail($gallery)->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Gallery permanently deleted.',
        ]);
    }

    private function validateGallery(Request $request, ?Gallery $gallery = null): array
    {
        return $request->validate([
            'name'      => [
                'required', 'string', 'max:255'],
            'layout'    => ['nullable', 'string', 'max:100'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function bulkStatus(array $ids, bool $status): string
    {
        Gallery::whereIn('id', $ids)->update([
            'is_active' => $status,
        ]);

        return $status ? 'Selected galleries activated.' : 'Selected galleries deactivated.';
    }

    private function bulkDelete(array $ids): string
    {
        Gallery::whereIn('id', $ids)->delete();

        return 'Selected galleries moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        Gallery::onlyTrashed()->whereIn('id', $ids)->restore();

        return 'Selected galleries restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        Gallery::onlyTrashed()->whereIn('id', $ids)->forceDelete();

        return 'Selected galleries permanently deleted.';
    }
}
