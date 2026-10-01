<div class="row">
    <div class="col-md-4 text-center">@if($testimonial->hasMedia('photo'))<img
            src="{{ $testimonial->getFirstMediaUrl('photo') }}" width="120" height="120"
            class="rounded-circle border shadow-sm" style="object-fit:cover;">@endif<h4 class="font-weight-bold mt-3">
            {{ $testimonial->customer_name }}</h4>
        <div class="text-muted">{{ $testimonial->customer_title }}</div>@if($testimonial->rating)<div
            class="text-warning h5 mt-2">{{ str_repeat('★',$testimonial->rating) }}</div>@endif
    </div>
    <div class="col-md-8">
        <div class="card bg-light border-0">
            <div class="card-body">
                <p><strong>Type:</strong> {{ ucfirst(str_replace('_',' ',$testimonial->type)) }}</p>
                <p><strong>Source:</strong> {{ ucfirst($testimonial->source) }}</p>
                <p><strong>Status:</strong> {{ $testimonial->is_active?'Active':'Inactive' }}</p>
                <p><strong>Featured:</strong> {{ $testimonial->is_featured?'Yes':'No' }}</p>
                @if($testimonial->reviewed_at)<p><strong>Reviewed:</strong>
                    {{ $testimonial->reviewed_at->format('d M Y, h:i A') }}</p>@endif
            </div>
        </div>
    </div>
    <div class="col-md-12 mt-3">
        <h6 class="font-weight-bold">Review</h6>
        <div class="border rounded p-3">{!! $testimonial->review ?: '<span class="text-muted">No review text.</span>'
            !!}</div>
    </div>
    @if($testimonial->hasMedia('testimonial_screenshot'))<div class="col-md-12 mt-3">
        <h6 class="font-weight-bold">Screenshot</h6><img
            src="{{ $testimonial->getFirstMediaUrl('testimonial_screenshot') }}" class="img-fluid rounded border"
            style="max-height:500px;">
    </div>@endif
    <div class="col-md-6 mt-3"><strong>Services</strong>
        <div>@forelse($testimonial->services as $service)<span
                class="badge badge-info mr-1">{{ $service->name }}</span>@empty<span
                class="text-muted">Global</span>@endforelse</div>
    </div>
    <div class="col-md-6 mt-3"><strong>Locations</strong>
        <div>@forelse($testimonial->locations as $location)<span
                class="badge badge-secondary mr-1">{{ $location->name }}</span>@empty<span
                class="text-muted">Global</span>@endforelse</div>
    </div>
</div>
<div class="text-right border-top pt-3 mt-4"><button type="button" class="btn btn-secondary"
        data-dismiss="modal">Close</button></div>