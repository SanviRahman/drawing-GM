<div class="row">
    <div class="col-md-8 mb-2"><small class="text-muted d-block">Pricing Package</small><strong>{{ $pricingItem->pricingPackage?->name ?? 'Deleted package' }}</strong></div>
    <div class="col-md-4 mb-2"><small class="text-muted d-block">Status</small><span class="badge badge-{{ $pricingItem->is_active ? 'success' : 'secondary' }} px-2 py-1">{{ $pricingItem->is_active ? 'Active' : 'Inactive' }}</span></div>

    <div class="col-md-8 mb-2"><small class="text-muted d-block">Label</small><strong>{{ $pricingItem->label }}</strong></div>
    <div class="col-md-4 mb-2"><small class="text-muted d-block">Price Type</small><span class="badge badge-info px-2 py-1">{{ $pricingItem->price_type_label }}</span></div>

    <div class="col-md-12 mb-2"><small class="text-muted d-block">Display Price</small><div class="border rounded bg-light px-3 py-2 font-weight-bold">{{ $pricingItem->display_price }}</div></div>

    <div class="col-md-3 mb-2"><small class="text-muted d-block">Amount</small><span>{{ $pricingItem->amount !== null ? number_format((float) $pricingItem->amount, 2) : '—' }}</span></div>
    <div class="col-md-3 mb-2"><small class="text-muted d-block">Max Amount</small><span>{{ $pricingItem->amount_max !== null ? number_format((float) $pricingItem->amount_max, 2) : '—' }}</span></div>
    <div class="col-md-3 mb-2"><small class="text-muted d-block">Unit</small><span>{{ $pricingItem->unit ?: '—' }}</span></div>
    <div class="col-md-3 mb-2"><small class="text-muted d-block">Order</small><span>{{ $pricingItem->sort_order }}</span></div>

    <div class="col-md-6 mb-2"><small class="text-muted d-block">Prefix</small><span>{{ $pricingItem->prefix ?: '—' }}</span></div>
    <div class="col-md-6 mb-2"><small class="text-muted d-block">Suffix</small><span>{{ $pricingItem->suffix ?: '—' }}</span></div>

    <div class="col-md-6 mt-2"><small class="text-muted">Created: {{ $pricingItem->created_at?->format('d M Y, h:i A') ?? '—' }}</small></div>
    <div class="col-md-6 mt-2 text-md-right"><small class="text-muted">Updated: {{ $pricingItem->updated_at?->format('d M Y, h:i A') ?? '—' }}</small></div>
</div>
