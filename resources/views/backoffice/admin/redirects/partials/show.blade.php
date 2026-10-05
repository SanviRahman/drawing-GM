<div class="row">
<div class="col-md-12 mb-3"><small class="text-muted d-block">Source</small><code>{{ $redirect->from_path }}</code></div>
<div class="col-md-12 mb-3"><small class="text-muted d-block">Destination</small><div class="text-break">{{ $redirect->to_url }}</div></div>
<div class="col-md-3"><small class="text-muted d-block">Status Code</small><strong>{{ $redirect->status_code }}</strong></div>
<div class="col-md-3"><small class="text-muted d-block">Enabled</small>{{ $redirect->is_active?'Yes':'No' }}</div>
<div class="col-md-3"><small class="text-muted d-block">Hits</small>{{ number_format($redirect->hits) }}</div>
<div class="col-md-3"><small class="text-muted d-block">Last Hit</small>{{ $redirect->last_hit_at?->format('d M Y H:i') ?? '—' }}</div>
</div>