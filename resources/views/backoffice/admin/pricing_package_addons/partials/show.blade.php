@php
    $package = $pricingPackageAddon->pricingPackage;
    $addon = $pricingPackageAddon->pricingAddon;
    $imageUrl = $addon?->getFirstMediaUrl(\App\Models\PricingAddon::MEDIA_COLLECTION) ?: '';
    $overrideJson = $pricingPackageAddon->override_data
        ? json_encode(
            $pricingPackageAddon->override_data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        )
        : null;
@endphp

<div class="row">
    <div class="col-md-4 mb-3">
        @if($imageUrl)
            <img src="{{ $imageUrl }}"
                 alt="{{ $addon?->name ?? 'Pricing add-on' }}"
                 class="img-fluid rounded border"
                 style="width:100%;max-height:220px;object-fit:cover;">
        @else
            <div class="border rounded bg-light d-flex align-items-center justify-content-center text-muted" style="height:180px;">
                <div class="text-center">
                    <i class="far fa-image fa-3x mb-2"></i>
                    <div>No add-on image</div>
                </div>
            </div>
        @endif
        <small class="text-muted d-block mt-2">
            Image is owned by the Pricing Add-on record.
        </small>
    </div>

    <div class="col-md-8 mb-3">
        <div class="row">
            <div class="col-md-12 mb-3">
                <small class="text-muted d-block">Pricing Package</small>
                <strong>{{ $package?->name ?? 'Deleted package' }}</strong>
            </div>

            <div class="col-md-6 mb-3">
                <small class="text-muted d-block">Service</small>
                <span>{{ $package?->service?->name ?: 'Global / Not assigned' }}</span>
            </div>

            <div class="col-md-6 mb-3">
                <small class="text-muted d-block">Location</small>
                <span>{{ $package?->location?->name ?: 'Global / Not assigned' }}</span>
            </div>

            <div class="col-md-8 mb-3">
                <small class="text-muted d-block">Pricing Add-on</small>
                <strong>{{ $addon?->name ?? 'Deleted add-on' }}</strong>
            </div>

            <div class="col-md-4 mb-3">
                <small class="text-muted d-block">Default Price</small>
                <span>{{ $addon?->display_price ?? '—' }}</span>
            </div>
        </div>
    </div>

    <div class="col-md-12 mb-3">
        <small class="text-muted d-block mb-1">Override Data</small>
        @if($overrideJson)
            <pre class="border rounded bg-light p-3 mb-0 text-monospace" style="white-space:pre-wrap;word-break:break-word;">{{ $overrideJson }}</pre>
        @else
            <div class="border rounded bg-light px-3 py-2 text-muted">No overrides. This package uses the add-on defaults.</div>
        @endif
    </div>

    <div class="col-md-6">
        <small class="text-muted">Created: {{ $pricingPackageAddon->created_at?->format('d M Y, h:i A') ?? '—' }}</small>
    </div>
    <div class="col-md-6 text-md-right">
        <small class="text-muted">Updated: {{ $pricingPackageAddon->updated_at?->format('d M Y, h:i A') ?? '—' }}</small>
    </div>
</div>
