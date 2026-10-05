<div class="row">
<div class="col-md-6 mb-3"><small class="text-muted d-block">Provider</small><strong>{{ $trackingProvider->label() }}</strong></div>
<div class="col-md-6 mb-3"><small class="text-muted d-block">Public Identifier</small><code>{{ $trackingProvider->public_identifier ?: '—' }}</code></div>
<div class="col-md-3 mb-3"><small class="text-muted d-block">Enabled</small>{{ $trackingProvider->is_enabled?'Yes':'No' }}</div>
<div class="col-md-3 mb-3"><small class="text-muted d-block">Mode</small>{{ $trackingProvider->test_mode?'Test':'Live' }}</div>
<div class="col-md-3 mb-3"><small class="text-muted d-block">Secret</small>{{ $trackingProvider->secret ? 'Configured':'Not configured' }}</div>
<div class="col-md-3 mb-3"><small class="text-muted d-block">Event Rules</small>{{ $trackingProvider->event_rules_count ?? 0 }}</div>
@if($trackingProvider->provider==='meta_pixel')
<div class="col-md-12"><h6 class="font-weight-bold text-primary">Meta Pixel Entries</h6>@forelse($trackingProvider->metaPixels() as $pixel)<div class="border rounded p-2 mb-2"><strong>{{ $pixel['label']??'Pixel' }}</strong> — <code>{{ $pixel['pixel_id']??'—' }}</code> <span class="badge badge-{{ (bool)($pixel['is_active']??true)?'success':'secondary' }}">{{ (bool)($pixel['is_active']??true)?'Active':'Inactive' }}</span>@if(!empty($pixel['script_snippet']))<pre class="bg-light border rounded p-2 mt-2 mb-0" style="white-space:pre-wrap">{{ $pixel['script_snippet'] }}</pre>@endif</div>@empty<p class="text-muted">No Meta Pixel entries.</p>@endforelse</div>
@endif
</div>
