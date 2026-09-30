<div class="row">
    <div class="col-md-6 mb-2"><small class="text-muted d-block">Name</small><strong>{{ $pricingPackage->name }}</strong></div>
    <div class="col-md-3 mb-2"><small class="text-muted d-block">Status</small><span class="badge badge-{{ $pricingPackage->is_active ? 'success' : 'secondary' }}">{{ $pricingPackage->is_active ? 'Active' : 'Inactive' }}</span></div>
    <div class="col-md-3 mb-2"><small class="text-muted d-block">Featured</small><span class="badge badge-{{ $pricingPackage->is_featured ? 'warning' : 'light' }}">{{ $pricingPackage->is_featured ? 'Yes' : 'No' }}</span></div>
    <div class="col-md-6 mb-2"><small class="text-muted d-block">Service</small><span>{{ $pricingPackage->service?->name ?? 'Global' }}</span></div>
    <div class="col-md-6 mb-2"><small class="text-muted d-block">Location</small><span>{{ $pricingPackage->location?->name ?? 'Global' }}</span></div>
    <div class="col-md-4 mb-2"><small class="text-muted d-block">Currency</small><strong>{{ $pricingPackage->currency }}</strong></div>
    <div class="col-md-4 mb-2"><small class="text-muted d-block">Badge</small><span>{{ $pricingPackage->badge ?: '—' }}</span></div>
    <div class="col-md-4 mb-2"><small class="text-muted d-block">Order</small><span>{{ $pricingPackage->sort_order }}</span></div>
    <div class="col-md-12 mb-2"><small class="text-muted d-block">Subtitle</small><span>{{ $pricingPackage->subtitle ?: '—' }}</span></div>
    <div class="col-md-12"><small class="text-muted d-block mb-1">Description</small>
        @if($pricingPackage->description)
            <div class="border rounded bg-light px-3 py-2">{!! $pricingPackage->description !!}</div>
        @else
            <div class="border rounded bg-light px-3 py-2 text-muted">No description.</div>
        @endif
    </div>
</div>
