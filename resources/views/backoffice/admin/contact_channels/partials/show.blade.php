<div class="text-center mb-4">
    <span class="contact-channel-icon mb-3"
          style="background: {{ $contactChannel->colour_css }}; width:72px; height:72px; font-size:30px; {{ $contactChannel->colour_css === '#f8f9fa' ? 'color:#343a40;' : '' }}">
        <i class="{{ $contactChannel->icon_class }}"></i>
    </span>

    <h4 class="font-weight-bold text-dark mb-1">{{ $contactChannel->label }}</h4>
    <p class="text-muted mb-2">{{ $contactChannel->type_label }}@if($contactChannel->region) · {{ $contactChannel->region }}@endif</p>

    <span class="badge badge-{{ $contactChannel->is_active ? 'success' : 'secondary' }} px-3 py-1 mr-1">
        {{ $contactChannel->is_active ? 'Active' : 'Inactive' }}
    </span>
    @if($contactChannel->is_default)
        <span class="badge badge-warning px-3 py-1"><i class="fas fa-star mr-1"></i>Default</span>
    @endif
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card bg-light border-0 h-100 shadow-sm">
            <div class="card-body">
                <h6 class="font-weight-bold border-bottom pb-2 mb-3"><i class="fas fa-address-card text-primary mr-2"></i>Contact Details</h6>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Stored Value</dt>
                    <dd class="col-sm-8 text-break">{{ $contactChannel->value }}</dd>

                    <dt class="col-sm-4">Display Value</dt>
                    <dd class="col-sm-8">{{ $contactChannel->display_value ?: '—' }}</dd>

                    <dt class="col-sm-4">Availability</dt>
                    <dd class="col-sm-8">{{ $contactChannel->availability_text ?: '—' }}</dd>

                    <dt class="col-sm-4">Track Clicks</dt>
                    <dd class="col-sm-8">{{ $contactChannel->track_clicks ? 'Yes' : 'No' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card bg-light border-0 h-100 shadow-sm">
            <div class="card-body">
                <h6 class="font-weight-bold border-bottom pb-2 mb-3"><i class="fas fa-sliders-h text-info mr-2"></i>Display Configuration</h6>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Icon Key</dt>
                    <dd class="col-sm-8"><code>{{ $contactChannel->icon }}</code></dd>

                    <dt class="col-sm-4">Colour</dt>
                    <dd class="col-sm-8">
                        <span class="d-inline-block align-middle mr-2" style="width:18px;height:18px;border-radius:4px;background:{{ $contactChannel->colour_css }};border:1px solid #ced4da;"></span>
                        <code>{{ $contactChannel->colour }}</code>
                    </dd>

                    <dt class="col-sm-4">Sort Order</dt>
                    <dd class="col-sm-8">{{ $contactChannel->sort_order }}</dd>

                    <dt class="col-sm-4">Default</dt>
                    <dd class="col-sm-8">{{ $contactChannel->is_default ? 'Yes' : 'No' }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<div class="card bg-light border-0 p-4 shadow-sm mb-4">
    <h6 class="font-weight-bold text-dark mb-3 border-bottom pb-2">
        <i class="fas fa-comment-alt mr-2 text-success"></i>Message Template
    </h6>

    @if($contactChannel->message_template)
        <div class="text-dark contact-message-rich-content">{!! $contactChannel->message_template !!}</div>
        <hr>
        <small class="text-muted d-block mb-1 font-weight-bold">Plain text used by a future WhatsApp URL resolver:</small>
        <pre class="mb-0 p-2 bg-white border rounded" style="white-space:pre-wrap;">{{ $contactChannel->message_template_text }}</pre>
    @else
        <span class="text-muted small font-italic"><i class="fas fa-info-circle mr-1"></i>No message template configured.</span>
    @endif
</div>

<div class="row text-center mt-3 bg-light rounded py-3 shadow-sm mx-0">
    <div class="col-6 border-right">
        <p class="text-muted small text-uppercase font-weight-bold mb-1"><i class="fas fa-calendar-plus mr-1"></i>Created</p>
        <h6 class="text-dark font-weight-bold mb-0">{{ $contactChannel->created_at?->format('d M, Y') ?? '—' }}</h6>
        <small class="text-muted">{{ $contactChannel->created_at?->format('h:i A') ?? '' }}</small>
    </div>
    <div class="col-6">
        <p class="text-muted small text-uppercase font-weight-bold mb-1"><i class="fas fa-edit mr-1"></i>Last Updated</p>
        <h6 class="text-dark font-weight-bold mb-0">{{ $contactChannel->updated_at?->format('d M, Y') ?? '—' }}</h6>
        <small class="text-muted">{{ $contactChannel->updated_at?->format('h:i A') ?? '' }}</small>
    </div>
</div>

<div class="text-right border-top pt-3 mt-4 bg-white rounded-bottom">
    <button type="button" class="btn btn-secondary font-weight-bold px-5 shadow-sm" data-dismiss="modal">Close</button>
</div>
