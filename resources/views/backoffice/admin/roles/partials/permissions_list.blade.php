@forelse($permissions as $groupName => $groupPermissions)
    @php
        $groupPermNames = $groupPermissions->pluck('name')->toArray();
        $isAllGroupChecked = !empty($selectedPermissions)
            && count($groupPermNames) > 0
            && count(array_intersect($groupPermNames, $selectedPermissions)) === count($groupPermNames);
    @endphp

    <div class="card shadow-sm border-0 mb-3 permission-group-card">
        <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center border-bottom">
            <span class="font-weight-bold text-dark text-uppercase small">
                <i class="fas fa-folder-open text-warning mr-1"></i> {{ $groupName }}
            </span>
            <div class="custom-control custom-checkbox">
                <input type="checkbox" class="custom-control-input group-select-all" id="group_{{ $loop->index }}" {{ $isAllGroupChecked ? 'checked' : '' }}>
                <label class="custom-control-label small font-weight-bold text-muted" for="group_{{ $loop->index }}">Select Group</label>
            </div>
        </div>

        <div class="card-body bg-white py-2 px-3">
            <div class="row">
                @foreach($groupPermissions as $permission)
                    <div class="col-md-4 mb-2 permission-item-col">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox"
                                   class="custom-control-input permission-checkbox"
                                   name="permissions[]"
                                   value="{{ $permission->name }}"
                                   id="perm_{{ $permission->id }}"
                                   {{ in_array($permission->name, $selectedPermissions ?? [], true) ? 'checked' : '' }}>
                            <label class="custom-control-label text-dark small" for="perm_{{ $permission->id }}">{{ $permission->name }}</label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@empty
    <div class="text-center py-4 text-muted">
        <i class="fas fa-exclamation-circle fa-2x mb-2"></i>
        <p class="mb-0 font-weight-bold">No permissions found for this guard.</p>
    </div>
@endforelse
