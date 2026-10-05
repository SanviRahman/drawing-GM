@php
    $channel = $contactTarget->channel;
    $targetRecord = $contactTarget->targetable ?: $contactTarget->targetRecord(true);
    $targetTrashed = $targetRecord && method_exists($targetRecord, 'trashed') && $targetRecord->trashed();
@endphp

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="text-muted small font-weight-bold text-uppercase">Contact Channel</div>
        <div class="font-weight-bold text-dark">{{ $channel?->label ?? 'Unavailable channel' }}</div>
        @if($channel?->region)
            <small class="text-muted">{{ $channel->region }}</small>
        @endif
        @if($channel?->trashed())
            <div><span class="badge badge-danger mt-1">Channel Trashed</span></div>
        @endif
    </div>

    <div class="col-md-6 mb-3">
        <div class="text-muted small font-weight-bold text-uppercase">Channel Type</div>
        <span class="badge badge-info text-uppercase">{{ $channel?->type_label ?? 'Unknown' }}</span>
    </div>

    <div class="col-md-6 mb-3">
        <div class="text-muted small font-weight-bold text-uppercase">Target Type</div>
        <span class="badge badge-secondary">{{ $contactTarget->target_type_label }}</span>
    </div>

    <div class="col-md-6 mb-3">
        <div class="text-muted small font-weight-bold text-uppercase">Target</div>
        <div class="font-weight-bold">{{ $contactTarget->target_name }}</div>
        <small class="text-muted">ID: {{ $contactTarget->targetable_id }}</small>
        @if($targetTrashed)
            <div><span class="badge badge-danger mt-1">Target Trashed</span></div>
        @elseif(! $targetRecord)
            <div><span class="badge badge-danger mt-1">Target Unavailable</span></div>
        @endif
    </div>

    <div class="col-md-6 mb-3">
        <div class="text-muted small font-weight-bold text-uppercase">Created</div>
        <div>{{ optional($contactTarget->created_at)->format('d M Y, h:i A') ?? '—' }}</div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="text-muted small font-weight-bold text-uppercase">Updated</div>
        <div>{{ optional($contactTarget->updated_at)->format('d M Y, h:i A') ?? '—' }}</div>
    </div>
</div>

<div class="alert alert-info mb-0">
    <i class="fas fa-info-circle mr-1"></i>
    This mapping scopes the selected contact channel to this {{ strtolower($contactTarget->target_type_label) }}. Global fallback channels are channels without active contact target mappings.
</div>
