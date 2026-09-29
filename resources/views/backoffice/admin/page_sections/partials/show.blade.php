@php
    $payload = json_encode($pageSection->payload ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $status = $pageSection->scheduleStatus();
@endphp

<div class="row">
    <div class="col-md-5">
        <div class="card border-0 bg-light h-100"><div class="card-body">
            <h5 class="font-weight-bold text-dark mb-3">{{ $pageSection->heading ?: ($pageSection->sectionDefinition?->name ?? 'Page Section') }}</h5>
            <dl class="row mb-0"><dt class="col-sm-4">Page</dt><dd class="col-sm-8">{{ $pageSection->page?->title ?? 'Deleted page' }}</dd><dt class="col-sm-4">Section</dt><dd class="col-sm-8"><code>{{ $pageSection->sectionDefinition?->key ?? 'deleted-definition' }}</code></dd><dt class="col-sm-4">Theme</dt><dd class="col-sm-8"><span class="badge badge-light border">{{ $pageSection->theme }}</span></dd><dt class="col-sm-4">Order</dt><dd class="col-sm-8">{{ $pageSection->sort_order }}</dd><dt class="col-sm-4">Status</dt><dd class="col-sm-8"><span class="badge badge-{{ $pageSection->scheduleBadgeClass() }}">{{ ucfirst($status) }}</span></dd><dt class="col-sm-4">Starts</dt><dd class="col-sm-8">{{ $pageSection->starts_at?->format('d M Y, h:i A') ?? 'Immediately' }}</dd><dt class="col-sm-4">Ends</dt><dd class="col-sm-8">{{ $pageSection->ends_at?->format('d M Y, h:i A') ?? 'No expiry' }}</dd></dl>
        </div></div>
    </div>
    <div class="col-md-7 mt-3 mt-md-0">
        <h6 class="font-weight-bold text-uppercase text-muted">Heading</h6><div class="border rounded p-3 bg-white mb-3">{{ $pageSection->heading ?: 'No heading provided.' }}</div>
        <h6 class="font-weight-bold text-uppercase text-muted">Subheading</h6><div class="border rounded p-3 bg-white mb-4">{{ $pageSection->subheading ?: 'No subheading provided.' }}</div>
        <h6 class="font-weight-bold text-uppercase text-muted">Payload JSON</h6><pre class="border rounded bg-dark text-light p-3 mb-0" style="max-height:420px;overflow:auto;"><code>{{ $payload }}</code></pre>
    </div>
</div>
