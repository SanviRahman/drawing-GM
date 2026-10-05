@php
    $trashMode = (bool) ($isTrash ?? false);
@endphp


<div class="table-responsive">
    <table class="table table-hover table-striped mb-0">
        <thead class="thead-light">
        <tr>
            <th style="width: 45px;" class="text-center">
                <input type="checkbox" id="checkAll">
            </th>
            <th>Contact Channel</th>
            <th>Channel Type</th>
            <th>Target Type</th>
            <th>Target</th>
            <th class="text-center">Channel</th>
            <th class="text-center" style="width: 170px;">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($contactTargets as $contactTarget)
            @php
                $channel = $contactTarget->channel;
                $targetRecord = $contactTarget->targetable ?: $contactTarget->targetRecord(true);
                $targetTrashed = $targetRecord && method_exists($targetRecord, 'trashed') && $targetRecord->trashed();
            @endphp
            <tr>
                <td class="text-center align-middle">
                    <input type="checkbox" class="row-checkbox" value="{{ $contactTarget->id }}">
                </td>

                <td class="align-middle">
                    <div class="font-weight-bold text-dark">{{ $channel?->label ?? 'Unavailable channel' }}</div>
                    @if($channel?->region)
                        <small class="text-muted">{{ $channel->region }}</small>
                    @endif
                    @if($channel?->trashed())
                        <span class="badge badge-danger ml-1">Channel Trashed</span>
                    @endif
                </td>

                <td class="align-middle">
                    <span class="badge badge-info text-uppercase">{{ $channel?->type_label ?? 'Unknown' }}</span>
                </td>

                <td class="align-middle">
                    <span class="badge badge-secondary">{{ $contactTarget->target_type_label }}</span>
                </td>

                <td class="align-middle">
                    <div class="font-weight-bold">{{ $contactTarget->target_name }}</div>
                    <small class="text-muted">ID: {{ $contactTarget->targetable_id }}</small>
                    @if($targetTrashed)
                        <span class="badge badge-danger ml-1">Target Trashed</span>
                    @elseif(! $targetRecord)
                        <span class="badge badge-danger ml-1">Unavailable</span>
                    @endif
                </td>

                <td class="text-center align-middle">
                    @if($channel && ! $channel->trashed() && $channel->is_active)
                        <span class="badge badge-success px-2 py-1">Active</span>
                    @elseif($channel && ! $channel->trashed())
                        <span class="badge badge-secondary px-2 py-1">Inactive</span>
                    @else
                        <span class="badge badge-danger px-2 py-1">Unavailable</span>
                    @endif
                </td>

                <td class="text-center align-middle action-cell text-nowrap">
                    @if($trashMode)
                        @can('contact_target_restore')
                            <button type="button"
                                    class="btn btn-sm btn-outline-success btn-restore shadow-sm mx-1"
                                    data-url="{{ route('admin.contact_targets.restore', $contactTarget->id) }}"
                                    title="Restore">
                                <i class="fas fa-undo"></i>
                            </button>
                        @endcan

                        @can('contact_target_force_delete')
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger btn-force-delete shadow-sm mx-1"
                                    data-url="{{ route('admin.contact_targets.force_delete', $contactTarget->id) }}"
                                    title="Permanent Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        @endcan
                    @else
                        @can('contact_target_view')
                            <button type="button"
                                    class="btn btn-sm btn-outline-info btn-show shadow-sm mx-1"
                                    data-url="{{ route('admin.contact_targets.show', $contactTarget->id) }}"
                                    title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                        @endcan

                        @can('contact_target_update')
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary btn-edit shadow-sm mx-1"
                                    data-url="{{ route('admin.contact_targets.edit', $contactTarget->id) }}"
                                    title="Edit">
                                <i class="fas fa-pen"></i>
                            </button>
                        @endcan

                        @can('contact_target_delete')
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger btn-delete shadow-sm mx-1"
                                    data-url="{{ route('admin.contact_targets.destroy', $contactTarget->id) }}"
                                    title="Trash">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endcan
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-crosshairs fa-3x mb-3 text-light"></i>
                        <h5 class="font-weight-bold">No Contact Targets Found</h5>
                        <p class="mb-0 small">{{ $trashMode ? 'No mappings are currently in Trash.' : 'Create a target mapping or adjust your filters.' }}</p>
                    </div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($contactTargets->hasPages())
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-3 border-top bg-light">
        <div class="text-muted small font-weight-bold mb-2 mb-md-0">
            Showing {{ $contactTargets->firstItem() ?? 0 }} to {{ $contactTargets->lastItem() ?? 0 }} of {{ $contactTargets->total() }}
        </div>
        <div>{{ $contactTargets->links() }}</div>
    </div>
@endif
