<div class="text-center mb-4">
    <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center text-white mb-3 shadow" style="width: 80px; height: 80px; font-size: 32px;">
        <i class="fas fa-sliders-h"></i>
    </div>

    <h4 class="font-weight-bold text-dark mb-1">{{ $setting->setting_key }}</h4>
    <p class="text-muted mb-2"><i class="fas fa-folder mr-2 text-primary"></i>{{ $setting->group_name }}</p>

    <span class="badge badge-{{ match($setting->value_type) {
        'boolean' => 'info',
        'integer' => 'success',
        'json' => 'warning',
        'encrypted' => 'dark',
        default => 'secondary',
    } }} px-3 py-1 font-weight-bold shadow-sm text-uppercase mr-1">{{ $setting->value_type }}</span>

    <span class="badge badge-{{ $setting->is_public ? 'success' : 'secondary' }} px-3 py-1 font-weight-bold shadow-sm mr-1">
        {{ $setting->is_public ? 'Public' : 'Private' }}
    </span>
</div>

<div class="card bg-light border-0 p-4 shadow-sm mb-4">
    <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2">
        <i class="fas fa-align-left mr-2 text-info"></i> Stored Value
    </h6>
    <div class="text-break" style="word-wrap: anywhere;">
        @if($setting->value_type === 'encrypted')
            <span class="text-muted font-italic"><i class="fas fa-lock mr-1"></i>{{ $setting->presentValue() }}</span>
        @else
            <code class="text-dark">{{ $setting->presentValue(10000) }}</code>
        @endif
    </div>
</div>

<div class="card bg-light border-0 p-4 shadow-sm mb-4">
    <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2">
        <i class="fas fa-images mr-2 text-info"></i> Branding Media
    </h6>
    <div class="row text-center">
        @foreach(\App\Models\SiteSetting::MEDIA_COLLECTIONS as $collection)
            <div class="col-md-4 mb-3 mb-md-0">
                <img src="{{ $setting->collectionUrl($collection) }}" alt="{{ ucfirst(str_replace('_', ' ', $collection)) }}"
                     class="rounded border bg-white mb-2 shadow-sm" style="width: 100%; height: 90px; object-fit: contain;">
                <p class="small font-weight-bold text-muted mb-0">{{ ucfirst(str_replace('_', ' ', $collection)) }}</p>
                @if($setting->hasMedia($collection))
                    <span class="badge badge-success mt-1">Attached</span>
                @else
                    <span class="badge badge-light text-secondary mt-1">Not set</span>
                @endif
            </div>
        @endforeach
    </div>
</div>

<div class="row text-center mt-3 bg-light rounded py-3 shadow-sm mx-0">
    <div class="col-6 border-right">
        <p class="text-muted small text-uppercase font-weight-bold mb-1"><i class="fas fa-calendar-plus mr-1"></i>Created</p>
        <h6 class="text-dark font-weight-bold mb-0">{{ $setting->created_at?->format('d M, Y') }}</h6>
        <small class="text-muted">{{ $setting->created_at?->format('h:i A') }}</small>
    </div>
    <div class="col-6">
        <p class="text-muted small text-uppercase font-weight-bold mb-1"><i class="fas fa-edit mr-1"></i>Last Updated</p>
        <h6 class="text-dark font-weight-bold mb-0">{{ $setting->updated_at?->format('d M, Y') }}</h6>
        <small class="text-muted">{{ $setting->updated_at?->format('h:i A') }}</small>
    </div>
</div>

<div class="text-right border-top pt-3 mt-4 bg-white rounded-bottom">
    <button type="button" class="btn btn-secondary font-weight-bold px-5 shadow-sm" data-dismiss="modal">Close</button>
</div>
