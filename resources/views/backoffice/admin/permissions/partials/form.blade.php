@php
    $isEdit = isset($permission);
    $actionUrl = $isEdit ? route('admin.permissions.update', $permission->id) : route('admin.permissions.store');
@endphp

<form id="permission-form" action="{{ $actionUrl }}" method="POST">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <!-- Permission Name -->
        <div class="col-md-12 mb-3">
            <div class="form-group mb-0">
                <label for="name" class="font-weight-bold text-dark">Permission Name <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control"
                       value="{{ $isEdit ? $permission->name : old('name') }}" placeholder="e.g., user_create" required>
                <div class="invalid-feedback error-name"></div>
            </div>
        </div>

        <!-- Guard Name -->
        <div class="col-md-6 mb-3">
            <div class="form-group mb-0">
                <label for="guard_name" class="font-weight-bold text-dark">Guard Name <span class="text-danger">*</span></label>
                <select name="guard_name" id="guard_name" class="form-control" required>
                    <option value="">-- Select Guard --</option>
                    <option value="admin" {{ (($isEdit && $permission->guard_name === 'admin') || (!is_null(old('guard_name')) && old('guard_name') === 'admin') || (!$isEdit && is_null(old('guard_name')))) ? 'selected' : '' }}>Admin</option>
                    <option value="web" {{ (($isEdit && $permission->guard_name === 'web') || (old('guard_name') === 'web')) ? 'selected' : '' }}>Web</option>
                    <option value="staff" {{ (($isEdit && $permission->guard_name === 'staff') || (old('guard_name') === 'staff')) ? 'selected' : '' }}>Staff</option>
                    <option value="student" {{ (($isEdit && $permission->guard_name === 'student') || (old('guard_name') === 'student')) ? 'selected' : '' }}>Student</option>
                    <option value="guardian" {{ (($isEdit && $permission->guard_name === 'guardian') || (old('guard_name') === 'guardian')) ? 'selected' : '' }}>Guardian</option>
                </select>
                <div class="invalid-feedback error-guard_name"></div>
            </div>
        </div>

        <!-- Group Name -->
        <div class="col-md-6 mb-3">
            <div class="form-group mb-0">
                <label for="group_name" class="font-weight-bold text-dark">Group Name</label>
                <input type="text" name="group_name" id="group_name" class="form-control"
                       value="{{ $isEdit ? $permission->group_name : old('group_name') }}" placeholder="e.g., User Management" list="groupList">
                <datalist id="groupList">
                    @if(isset($groups))
                        @foreach($groups as $grp)
                            <option value="{{ $grp }}">
                        @endforeach
                    @endif
                </datalist>
                <div class="invalid-feedback error-group_name"></div>
            </div>
        </div>
    </div>

    <!-- Modal Footer -->
    <div class="text-right border-top pt-3 mt-2 bg-white rounded-bottom">
        <button type="button" class="btn btn-light border font-weight-bold px-4 mr-2 shadow-sm" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary font-weight-bold px-5 shadow-sm">
            <i class="fas fa-save mr-2"></i> {{ $isEdit ? 'Update Permission' : 'Save Permission' }}
        </button>
    </div>
</form>
