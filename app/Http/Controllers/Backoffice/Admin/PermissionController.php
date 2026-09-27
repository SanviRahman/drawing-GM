<?php

namespace App\Http\Controllers\Backoffice\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $permissions = Permission::query()
            ->search($request->input('search'))
            ->when(
                $request->filled('guard_name'),
                fn ($query) => $query->byGuard((string) $request->input('guard_name'))
            )
            ->when(
                $request->filled('group_name'),
                fn ($query) => $query->byGroup((string) $request->input('group_name'))
            )
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $guards = Permission::query()
            ->select('guard_name')
            ->distinct()
            ->orderBy('guard_name')
            ->pluck('guard_name');

        $groups = Permission::query()
            ->select('group_name')
            ->distinct()
            ->whereNotNull('group_name')
            ->orderBy('group_name')
            ->pluck('group_name');

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.permissions.partials.table', [
                    'permissions' => $permissions,
                    'isTrash' => false,
                ])->render(),
            ]);
        }

        $title = 'Permissions Management';

        $breadcrumb = [
            ['text' => 'Permissions List', 'url' => route('admin.permissions.index')],
        ];

        return view('backoffice.admin.permissions.index', compact(
            'permissions',
            'guards',
            'groups',
            'title',
            'breadcrumb'
        ));
    }

    public function list(Request $request)
    {
        $permissions = Permission::query()
            ->search($request->input('search'))
            ->when(
                $request->filled('guard_name'),
                fn ($query) => $query->byGuard((string) $request->input('guard_name'))
            )
            ->orderBy('group_name')
            ->orderBy('name')
            ->limit(100)
            ->get([
                'id',
                'name',
                'guard_name',
                'group_name',
            ]);

        return response()->json([
            'success' => true,
            'data' => $permissions,
        ]);
    }

    public function create(Request $request)
    {
        abort_unless($request->ajax(), 404);

        $groups = Permission::query()
            ->select('group_name')
            ->distinct()
            ->whereNotNull('group_name')
            ->orderBy('group_name')
            ->pluck('group_name');

        return response()->json([
            'html' => view('backoffice.admin.permissions.partials.form', compact('groups'))->render(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePermission($request);

        $permission = Permission::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'],
            'group_name' => $validated['group_name'],
        ]);

        $this->forgetPermissionCache();

        return response()->json([
            'success' => true,
            'message' => 'Permission created successfully.',
            'data' => [
                'id' => $permission->id,
            ],
        ]);
    }

    public function show(Request $request, Permission $permission)
    {
        abort_unless($request->ajax(), 404);

        return response()->json([
            'html' => view('backoffice.admin.permissions.partials.show', compact('permission'))->render(),
        ]);
    }

    public function edit(Request $request, Permission $permission)
    {
        abort_unless($request->ajax(), 404);

        $groups = Permission::query()
            ->select('group_name')
            ->distinct()
            ->whereNotNull('group_name')
            ->orderBy('group_name')
            ->pluck('group_name');

        return response()->json([
            'html' => view('backoffice.admin.permissions.partials.form', compact(
                'permission',
                'groups'
            ))->render(),
        ]);
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $this->validatePermission($request, $permission);

        $this->guardAgainstAssignedGuardChange($permission, $validated['guard_name']);

        $permission->update([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'],
            'group_name' => $validated['group_name'],
        ]);

        $this->forgetPermissionCache();

        return response()->json([
            'success' => true,
            'message' => 'Permission updated successfully.',
        ]);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        $this->forgetPermissionCache();

        return response()->json([
            'success' => true,
            'message' => 'Permission moved to trash.',
        ]);
    }

    public function multipleAction(Request $request)
    {
        $validated = $request->validate([
            'action' => [
                'required',
                Rule::in([
                    'delete',
                    'restore',
                    'force_delete',
                ]),
            ],
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer', 'distinct'],
        ]);

        $ids = collect($validated['ids'])
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid permissions selected.',
            ], 422);
        }

        $message = match ($validated['action']) {
            'delete' => $this->bulkDelete($ids),
            'restore' => $this->bulkRestore($ids),
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
        $permissions = Permission::onlyTrashed()
            ->search($request->input('search'))
            ->latest('deleted_at')
            ->paginate(15)
            ->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('backoffice.admin.permissions.partials.table', [
                    'permissions' => $permissions,
                    'isTrash' => true,
                ])->render(),
            ]);
        }

        $title = 'Trashed Permissions';

        $breadcrumb = [
            ['text' => 'Permissions', 'url' => route('admin.permissions.index')],
            ['text' => 'Trash', 'url' => null],
        ];

        return view('backoffice.admin.permissions.trash', compact(
            'permissions',
            'title',
            'breadcrumb'
        ));
    }

    public function restore(int $permission)
    {
        $model = Permission::onlyTrashed()->findOrFail($permission);

        $model->restore();

        $this->forgetPermissionCache();

        return response()->json([
            'success' => true,
            'message' => 'Permission restored successfully.',
        ]);
    }

    public function forceDelete(int $permission)
    {
        $model = Permission::onlyTrashed()->findOrFail($permission);

        $model->forceDelete();

        $this->forgetPermissionCache();

        return response()->json([
            'success' => true,
            'message' => 'Permission permanently deleted.',
        ]);
    }

    private function validatePermission(
        Request $request,
        ?Permission $permission = null
    ): array {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'guard_name' => trim((string) $request->input('guard_name', 'admin')),
            'group_name' => trim((string) $request->input('group_name', '')),
        ]);

        $guardName = (string) $request->input('guard_name');

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('permissions', 'name')
                    ->where(fn ($query) => $query->where('guard_name', $guardName))
                    ->ignore($permission?->id),
            ],
            'guard_name' => [
                'required',
                'string',
                'max:255',
            ],
            'group_name' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $validated['group_name'] = $validated['group_name'] !== ''
            ? $validated['group_name']
            : 'General';

        return $validated;
    }

    private function guardAgainstAssignedGuardChange(
        Permission $permission,
        string $newGuardName
    ): void {
        if ($permission->guard_name === $newGuardName) {
            return;
        }

        $hasRoleAssignments = DB::table(
            config('permission.table_names.role_has_permissions', 'role_has_permissions')
        )
            ->where(
                config('permission.column_names.permission_pivot_key') ?: 'permission_id',
                $permission->id
            )
            ->exists();

        $hasDirectAssignments = DB::table(
            config('permission.table_names.model_has_permissions', 'model_has_permissions')
        )
            ->where(
                config('permission.column_names.permission_pivot_key') ?: 'permission_id',
                $permission->id
            )
            ->exists();

        if ($hasRoleAssignments || $hasDirectAssignments) {
            throw ValidationException::withMessages([
                'guard_name' => 'Guard name cannot be changed while this permission is assigned to a role or user.',
            ]);
        }
    }

    private function bulkDelete(array $ids): string
    {
        $count = Permission::query()
            ->whereIn('id', $ids)
            ->delete();

        return $count > 0
            ? "{$count} permission(s) moved to trash."
            : 'No active permissions were changed.';
    }

    private function bulkRestore(array $ids): string
    {
        $count = Permission::onlyTrashed()
            ->whereIn('id', $ids)
            ->restore();

        return $count > 0
            ? "{$count} permission(s) restored."
            : 'No trashed permissions were changed.';
    }

    private function bulkForceDelete(array $ids): string
    {
        $count = Permission::onlyTrashed()
            ->whereIn('id', $ids)
            ->forceDelete();

        return $count > 0
            ? "{$count} permission(s) permanently deleted."
            : 'No trashed permissions were changed.';
    }

    private function forgetPermissionCache(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
