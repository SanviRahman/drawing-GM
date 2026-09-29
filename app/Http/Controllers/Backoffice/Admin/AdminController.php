<?php
namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Media;
use App\Models\Role;
use App\Services\AdminAvatarService;
use App\Services\Media\MediaLibraryService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function __construct(
        private readonly AdminAvatarService $avatarService,
        private readonly MediaLibraryService $mediaLibraryService,
    ) {}

    public function index(Request $request)
    {
        $query = Admin::query()->with('roles');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $role = (string) $request->role;

            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('name', $role)->where('guard_name', 'admin');
            });
        }

        $admins = $query->latest('id')->paginate(15)->withQueryString();

        $roles = Role::where('guard_name', 'admin')->orderBy('name')->pluck('name');

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.admins.partials.table', compact('admins'))->render(),
            ]);
        }

        $title      = 'Admin Users Management';
        $breadcrumb = [
            ['text' => 'Access Control', 'url' => null],
            ['text' => 'Admin Users', 'url' => route('admin.admins.index')],
        ];

        return view('backoffice.admin.admins.index', compact('admins', 'roles', 'title', 'breadcrumb'));
    }

    public function list(Request $request)
    {
        $query = Admin::query()->select('id', 'name', 'username', 'email', 'status');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return response()->json([
            'success' => true,
            'data'    => $query->latest('id')->limit(50)->get(),
        ]);
    }

    public function create(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $roles = Role::where('guard_name', 'admin')->orderBy('name')->pluck('name');

        return response()->json([
            'html' => view('backoffice.admin.admins.partials.form', compact('roles'))->render(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateAdmin($request);

        $admin = Admin::create([
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => $validated['password'],
            'status'   => $validated['status'],
        ]);

        $admin->syncRoles($validated['roles'] ?? []);
        $this->avatarService->syncFromRequest($request, $admin);

        return response()->json([
            'success' => true,
            'message' => 'Admin user created successfully.',
        ]);
    }

    public function show(Request $request, Admin $admin)
    {
        abort_unless($request->ajax(), 404);

        $admin->load('roles', 'media');

        return response()->json([
            'html' => view('backoffice.admin.admins.partials.show', compact('admin'))->render(),
        ]);
    }

    public function edit(Request $request, Admin $admin)
    {
        abort_unless($request->ajax(), 404);

        $admin->load('media');
        $roles = Role::where('guard_name', 'admin')->orderBy('name')->pluck('name');

        return response()->json([
            'html' => view('backoffice.admin.admins.partials.form', compact('admin', 'roles'))->render(),
        ]);
    }

    public function update(Request $request, Admin $admin)
    {
        $validated = $this->validateAdmin($request, $admin);

        $data = [
            'name'     => $validated['name'],
            'username' => $validated['username'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'status'   => $validated['status'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $admin->update($data);
        $admin->syncRoles($validated['roles'] ?? []);
        $this->avatarService->syncFromRequest($request, $admin);

        return response()->json([
            'success' => true,
            'message' => 'Admin user updated successfully.',
        ]);
    }

    public function destroy(Admin $admin)
    {
        if ((int) $admin->id === (int) auth('admin')->id()) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot delete yourself.',
            ], 403);
        }

        $admin->delete();

        return response()->json([
            'success' => true,
            'message' => 'Admin moved to trash.',
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
            ->reject(fn($id) => $id === (int) auth('admin')->id())
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Action cannot be performed on yourself.',
            ], 422);
        }

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
        $query = Admin::onlyTrashed()->with('roles')->latest('deleted_at');

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $admins = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.admins.partials.table', [
                    'admins'  => $admins,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        $title      = 'Trashed Admin Users';
        $breadcrumb = [
            ['text' => 'Access Control', 'url' => null],
            ['text' => 'Admin Users', 'url' => route('admin.admins.index')],
            ['text' => 'Trash', 'url' => null],
        ];

        return view('backoffice.admin.admins.trash', compact('admins', 'title', 'breadcrumb'));
    }

    public function restore(int $admin)
    {
        Admin::onlyTrashed()->findOrFail($admin)->restore();

        return response()->json([
            'success' => true,
            'message' => 'Admin restored successfully.',
        ]);
    }

    public function forceDelete(int $admin)
    {
        $model = Admin::onlyTrashed()->findOrFail($admin);
        $this->mediaLibraryService->reassignLibraryMedia($model, auth('admin')->user());
        $this->avatarService->purgeAll($model);
        $model->forceDelete();

        return response()->json([
            'success' => true,
            'message' => 'Admin permanently deleted.',
        ]);
    }

    private function validateAdmin(Request $request, ?Admin $admin = null): array
    {
        return $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'username'       => ['required', 'string', 'max:255', Rule::unique('admins', 'username')->ignore($admin?->id)],
            'email'          => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin?->id)],
            'phone'          => ['nullable', 'string', 'max:20', Rule::unique('admins', 'phone')->ignore($admin?->id)],
            'password'       => [$admin ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
            'status'         => ['required', 'boolean'],
            'roles'          => ['nullable', 'array'],
            'roles.*'        => [
                'string',
                Rule::exists('roles', 'name')->where(fn($query) => $query->where('guard_name', 'admin')->whereNull('deleted_at')),
            ],
            'photo'          => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
            'photo_media_id' => ['nullable', 'integer', Rule::exists('media', 'id')->whereNull('deleted_at'), $this->reusableImageRule()],
        ]);
    }

    private function bulkStatus(array $ids, bool $status): string
    {
        Admin::whereIn('id', $ids)->update(['status' => $status]);

        return $status ? 'Selected admins activated.' : 'Selected admins deactivated.';
    }

    private function bulkDelete(array $ids): string
    {
        Admin::whereIn('id', $ids)->delete();

        return 'Selected admins moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        Admin::onlyTrashed()->whereIn('id', $ids)->restore();

        return 'Selected admins restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        Admin::onlyTrashed()->whereIn('id', $ids)->get()->each(function (Admin $admin) {
            $this->mediaLibraryService->reassignLibraryMedia($admin, auth('admin')->user());
            $this->avatarService->purgeAll($admin);
            $admin->forceDelete();
        });

        return 'Selected admins permanently deleted.';
    }
    private function reusableImageRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            if ($value === null || $value === '') {
                return;
            }

            $media = Media::query()->find((int) $value);

            if (! $media || ! $media->isPickerSafe() || ! $media->isImage()) {
                $fail('The selected media must be an active reusable image.');
            }
        };
    }

}
