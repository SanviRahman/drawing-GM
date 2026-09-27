<div class="row text-center mb-4">
    <div class="col-md-6 mb-3 mb-md-0">
        <div class="p-3 border rounded shadow-sm bg-light h-100">
            <p class="text-muted small text-uppercase font-weight-bold mb-1">Record ID</p>
            <h5 class="mb-0 text-dark font-weight-bold">#{{ $permission->id }}</h5>
        </div>
    </div>
    <div class="col-md-6">
        <div class="p-3 border rounded shadow-sm bg-light h-100">
            <p class="text-muted small text-uppercase font-weight-bold mb-1">Created On</p>
            <h6 class="mb-0 text-dark mt-1">{{ $permission->created_at->format('M d, Y h:i A') }}</h6>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm bg-light mb-3">
    <div class="card-body p-4 text-center">
        <span class="text-muted small text-uppercase font-weight-bold d-block mb-1">Permission Name</span>
        <h3 class="font-weight-bold text-dark mb-3"><i class="fas fa-key text-primary mr-2"></i>{{ $permission->name }}</h3>

        <div class="d-flex justify-content-center" style="gap: 10px;">
            <span class="badge badge-info px-3 py-2"><i class="fas fa-shield-alt mr-1"></i>Guard: {{ $permission->guard_name }}</span>
            <span class="badge badge-primary px-3 py-2"><i class="fas fa-layer-group mr-1"></i>Group: {{ $permission->group_name ?? 'General' }}</span>
        </div>
    </div>
</div>

<div class="text-right border-top pt-3 mt-3 bg-white rounded-bottom">
    <button type="button" class="btn btn-secondary font-weight-bold px-5 shadow-sm" data-dismiss="modal">Close</button>
</div>
