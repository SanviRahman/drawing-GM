@php
    $imageUrl = $pricingAddon->getFirstMediaUrl(\App\Models\PricingAddon::MEDIA_COLLECTION);
@endphp
<div class="row">
    <div class="col-md-4 mb-3">
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $pricingAddon->name }}" class="img-fluid rounded border" style="width:100%;max-height:220px;object-fit:cover;">
        @else
            <div class="border rounded bg-light d-flex align-items-center justify-content-center text-muted" style="height:180px;"><div class="text-center"><i class="far fa-image fa-3x mb-2"></i><div>No image</div></div></div>
        @endif
    </div>
    <div class="col-md-8 mb-3"><div class="row">
        <div class="col-md-8 mb-2"><small class="text-muted d-block">Name</small><strong>{{ $pricingAddon->name }}</strong></div>
        <div class="col-md-4 mb-2"><small class="text-muted d-block">Status</small><span class="badge badge-{{ $pricingAddon->is_active ? 'success' : 'secondary' }} px-2 py-1">{{ $pricingAddon->is_active ? 'Active' : 'Inactive' }}</span></div>
        <div class="col-md-4 mb-2"><small class="text-muted d-block">Price Type</small><span class="badge badge-info px-2 py-1">{{ $pricingAddon->price_type_label }}</span></div>
        <div class="col-md-4 mb-2"><small class="text-muted d-block">Amount</small><span>{{ $pricingAddon->amount !== null ? number_format((float) $pricingAddon->amount, 2) : '—' }}</span></div>
        <div class="col-md-4 mb-2"><small class="text-muted d-block">Maximum Amount</small><span>{{ $pricingAddon->amount_max !== null ? number_format((float) $pricingAddon->amount_max, 2) : '—' }}</span></div>
        <div class="col-md-6 mb-2"><small class="text-muted d-block">Unit</small><span>{{ $pricingAddon->unit ?: '—' }}</span></div>
        <div class="col-md-6 mb-2"><small class="text-muted d-block">Sort Order</small><span>{{ $pricingAddon->sort_order }}</span></div>
        <div class="col-md-12 mb-2"><small class="text-muted d-block">Display Price</small><div class="border rounded bg-light px-3 py-2 font-weight-bold">{{ $pricingAddon->display_price }}</div></div>
    </div></div>

    <div class="col-md-12 mb-3"><small class="text-muted d-block mb-1">Description</small><div class="border rounded bg-light px-3 py-2">{!! $pricingAddon->description ?: '<span class="text-muted">No description.</span>' !!}</div></div>
    <div class="col-md-6"><small class="text-muted">Created: {{ $pricingAddon->created_at?->format('d M Y, h:i A') ?? '—' }}</small></div>
    <div class="col-md-6 text-md-right"><small class="text-muted">Updated: {{ $pricingAddon->updated_at?->format('d M Y, h:i A') ?? '—' }}</small></div>
</div>
