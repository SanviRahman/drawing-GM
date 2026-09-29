<?php
namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::query()->withCount(['permissions', 'users']);

        if ($request->filled('search')) {
            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('guard_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('guard_name')) {
            $query->where('guard_name', $request->guard_name);
        }

        $roles = $query->latest('id')->paginate(15)->withQueryString();

        $guards = Role::query()
            ->select('guard_name')
            ->distinct()
            ->orderBy('guard_name')
            ->pluck('guard_name');

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.roles.partials.table', [
                    'roles'   => $roles,
                    'isTrash' => false,
                ])->render(),
            ]);
        }

        $title      = 'Roles & Permissions Management';
        $breadcrumb = [
            ['text' => 'Access Control', 'url' => null],
            ['text' => 'Roles List', 'url' => route('admin.roles.index')],
        ];

        return view('backoffice.admin.roles.index', compact('roles', 'guards', 'title', 'breadcrumb'));
    }

    public function list(Request $request)
    {
        $query = Role::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . trim((string) $request->search) . '%');
        }

        if ($request->filled('guard_name')) {
            $query->where('guard_name', $request->guard_name);
        }

        return response()->json([
            'success' => true,
            'data'    => $query->orderBy('name')->limit(100)->get(['id', 'name', 'guard_name']),
        ]);
    }

    public function getPermissions(Request $request)
    {
        $guardName = (string) $request->input('guard_name', 'admin');
        $search    = trim((string) $request->input('search', ''));
        $roleId    = $request->integer('role_id');

        if ($request->has('selected_permissions')) {
            $selectedPermissions = collect((array) $request->input('selected_permissions', []))
                ->filter(fn($name) => is_string($name) && $name !== '')
                ->unique()
                ->values()
                ->all();
        } else {
            $selectedPermissions = [];

            if ($roleId) {
                $role = Role::find($roleId);

                if ($role) {
                    $selectedPermissions = $role->permissions->pluck('name')->all();
                }
            }
        }

        $permissions = Permission::query()
            ->where('guard_name', $guardName)
            ->when($search !== '', fn($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('group_name')
            ->orderBy('name')
            ->get()
            ->groupBy(fn(Permission $permission) => $permission->group_name ?: 'General');

        return response()->json([
            'success' => true,
            'html'    => view('backoffice.admin.roles.partials.permissions_list', [
                'permissions'         => $permissions,
                'selectedPermissions' => $selectedPermissions,
            ])->render(),
        ]);
    }

    public function create(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $guards = Permission::query()
            ->select('guard_name')
            ->distinct()
            ->orderBy('guard_name')
            ->pluck('guard_name')
            ->prepend('admin')
            ->unique()
            ->values();

        $defaultGuard = 'admin';

        $permissions = Permission::query()
            ->where('guard_name', $defaultGuard)
            ->orderBy('group_name')
            ->orderBy('name')
            ->get()
            ->groupBy(fn(Permission $permission) => $permission->group_name ?: 'General');

        return response()->json([
            'html' => view('backoffice.admin.roles.partials.form', compact('guards', 'permissions', 'defaultGuard'))->render(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateRole($request);

        $role = Role::create([
            'name'       => $validated['name'],
            'guard_name' => $validated['guard_name'],
        ]);

        $role->syncPermissions(
            $this->validatedPermissionNames(
                $validated['permissions'] ?? [],
                $validated['guard_name']
            )
        );

        $this->forgetPermissionCache();

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully.',
        ]);
    }

    public function show(Request $request, Role $role)
    {
        abort_unless($request->ajax(), 404);

        $role->load('permissions');
        $groupedPermissions = $role->permissions->groupBy(fn($permission) => $permission->group_name ?: 'General');

        return response()->json([
            'html' => view('backoffice.admin.roles.partials.show', compact('role', 'groupedPermissions'))->render(),
        ]);
    }

    public function edit(Request $request, Role $role)
    {
        abort_unless($request->ajax(), 404);

        $guards = Permission::query()
            ->select('guard_name')
            ->distinct()
            ->orderBy('guard_name')
            ->pluck('guard_name')
            ->prepend($role->guard_name)
            ->unique()
            ->values();

        $role->load('permissions');
        $rolePermissions = $role->permissions->pluck('name')->all();

        $permissions = Permission::query()
            ->where('guard_name', $role->guard_name)
            ->orderBy('group_name')
            ->orderBy('name')
            ->get()
            ->groupBy(fn(Permission $permission) => $permission->group_name ?: 'General');

        return response()->json([
            'html' => view('backoffice.admin.roles.partials.form', compact('role', 'guards', 'permissions', 'rolePermissions'))->render(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $validated = $this->validateRole($request, $role);

        if ($role->isSuperAdmin() && ($validated['name'] !== 'super_admin' || $validated['guard_name'] !== 'admin')) {
            return response()->json([
                'success' => false,
                'message' => 'The super_admin role name and guard cannot be changed.',
            ], 422);
        }

        $role->update([
            'name'       => $validated['name'],
            'guard_name' => $validated['guard_name'],
        ]);

        $role->syncPermissions(
            $this->validatedPermissionNames(
                $validated['permissions'] ?? [],
                $validated['guard_name']
            )
        );

        $this->forgetPermissionCache();

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully.',
        ]);
    }

    public function destroy(Role $role)
    {
        if ($role->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'The super_admin role cannot be deleted.',
            ], 403);
        }

        $role->delete();
        $this->forgetPermissionCache();

        return response()->json([
            'success' => true,
            'message' => 'Role moved to trash.',
        ]);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['delete', 'restore', 'force_delete'])],
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['required', 'integer', 'distinct'],
        ]);

        $ids = collect($validated['ids'])
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $message = match ($validated['action']) {
            'delete'       => $this->bulkDelete($ids),
            'restore'      => $this->bulkRestore($ids),
            'force_delete' => $this->bulkForceDelete($ids),
        };

        $this->forgetPermissionCache();

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }

    public function trash(Request $request)
    {
        $query = Role::onlyTrashed()
            ->withCount(['permissions', 'users'])
            ->latest('deleted_at');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . trim((string) $request->search) . '%');
        }

        $roles = $query->paginate(15)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.roles.partials.table', [
                    'roles'   => $roles,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        $title      = 'Trash Roles & Permissions';
        $breadcrumb = [
            ['text' => 'Access Control', 'url' => null],
            ['text' => 'Roles List', 'url' => route('admin.roles.index')],
            ['text' => 'Trash', 'url' => null],
        ];

        return view('backoffice.admin.roles.trash', compact('roles', 'title', 'breadcrumb'));
    }

    public function restore(int $role)
    {
        $model = Role::onlyTrashed()->findOrFail($role);
        $model->restore();

        $this->forgetPermissionCache();

        return response()->json([
            'success' => true,
            'message' => 'Role restored successfully.',
        ]);
    }

    public function forceDelete(int $role)
    {
        $model = Role::onlyTrashed()->findOrFail($role);

        if ($model->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'The super_admin role cannot be permanently deleted.',
            ], 403);
        }

        $model->forceDelete();
        $this->forgetPermissionCache();

        return response()->json([
            'success' => true,
            'message' => 'Role permanently deleted.',
        ]);
    }

    private function validateRole(Request $request, ?Role $role = null): array
    {
        $guardName = (string) $request->input('guard_name', 'admin');

        return $request->validate([
            'name'          => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->where(fn($query) => $query->where('guard_name', $guardName))
                    ->ignore($role?->id),
            ],
            'guard_name'    => ['required', 'string', 'max:255'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['required', 'string', 'distinct'],
        ]);
    }

    private function validatedPermissionNames(array $permissionNames, string $guardName): array
    {
        $requested = collect($permissionNames)
            ->filter()
            ->map(fn($name) => (string) $name)
            ->unique()
            ->values();

        if ($requested->isEmpty()) {
            return [];
        }

        $valid = Permission::query()
            ->where('guard_name', $guardName)
            ->whereIn('name', $requested->all())
            ->pluck('name')
            ->values();

        if ($valid->count() !== $requested->count()) {
            throw ValidationException::withMessages([
                'permissions' => 'One or more permissions are invalid for the selected guard.',
            ]);
        }

        return $valid->all();
    }

    private function bulkDelete(array $ids): string
    {
        Role::query()
            ->whereIn('id', $ids)
            ->where(function ($query) {
                $query->where('name', '!=', 'super_admin')
                    ->orWhere('guard_name', '!=', 'admin');
            })
            ->delete();

        return 'Selected roles moved to trash.';
    }

    private function bulkRestore(array $ids): string
    {
        Role::onlyTrashed()->whereIn('id', $ids)->restore();

        return 'Selected roles restored.';
    }

    private function bulkForceDelete(array $ids): string
    {
        Role::onlyTrashed()
            ->whereIn('id', $ids)
            ->where(function ($query) {
                $query->where('name', '!=', 'super_admin')
                    ->orWhere('guard_name', '!=', 'admin');
            })
            ->forceDelete();

        return 'Selected roles permanently deleted.';
    }

    private function forgetPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
