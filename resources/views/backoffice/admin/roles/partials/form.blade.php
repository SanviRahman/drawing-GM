@php
    $isEdit = isset($role);
    $actionUrl = $isEdit ? route('admin.roles.update', $role->id) : route('admin.roles.store');
    $selectedPermissions = $rolePermissions ?? [];
    $guards = $guards ?? ['staff', 'admin', 'guardian', 'web', 'student'];
    $selectedGuard = $isEdit ? $role->guard_name : ($defaultGuard ?? 'admin');
@endphp

<form id="ajax-form" action="{{ $actionUrl }}" method="POST" data-role-id="{{ $isEdit ? $role->id : '' }}">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Role Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ $isEdit ? $role->name : old('name') }}" required placeholder="e.g. Editor">
            <div class="invalid-feedback error-name"></div>
        </div>

        <div class="col-md-6 mb-3">
            <label class="font-weight-bold">Guard Name <span class="text-danger">*</span></label>
            <select name="guard_name" id="guard_name" class="form-control" required>
                @foreach($guards as $guard)
                    <option value="{{ $guard }}" {{ $selectedGuard === $guard ? 'selected' : '' }}>{{ ucfirst($guard) }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback error-guard_name"></div>
        </div>
    </div>

    <hr class="my-3">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
        <h6 class="font-weight-bold text-primary mb-2 mb-md-0">
            <i class="fas fa-key mr-1"></i> Assign Permissions
        </h6>
        <div class="d-flex align-items-center" style="gap: 10px;">
            <input type="text" id="permission_search" class="form-control form-control-sm shadow-none" placeholder="Search permission..." style="width: 200px;" autocomplete="off">
            <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold shadow-sm" id="btnSelectAll">Select All</button>
            <button type="button" class="btn btn-outline-secondary btn-sm font-weight-bold shadow-sm" id="btnDeselectAll">Deselect All</button>
        </div>
    </div>

    <div id="permissions-container" class="border rounded p-3 bg-light" style="max-height: 350px; overflow-y: auto;">
        @include('backoffice.admin.roles.partials.permissions_list', ['permissions' => $permissions ?? [], 'selectedPermissions' => $selectedPermissions])
    </div>
    <div class="invalid-feedback error-permissions d-block"></div>

    <div class="text-right border-top pt-3 mt-4 bg-white rounded-bottom">
        <button type="button" class="btn btn-light border font-weight-bold px-4 mr-2 shadow-sm" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary font-weight-bold px-5 shadow-sm">
            <i class="fas fa-save mr-2"></i> {{ $isEdit ? 'Update Role' : 'Save Role' }}
        </button>
    </div>
</form>
