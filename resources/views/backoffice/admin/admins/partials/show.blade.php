<div class="text-center mb-4">
    @if($admin->hasProfilePhoto())
        <img src="{{ $admin->image_url }}"
             alt="{{ $admin->name }}"
             class="rounded-circle border mb-3 shadow"
             width="80"
             height="80"
             style="object-fit: cover;">
    @else
        <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center text-white mb-3 shadow" style="width: 80px; height: 80px; font-size: 32px; font-weight: bold;">
            {{ strtoupper(substr($admin->name, 0, 1)) }}
        </div>
    @endif

    <h4 class="font-weight-bold text-dark mb-1">{{ $admin->name }}</h4>
    <p class="text-muted mb-2"><i class="fas fa-envelope mr-2 text-primary"></i>{{ $admin->email }}</p>
    <span class="badge badge-{{ $admin->status ? 'success' : 'secondary' }} px-3 py-1 font-weight-bold shadow-sm">
        {{ $admin->status ? 'Active' : 'Inactive' }}
    </span>
</div>

<div class="card bg-light border-0 p-4 shadow-sm mb-4">
    <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2">
        <i class="fas fa-user-shield mr-2 text-info"></i> Assigned Roles
    </h6>
    <div>
        @forelse($admin->roles as $role)
            <span class="badge badge-info px-3 py-2 mr-2 mb-2 text-uppercase shadow-sm" style="font-size: 12px;">
                {{ $role->name }}
            </span>
        @empty
            <span class="text-muted small font-italic"><i class="fas fa-exclamation-circle mr-1"></i>No roles assigned to this admin yet.</span>
        @endforelse
    </div>
</div>

<div class="row text-center mt-3 bg-light rounded py-3 shadow-sm mx-0">
    <div class="col-6 border-right">
        <p class="text-muted small text-uppercase font-weight-bold mb-1"><i class="fas fa-calendar-plus mr-1"></i>Account Created</p>
        <h6 class="text-dark font-weight-bold mb-0">{{ $admin->created_at->format('d M, Y') }}</h6>
        <small class="text-muted">{{ $admin->created_at->format('h:i A') }}</small>
    </div>
    <div class="col-6">
        <p class="text-muted small text-uppercase font-weight-bold mb-1"><i class="fas fa-edit mr-1"></i>Last Updated</p>
        <h6 class="text-dark font-weight-bold mb-0">{{ $admin->updated_at->format('d M, Y') }}</h6>
        <small class="text-muted">{{ $admin->updated_at->format('h:i A') }}</small>
    </div>
</div>

<div class="text-right border-top pt-3 mt-4 bg-white rounded-bottom">
    <button type="button" class="btn btn-secondary font-weight-bold px-5 shadow-sm" data-dismiss="modal">Close</button>
</div>
