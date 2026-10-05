<div id="lead-show-panel" data-note-url="{{ route('admin.leads.notes.store', $lead->id) }}">
    <div class="row">
        <div class="col-md-3 mb-3"><small class="text-muted text-uppercase font-weight-bold">Reference</small><div class="font-weight-bold text-primary">{{ $lead->reference }}</div></div>
        <div class="col-md-3 mb-3"><small class="text-muted text-uppercase font-weight-bold">Status</small><div><span class="badge badge-{{ $lead->statusBadgeClass() }}">{{ $lead->statusLabel() }}</span></div></div>
        <div class="col-md-3 mb-3"><small class="text-muted text-uppercase font-weight-bold">Assignee</small><div>{{ $lead->assignee?->name ?? 'Unassigned' }}</div></div>
        <div class="col-md-3 mb-3"><small class="text-muted text-uppercase font-weight-bold">Created</small><div>{{ optional($lead->created_at)->format('d M Y H:i') }}</div></div>

        <div class="col-md-4 mb-3"><small class="text-muted text-uppercase font-weight-bold">Client</small><div>{{ $lead->name }}</div></div>
        <div class="col-md-4 mb-3"><small class="text-muted text-uppercase font-weight-bold">Phone</small><div>{{ $lead->phone }}</div></div>
        <div class="col-md-4 mb-3"><small class="text-muted text-uppercase font-weight-bold">Email</small><div>{{ $lead->email ?: '—' }}</div></div>

        <div class="col-md-4 mb-3"><small class="text-muted text-uppercase font-weight-bold">Location</small><div>{{ $lead->location?->name ?? '—' }}</div></div>
        <div class="col-md-4 mb-3"><small class="text-muted text-uppercase font-weight-bold">User Account</small><div>{{ $lead->user?->name ?? 'Guest' }}</div></div>
        <div class="col-md-4 mb-3"><small class="text-muted text-uppercase font-weight-bold">Source</small><div class="text-break">{{ $lead->source_page_url ?: '—' }}</div></div>

        <div class="col-md-12 mb-3">
            <small class="text-muted text-uppercase font-weight-bold">Message</small>
            <div class="border rounded p-3 mt-1 bg-light">{!! $lead->message ?: '<span class="text-muted">No message.</span>' !!}</div>
        </div>
    </div>

    <hr>
    <h6 class="font-weight-bold text-primary"><i class="fas fa-list-alt mr-1"></i>Booking Answers</h6>
    <div class="table-responsive mb-3">
        <table class="table table-sm table-bordered mb-0">
            <tbody>
            @forelse($lead->answers as $answer)
                <tr>
                    <th style="width:35%;">{{ $answer->field_label }}</th>
                    <td>{{ $answer->answer }}</td>
                </tr>
            @empty
                <tr><td class="text-muted text-center">No dynamic booking answers.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <hr>
    <h6 class="font-weight-bold text-primary"><i class="fas fa-tools mr-1"></i>Requested Services</h6>
    <div class="row mb-2">
        @forelse($lead->serviceLinks as $link)
            @if(!$link->trashed())
                <div class="col-md-6 mb-3">
                    <div class="border rounded p-3 h-100">
                        <strong>{{ $link->service?->name ?? 'Unavailable service' }}</strong>
                        @if($link->notes)
                            <div class="mt-2 small">{!! $link->notes !!}</div>
                        @endif
                    </div>
                </div>
            @endif
        @empty
            <div class="col-12 text-muted">No services selected.</div>
        @endforelse
    </div>

    <hr>
    <h6 class="font-weight-bold text-primary"><i class="fas fa-paperclip mr-1"></i>Private Attachments</h6>
    <div class="row mb-2">
        @forelse($lead->getMedia(\App\Models\Lead::ATTACHMENTS_COLLECTION) as $media)
            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                <div class="border rounded p-2 h-100">
                    @if($media->isImage())
                        @can('lead_attachment_download')
                            <img src="{{ route('admin.leads.attachments.preview', [$lead->id, $media->id]) }}"
                                 class="img-fluid border rounded mb-2" style="width:100%;height:110px;object-fit:cover;" alt="{{ $media->file_name }}">
                        @endcan
                    @else
                        <div class="text-center py-4 bg-light rounded mb-2"><i class="fas fa-file fa-3x text-muted"></i></div>
                    @endif
                    <small class="d-block text-truncate" title="{{ $media->file_name }}">{{ $media->file_name }}</small>
                    <small class="text-muted d-block">{{ number_format($media->size / 1024, 1) }} KB</small>
                    @can('lead_attachment_download')
                        <a href="{{ route('admin.leads.attachments.download', [$lead->id, $media->id]) }}" class="btn btn-outline-info btn-xs mt-2">
                            <i class="fas fa-download mr-1"></i>Download
                        </a>
                    @endcan
                </div>
            </div>
        @empty
            <div class="col-12 text-muted">No attachments.</div>
        @endforelse
    </div>

    <hr>
    <h6 class="font-weight-bold text-primary"><i class="fas fa-sticky-note mr-1"></i>Notes</h6>

    @if(auth('admin')->user()?->can('lead_internal_note') || auth('admin')->user()?->can('lead_public_note'))
        <form id="lead-note-form" action="{{ route('admin.leads.notes.store', $lead->id) }}" method="POST" class="mb-4">
            @csrf
            <div class="row">
                <div class="col-md-9 mb-2">
                    <textarea name="note" id="lead_note_editor_{{ $lead->id }}" class="form-control tinymce-editor" rows="5" data-editor-height="200" placeholder="Add a lead note..."></textarea>
                    <div class="invalid-feedback error-note"></div>
                    <small class="text-muted">Stored as <code>TEXT</code>, so TinyMCE is used.</small>
                </div>
                <div class="col-md-3 mb-2">
                    <label class="font-weight-bold">Visibility</label>
                    <select name="visible_to_user" class="form-control">
                        @can('lead_internal_note')<option value="0">Internal only</option>@endcan
                        @can('lead_public_note')<option value="1">Visible to user</option>@endcan
                    </select>
                    <button type="submit" class="btn btn-primary btn-block mt-2"><i class="fas fa-plus mr-1"></i>Add Note</button>
                </div>
            </div>
        </form>
    @endif

    <div class="mb-3">
        @forelse($lead->notes as $note)
            <div class="border rounded p-3 mb-2">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>{{ $note->author?->name ?? 'System / unavailable actor' }}</strong>
                        <span class="badge badge-{{ $note->visible_to_user ? 'info' : 'secondary' }} ml-1">{{ $note->visible_to_user ? 'Public' : 'Internal' }}</span>
                        <small class="text-muted ml-2">{{ optional($note->created_at)->format('d M Y H:i') }}</small>
                    </div>
                    @if(($note->visible_to_user && auth('admin')->user()?->can('lead_public_note')) || (!$note->visible_to_user && auth('admin')->user()?->can('lead_internal_note')))
                        <button type="button" class="btn btn-outline-danger btn-xs btn-delete-note"
                                data-url="{{ route('admin.leads.notes.destroy', [$lead->id, $note->id]) }}">
                            <i class="fas fa-trash"></i>
                        </button>
                    @endif
                </div>
                <div class="mt-2">{!! $note->note !!}</div>
            </div>
        @empty
            <p class="text-muted">No active notes.</p>
        @endforelse
    </div>

    @if($trashedNotes->isNotEmpty())
        <details class="mb-3">
            <summary class="font-weight-bold text-muted">Trashed Notes ({{ $trashedNotes->count() }})</summary>
            <div class="mt-2">
                @foreach($trashedNotes as $note)
                    <div class="border rounded p-2 mb-2 bg-light">
                        <div class="d-flex justify-content-between">
                            <div class="small">{!! $note->note !!}</div>
                            @if(($note->visible_to_user && auth('admin')->user()?->can('lead_public_note')) || (!$note->visible_to_user && auth('admin')->user()?->can('lead_internal_note')))
                                <button type="button" class="btn btn-outline-success btn-xs btn-restore-note"
                                        data-url="{{ route('admin.leads.notes.restore', [$lead->id, $note->id]) }}">
                                    <i class="fas fa-undo"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </details>
    @endif

    <hr>
    <h6 class="font-weight-bold text-primary"><i class="fas fa-history mr-1"></i>Status History</h6>
    <div class="table-responsive mb-3">
        <table class="table table-sm table-bordered">
            <thead><tr><th>Date</th><th>From</th><th>To</th><th>Changed By</th><th>Reason</th></tr></thead>
            <tbody>
            @forelse($lead->statusHistories as $history)
                <tr>
                    <td>{{ optional($history->created_at)->format('d M Y H:i') }}</td>
                    <td>{{ $history->from_status ? (\App\Models\Lead::STATUSES[$history->from_status] ?? $history->from_status) : '—' }}</td>
                    <td>{{ \App\Models\Lead::STATUSES[$history->to_status] ?? $history->to_status }}</td>
                    <td>{{ $history->changedBy?->name ?? 'System / unavailable actor' }}</td>
                    <td>{{ $history->reason ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">No status history.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <hr>
    <div class="row">
        <div class="col-md-3"><small class="text-muted font-weight-bold">Metadata</small><pre class="small bg-light border rounded p-2">{{ json_encode($lead->metadata, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) ?: '—' }}</pre></div>
        <div class="col-md-3"><small class="text-muted font-weight-bold">UTM</small><pre class="small bg-light border rounded p-2">{{ json_encode($lead->utm, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) ?: '—' }}</pre></div>
        <div class="col-md-3"><small class="text-muted font-weight-bold">Consent</small><pre class="small bg-light border rounded p-2">{{ json_encode($lead->consent, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) ?: '—' }}</pre></div>
        <div class="col-md-3"><small class="text-muted font-weight-bold">Pricing Snapshot</small><pre class="small bg-light border rounded p-2">{{ json_encode($lead->pricing_snapshot, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) ?: '—' }}</pre></div>
    </div>
</div>

<script>
document.dispatchEvent(new CustomEvent('admin:content-updated'));
</script>
