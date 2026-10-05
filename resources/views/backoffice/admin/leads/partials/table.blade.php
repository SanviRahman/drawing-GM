<div class="table-responsive">
<table class="table table-hover mb-0">
    <thead class="thead-light">
        <tr>
            <th style="width:42px;"><input type="checkbox" id="checkAll"></th>
            <th>Reference / Client</th>
            <th>Contact</th>
            <th>Services</th>
            <th>Status</th>
            <th>Assignee</th>
            <th>Created</th>
            <th class="text-right">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($leads as $lead)
            <tr>
                <td class="align-middle"><input type="checkbox" class="row-checkbox" value="{{ $lead->id }}"></td>
                <td class="align-middle">
                    <div class="font-weight-bold text-primary">{{ $lead->reference }}</div>
                    <div>{{ $lead->name }}</div>
                    @if($lead->location)<small class="text-muted"><i class="fas fa-map-marker-alt mr-1"></i>{{ $lead->location->name }}</small>@endif
                </td>
                <td class="align-middle">
                    <div><i class="fas fa-phone mr-1 text-muted"></i>{{ $lead->phone }}</div>
                    @if($lead->email)<small class="text-muted"><i class="fas fa-envelope mr-1"></i>{{ $lead->email }}</small>@endif
                </td>
                <td class="align-middle">
                    @forelse($lead->services->take(3) as $service)
                        <span class="badge badge-light border mr-1 mb-1">{{ $service->name }}</span>
                    @empty
                        <span class="text-muted small">No services</span>
                    @endforelse
                    @if($lead->services->count() > 3)
                        <span class="badge badge-secondary">+{{ $lead->services->count() - 3 }}</span>
                    @endif
                </td>
                <td class="align-middle">
                    <span class="badge badge-{{ $lead->statusBadgeClass() }} px-2 py-1">{{ $lead->statusLabel() }}</span>
                </td>
                <td class="align-middle">{{ $lead->assignee?->name ?? 'Unassigned' }}</td>
                <td class="align-middle"><small>{{ optional($lead->created_at)->format('d M Y H:i') }}</small></td>
                <td class="text-right align-middle action-cell">
                    @if($isTrash ?? false)
                        @can('lead_restore')
                            <button type="button" class="btn btn-sm btn-outline-success btn-restore" data-url="{{ route('admin.leads.restore', $lead->id) }}" title="Restore"><i class="fas fa-undo"></i></button>
                        @endcan
                        @can('lead_force_delete')
                            <button type="button" class="btn btn-sm btn-outline-danger btn-force-delete" data-url="{{ route('admin.leads.force_delete', $lead->id) }}" title="Permanent Delete"><i class="fas fa-trash-alt"></i></button>
                        @endcan
                    @else
                        @can('lead_view')
                            <button type="button" class="btn btn-sm btn-outline-info btn-show" data-url="{{ route('admin.leads.show', $lead->id) }}" title="View"><i class="fas fa-eye"></i></button>
                        @endcan
                        @can('lead_update')
                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-url="{{ route('admin.leads.edit', $lead->id) }}" title="Edit"><i class="fas fa-pen"></i></button>
                        @endcan
                        @can('lead_change_status')
                            <button type="button" class="btn btn-sm btn-outline-warning btn-change-status"
                                    data-url="{{ route('admin.leads.change_status', $lead->id) }}"
                                    data-status="{{ $lead->status }}" title="Change Status"><i class="fas fa-exchange-alt"></i></button>
                        @endcan
                        @can('lead_delete')
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-url="{{ route('admin.leads.destroy', $lead->id) }}" title="Trash"><i class="fas fa-trash"></i></button>
                        @endcan
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="text-center py-5 text-muted"><i class="fas fa-user-tag fa-3x text-light mb-3 d-block"></i>No leads found.</td></tr>
        @endforelse
    </tbody>
</table>
</div>

@if($leads->hasPages())
<div class="d-flex justify-content-between align-items-center px-3 py-3 border-top bg-light">
    <small class="text-muted font-weight-bold">Showing {{ $leads->firstItem() ?? 0 }} to {{ $leads->lastItem() ?? 0 }} of {{ $leads->total() }}</small>
    {{ $leads->links() }}
</div>
@endif
