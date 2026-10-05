<div class="row">
<div class="col-md-6 mb-3"><small class="text-muted d-block">Provider</small><strong>{{ $trackingEventRule->provider?->label() ?? 'Unavailable' }}</strong></div>
<div class="col-md-6 mb-3"><small class="text-muted d-block">Internal Event</small><code>{{ $trackingEventRule->internal_event }}</code></div>
<div class="col-md-12 mb-3"><small class="text-muted d-block">Provider Event</small>{{ $trackingEventRule->provider_event }}</div>
<div class="col-md-6 mb-3"><small class="text-muted d-block">Consent Required</small>{{ $trackingEventRule->requires_marketing_consent?'Yes':'No' }}</div>
<div class="col-md-6 mb-3"><small class="text-muted d-block">Enabled</small>{{ $trackingEventRule->is_enabled?'Yes':'No' }}</div>
<div class="col-md-12"><small class="text-muted d-block">Parameter Map</small><pre class="bg-light border rounded p-3">{{ $trackingEventRule->parameter_map?json_encode($trackingEventRule->parameter_map,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES):'—' }}</pre></div>
</div>