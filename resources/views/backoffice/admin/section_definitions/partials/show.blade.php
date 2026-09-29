@php
    $schema = json_encode($sectionDefinition->schema_json ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
@endphp

<div class="row">
    <div class="col-md-5">
        <div class="card border-0 bg-light h-100"><div class="card-body">
            <h5 class="font-weight-bold text-dark mb-3">{{ $sectionDefinition->name }}</h5>
            <dl class="row mb-0"><dt class="col-sm-4">Key</dt><dd class="col-sm-8"><code>{{ $sectionDefinition->key }}</code></dd><dt class="col-sm-4">Component</dt><dd class="col-sm-8"><code>{{ $sectionDefinition->component_view }}</code></dd><dt class="col-sm-4">Status</dt><dd class="col-sm-8">@if($sectionDefinition->is_active)<span class="badge badge-success">Active</span>@else<span class="badge badge-secondary">Inactive</span>@endif</dd><dt class="col-sm-4">Created</dt><dd class="col-sm-8">{{ optional($sectionDefinition->created_at)->format('d M Y, h:i A') }}</dd><dt class="col-sm-4">Updated</dt><dd class="col-sm-8">{{ optional($sectionDefinition->updated_at)->format('d M Y, h:i A') }}</dd></dl>
        </div></div>
    </div>
    <div class="col-md-7 mt-3 mt-md-0">
        <h6 class="font-weight-bold text-uppercase text-muted">Description</h6>
        <div class="border rounded p-3 bg-white mb-4">@if($sectionDefinition->description){!! $sectionDefinition->description !!}@else<span class="text-muted">No description provided.</span>@endif</div>
        <h6 class="font-weight-bold text-uppercase text-muted">Validation Schema JSON</h6>
        <pre class="border rounded bg-dark text-light p-3 mb-0" style="max-height:420px;overflow:auto;"><code>{{ $schema }}</code></pre>
    </div>
</div>
