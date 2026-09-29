<?php
namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        $query = Menu::query()->withCount('items');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($request->filled('location') && in_array($request->location, Menu::LOCATIONS, true)) {
            $query->where('location', $request->location);
        }

        $menus = $query->ordered()->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.menus.partials.table', compact('menus'))->render(),
            ]);
        }

        $title      = 'Menus Management';
        $breadcrumb = [
            ['text' => 'Site Configuration', 'url' => null],
            ['text' => 'Menus', 'url' => route('admin.menus.index')],
        ];

        return view('backoffice.admin.menus.index', compact('menus', 'title', 'breadcrumb'));
    }

    public function list(Request $request)
    {
        $query = Menu::query()->select('id', 'name', 'location', 'is_active');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'success' => true,
            'data'    => $query->ordered()->limit(100)->get(),
        ]);
    }

    public function create(Request $request)
    {
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.menus.partials.form')->render(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateModel($request);

        Menu::create([
            'name'      => $validated['name'],
            'location'  => $validated['location'],
            'is_active' => $validated['is_active'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Menu created successfully.',
        ]);
    }

    public function show(Request $request, Menu $menu)
    {
        abort_unless($request->ajax(), 404);

        $menu->load('items');

        return response()->json([
            'html' => view('backoffice.admin.menus.partials.show', [
                'menu'          => $menu,
                'parentOptions' => $menu->itemOptions(),
            ])->render(),
        ]);
    }

    public function edit(Request $request, Menu $menu)
    {
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.menus.partials.form', compact('menu'))->render(),
        ]);
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $this->validateModel($request, $menu);

        $menu->update([
            'name'      => $validated['name'],
            'location'  => $validated['location'],
            'is_active' => $validated['is_active'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Menu updated successfully.',
        ]);
    }

    public function destroy(Menu $menu)
    {
        DB::transaction(function () use ($menu): void {
            $menu->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Menu moved to trash.',
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
        $query = Menu::onlyTrashed()->withCount('items')->latest('deleted_at');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $menus = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.menus.partials.table', [
                    'menus'   => $menus,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        $title      = 'Trashed Menus';
        $breadcrumb = [
            ['text' => 'Site Configuration', 'url' => null],
            ['text' => 'Menus', 'url' => route('admin.menus.index')],
            ['text' => 'Trash', 'url' => null],
        ];

        return view('backoffice.admin.menus.trash', compact('menus', 'title', 'breadcrumb'));
    }

    public function restore(int $menu)
    {
        DB::transaction(function () use ($menu): void {
            Menu::onlyTrashed()->findOrFail($menu)->restore();
        });

        return response()->json([
            'success' => true,
            'message' => 'Menu restored successfully.',
        ]);
    }

    public function forceDelete(int $menu)
    {
        DB::transaction(function () use ($menu): void {
            Menu::onlyTrashed()->findOrFail($menu)->forceDelete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Menu permanently deleted.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Menu items management (same AJAX/Modal architecture)
    |--------------------------------------------------------------------------
    */

    public function itemCreate(Request $request, Menu $menu)
    {
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.menus.partials.item_form', [
                'menu'          => $menu,
                'item'          => null,
                'parentOptions' => $menu->itemOptions(),
                'nextSortOrder' => $this->nextSortOrder($menu),
            ])->render(),
        ]);
    }

    public function itemStore(Request $request, Menu $menu)
    {
        $validated = $this->validateItem($request, $menu);

        $menu->items()->create($this->itemAttributes($validated));

        return response()->json([
            'success' => true,
            'message' => 'Menu item created successfully.',
        ]);
    }

    public function itemEdit(Request $request, Menu $menu, MenuItem $item)
    {
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.menus.partials.item_form', [
                'menu'          => $menu,
                'item'          => $item,
                'parentOptions' => $menu->itemOptions($item),
                'nextSortOrder' => $this->nextSortOrder($menu),
            ])->render(),
        ]);
    }

    public function itemUpdate(Request $request, Menu $menu, MenuItem $item)
    {
        $validated = $this->validateItem($request, $menu, $item);

        $item->update($this->itemAttributes($validated, $item));

        return response()->json([
            'success' => true,
            'message' => 'Menu item updated successfully.',
        ]);
    }

    public function itemDestroy(Menu $menu, MenuItem $item)
    {
        DB::transaction(function () use ($item): void {
            $item->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Menu item deleted successfully.',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    private function validateModel(Request $request, ?Menu $menu = null): array
    {
        return $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'location'  => [
                'required',
                'string',
                Rule::in(Menu::LOCATIONS),
                Rule::unique('menus', 'location')->ignore($menu?->id),
            ],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function validateItem(Request $request, Menu $menu, ?MenuItem $item = null): array
    {
        $validated = $request->validate([
            'parent_id'     => [
                'nullable',
                'integer',
                Rule::exists('menu_items', 'id')
                    ->where('menu_id', $menu->id)
                    ->whereNull('deleted_at'),
            ],
            'label'         => ['required', 'string', 'max:150'],
            'link_type'     => ['required', 'string', Rule::in(array_keys(MenuItem::LINK_TYPES))],
            'url'           => ['required_if:link_type,url', 'nullable', 'string', 'max:500'],
            'linkable_type' => ['required_if:link_type,linkable', 'nullable', 'string', Rule::in(array_keys(MenuItem::linkableModels()))],
            'linkable_id'   => ['required_if:link_type,linkable', 'nullable', 'integer'],
            'icon'          => ['nullable', 'string', 'max:100'],
            'target'        => ['required', 'string', Rule::in(MenuItem::TARGETS)],
            'css_class'     => ['nullable', 'string', 'max:150'],
            'sort_order'    => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active'     => ['required', 'boolean'],
        ]);

        if ($item instanceof MenuItem) {
            $forbidden = $item->descendantIds()->merge([$item->id])->all();

            if (in_array((int) ($validated['parent_id'] ?? 0), $forbidden, true)) {
                throw ValidationException::withMessages([
                    'parent_id' => 'A menu item cannot be nested under itself or one of its children.',
                ]);
            }
        }

        if (($validated['link_type'] ?? '') === 'linkable'
            && ! empty($validated['linkable_type'])
            && ! empty($validated['linkable_id'])) {
            $exists = app($validated['linkable_type'])
                ->newQuery()
                ->whereKey((int) $validated['linkable_id'])
                ->exists();

            if (! $exists) {
                throw ValidationException::withMessages([
                    'linkable_id' => 'The selected internal link target does not exist.',
                ]);
            }
        }

        return $validated;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function itemAttributes(array $validated, ?MenuItem $item = null): array
    {
        $isLinkable = $validated['link_type'] === 'linkable';

        return [
            'parent_id'     => $validated['parent_id'] ?? null,
            'label'         => $validated['label'],
            'link_type'     => $validated['link_type'],
            'linkable_type' => $isLinkable ? ($validated['linkable_type'] ?? null) : null,
            'linkable_id'   => $isLinkable ? ($validated['linkable_id'] ?? null) : null,
            'url'           => $validated['link_type'] === 'url' ? ($validated['url'] ?? null) : null,
            'icon'          => $validated['icon'] ?? null,
            'target'        => $validated['target'],
            'css_class'     => $validated['css_class'] ?? null,
            'sort_order'    => $validated['sort_order'] ?? $item?->sort_order ?? 0,
            'is_active'     => $validated['is_active'],
        ];
    }

    private function nextSortOrder(Menu $menu): int
    {
        return (int) $menu->items()->max('sort_order') + 1;
    }

    private function bulkStatus(array $ids, bool $status): string
    {
        Menu::whereIn('id', $ids)->update(['is_active' => $status]);

        return $status ? 'Selected menus activated.' : 'Selected menus deactivated.';
    }

    private function bulkDelete(array $ids): string
    {
        DB::transaction(function () use ($ids): void {
            Menu::whereIn('id', $ids)->get()->each(fn(Menu $menu) => $menu->delete());
        });

        return 'Selected menus moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        DB::transaction(function () use ($ids): void {
            Menu::onlyTrashed()->whereIn('id', $ids)->get()->each(fn(Menu $menu) => $menu->restore());
        });

        return 'Selected menus restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        DB::transaction(function () use ($ids): void {
            Menu::onlyTrashed()->whereIn('id', $ids)->get()->each(fn(Menu $menu) => $menu->forceDelete());
        });

        return 'Selected menus permanently deleted.';
    }
}
