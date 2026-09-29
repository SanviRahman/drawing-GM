<div class="row">
    <div class="col-md-6 mb-2">
        <small class="text-muted d-block">Service</small>
        <strong class="text-dark">{{ $serviceFeature->service?->name ?? 'Deleted service' }}</strong>
    </div>

    <div class="col-md-3 mb-2">
        <small class="text-muted d-block">Status</small>
        <span class="badge badge-{{ $serviceFeature->is_active ? 'success' : 'secondary' }} px-2 py-1">{{ $serviceFeature->is_active ? 'Active' : 'Inactive' }}</span>
    </div>

    <div class="col-md-3 mb-2">
        <small class="text-muted d-block">Order</small>
        <strong>{{ $serviceFeature->sort_order }}</strong>
    </div>

    <div class="col-md-9 mb-2">
        <small class="text-muted d-block">Feature Title</small>
        <strong class="text-dark">{{ $serviceFeature->title }}</strong>
    </div>

    <div class="col-md-3 mb-2">
        <small class="text-muted d-block">Icon</small>

        @if($serviceFeature->icon)
            <span class="badge badge-light border px-2 py-1"><i class="fas fa-{{ $serviceFeature->icon }} mr-1"></i>{{ $serviceFeature->icon }}</span>
        @else
            <span class="text-muted">—</span>
        @endif
    </div>

    <div class="col-md-12 mt-1">
        <small class="text-muted d-block mb-1">Description</small>

        @if($serviceFeature->description)
            <div class="border rounded bg-light px-3 py-2 service-feature-description">{!! $serviceFeature->description !!}</div>
        @else
            <div class="border rounded bg-light px-3 py-3 text-center text-muted"><i class="far fa-file-alt mr-1"></i>No description.</div>
        @endif
    </div>

    <div class="col-md-6 mt-2">
        <small class="text-muted">Created: {{ $serviceFeature->created_at?->format('d M Y, h:i A') ?? '—' }}</small>
    </div>

    <div class="col-md-6 mt-2 text-md-right">
        <small class="text-muted">Updated: {{ $serviceFeature->updated_at?->format('d M Y, h:i A') ?? '—' }}</small>
    </div>
</div>