<div class="text-center mb-4">
    <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center text-white mb-3 shadow" style="width: 70px; height: 70px; font-size: 28px; font-weight: bold;">
        <i class="fas fa-shield-alt"></i>
    </div>
    <h4 class="font-weight-bold text-dark mb-1">{{ $role->name }}</h4>
    <span class="badge badge-secondary px-3 py-1 font-weight-bold text-uppercase shadow-sm">Guard: {{ $role->guard_name }}</span>
</div>

<div class="card bg-light border-0 p-3 shadow-sm mb-3">
    <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2">
        <i class="fas fa-key mr-2 text-primary"></i> Assigned Permissions ({{ $role->permissions->count() }})
    </h6>

    <div style="max-height: 300px; overflow-y: auto;">
        @forelse($groupedPermissions as $groupName => $perms)
            <div class="mb-3 bg-white p-2 rounded border shadow-sm">
                <span class="font-weight-bold text-primary small text-uppercase d-block mb-1 border-bottom pb-1">{{ $groupName }}</span>
                <div class="d-flex flex-wrap" style="gap: 5px;">
                    @foreach($perms as $perm)
                        <span class="badge badge-light border text-dark px-2 py-1 small">{{ $perm->name }}</span>
                    @endforeach
                </div>
            </div>
        @empty
            <span class="text-muted small font-italic"><i class="fas fa-exclamation-circle mr-1"></i>No permissions assigned to this role.</span>
        @endforelse
    </div>
</div>

<div class="text-right border-top pt-3 mt-3 bg-white rounded-bottom">
    <button type="button" class="btn btn-secondary font-weight-bold px-5 shadow-sm" data-dismiss="modal">Close</button>
</div>
