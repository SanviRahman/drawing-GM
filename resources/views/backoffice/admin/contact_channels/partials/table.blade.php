@php
    $trashMode = (bool) ($isTrash ?? false);
@endphp

<div class="table-responsive">
    <table class="table table-hover mb-0 contact-channel-table">
        <thead class="thead-light">
        <tr>
            <th width="42" class="text-center">
                <input type="checkbox" id="checkAll">
            </th>
            <th width="70" class="text-center">Icon</th>
            <th>Label</th>
            <th width="110">Type</th>
            <th>Value</th>
            <th width="100" class="text-center">Status</th>
            <th width="100" class="text-center">Default</th>
            <th width="110" class="text-center">Order</th>
            <th width="235" class="text-center">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($contactChannels as $channel)
            <tr>
                <td data-label="Select" class="text-center align-middle">
                    <input type="checkbox" class="row-checkbox" value="{{ $channel->id }}">
                </td>

                <td data-label="Icon" class="text-center align-middle">
                    <span class="contact-channel-icon"
                          style="background: {{ $channel->colour_css }}; {{ $channel->colour_css === '#f8f9fa' ? 'color:#343a40;' : '' }}"
                          title="{{ $channel->icon }} / {{ $channel->colour }}">
                        <i class="{{ $channel->icon_class }}"></i>
                    </span>
                </td>

                <td data-label="Label" class="align-middle">
                    <strong class="d-block text-dark">{{ $channel->label }}</strong>
                    @if($channel->region)
                        <small class="text-muted d-block"><i class="fas fa-map-marker-alt mr-1"></i>{{ $channel->region }}</small>
                    @endif
                    @if($channel->availability_text)
                        <small class="text-muted d-block"><i class="far fa-clock mr-1"></i>{{ $channel->availability_text }}</small>
                    @endif
                    @if($trashMode && $channel->deleted_at)
                        <small class="text-danger d-block mt-1"><i class="fas fa-trash-alt mr-1"></i>{{ $channel->deleted_at->diffForHumans() }}</small>
                    @endif
                </td>

                <td data-label="Type" class="align-middle">
                    <span class="badge badge-info px-2 py-1">{{ $channel->type_label }}</span>
                </td>

                <td data-label="Value" class="align-middle">
                    <strong class="d-block text-dark text-break">{{ $channel->display_value ?: $channel->value }}</strong>
                    @if($channel->display_value && $channel->display_value !== $channel->value)
                        <small class="text-muted text-break">{{ $channel->value }}</small>
                    @endif
                    <small class="d-block mt-1 text-{{ $channel->track_clicks ? 'success' : 'muted' }}">
                        <i class="fas fa-chart-line mr-1"></i>Tracking {{ $channel->track_clicks ? 'On' : 'Off' }}
                    </small>
                </td>

                <td data-label="Status" class="text-center align-middle">
                    <span class="badge badge-{{ $channel->is_active ? 'success' : 'secondary' }} px-2 py-1">
                        {{ $channel->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>

                <td data-label="Default" class="text-center align-middle">
                    @if($channel->is_default)
                        <span class="badge badge-warning px-2 py-1"><i class="fas fa-star mr-1"></i>Default</span>
                    @else
                        <span class="text-muted small">—</span>
                    @endif
                </td>

                <td data-label="Order" class="text-center align-middle">
                    @if(!$trashMode && auth('admin')->user()?->can('contact_channel_reorder'))
                        <input type="number"
                               class="form-control form-control-sm text-center sort-order-input mx-auto"
                               style="width: 78px;"
                               min="0"
                               max="2147483647"
                               step="1"
                               data-id="{{ $channel->id }}"
                               value="{{ $channel->sort_order }}">
                    @else
                        <strong>{{ $channel->sort_order }}</strong>
                    @endif
                </td>

                <td data-label="Actions" class="text-center align-middle action-cell">
                    @if($trashMode)
                        @can('contact_channel_restore')
                            <button type="button"
                                    class="btn btn-sm btn-outline-success btn-restore shadow-sm mx-1"
                                    data-url="{{ route('admin.contact_channels.restore', $channel->id) }}"
                                    title="Restore">
                                <i class="fas fa-undo"></i>
                            </button>
                        @endcan

                        @can('contact_channel_force_delete')
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger btn-force-delete shadow-sm mx-1"
                                    data-url="{{ route('admin.contact_channels.force_delete', $channel->id) }}"
                                    title="Permanent Delete">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        @endcan
                    @else
                        @can('contact_channel_view')
                            <button type="button"
                                    class="btn btn-sm btn-outline-info btn-show shadow-sm mx-1"
                                    data-url="{{ route('admin.contact_channels.show', $channel->id) }}"
                                    title="View">
                                <i class="fas fa-eye"></i>
                            </button>
                        @endcan

                        @can('contact_channel_update')
                            <button type="button"
                                    class="btn btn-sm btn-outline-primary btn-edit shadow-sm mx-1"
                                    data-url="{{ route('admin.contact_channels.edit', $channel->id) }}"
                                    title="Edit">
                                <i class="fas fa-pen"></i>
                            </button>
                        @endcan

                        @can('contact_channel_set_default')
                            @if(!$channel->is_default)
                                <button type="button"
                                        class="btn btn-sm btn-outline-warning btn-set-default shadow-sm mx-1"
                                        data-url="{{ route('admin.contact_channels.set_default', $channel->id) }}"
                                        title="Set Default">
                                    <i class="far fa-star"></i>
                                </button>
                            @endif
                        @endcan

                        @can('contact_channel_toggle')
                            <button type="button"
                                    class="btn btn-sm btn-outline-{{ $channel->is_active ? 'warning' : 'success' }} btn-toggle shadow-sm mx-1"
                                    data-url="{{ route('admin.contact_channels.toggle', $channel->id) }}"
                                    title="{{ $channel->is_active ? 'Deactivate' : 'Activate' }}">
                                <i class="fas fa-{{ $channel->is_active ? 'pause' : 'play' }}"></i>
                            </button>
                        @endcan

                        @can('contact_channel_delete')
                            <button type="button"
                                    class="btn btn-sm btn-outline-danger btn-delete shadow-sm mx-1"
                                    data-url="{{ route('admin.contact_channels.destroy', $channel->id) }}"
                                    title="Trash">
                                <i class="fas fa-trash"></i>
                            </button>
                        @endcan
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-address-book fa-3x mb-3 text-light"></i>
                        <h5 class="font-weight-bold">No Contact Channels Found</h5>
                        <p class="mb-0 small">No contact channel data is available for the current filter.</p>
                    </div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($contactChannels->hasPages())
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-3 border-top bg-light">
        <div class="text-muted small font-weight-bold mb-2 mb-md-0">
            Showing {{ $contactChannels->firstItem() ?? 0 }} to {{ $contactChannels->lastItem() ?? 0 }} of {{ $contactChannels->total() }} entries
        </div>
        <div class="m-0 pagination-sm">
            {!! $contactChannels->appends(request()->query())->links('pagination::bootstrap-4') !!}
        </div>
    </div>
@endif
