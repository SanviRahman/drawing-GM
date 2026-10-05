@php
    $isTrashMode = isset($isTrash) && (bool) $isTrash;
@endphp
<div class="table-responsive">
    <table class="table table-hover mb-0 text-nowrap">
        <thead class="thead-light">
            <tr>
                <th class="text-center"><input id="checkAll" type="checkbox"></th>
                <th>Provider</th>
                <th>Public ID</th>
                <th class="text-center">Pixels</th>
                <th class="text-center">Rules</th>
                <th class="text-center">Mode</th>
                <th class="text-center">Status</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trackingProviders as $provider)<tr>
                <td class="text-center align-middle"><input type="checkbox" class="row-checkbox"
                        value="{{ $provider->id }}"></td>
                <td class="align-middle font-weight-bold">{{ $provider->label() }}</td>
                <td class="align-middle"><code>{{ $provider->public_identifier ?: '—' }}</code></td>
                <td class="text-center align-middle">
                    {{ $provider->provider==='meta_pixel' ? count($provider->metaPixels()) : '—' }}</td>
                <td class="text-center align-middle">
                    {{ $provider->event_rules_count ?? $provider->eventRules()->count() }}</td>
                <td class="text-center align-middle"><span
                        class="badge badge-{{ $provider->test_mode?'warning':'primary' }}">{{ $provider->test_mode?'Test':'Live' }}</span>
                </td>
                <td class="text-center align-middle"><span
                        class="badge badge-{{ $provider->is_enabled?'success':'secondary' }}">{{ $provider->is_enabled?'Enabled':'Disabled' }}</span>
                </td>
                <td class="text-center align-middle">
                    @if($isTrashMode)
                    @can('tracking_provider_restore')<button class="btn btn-sm btn-outline-success btn-restore"
                        data-url="{{ route('admin.tracking.restore',$provider->id) }}"><i
                            class="fas fa-undo"></i></button>@endcan
                    @can('tracking_provider_force_delete')<button class="btn btn-sm btn-outline-danger btn-force-delete"
                        data-url="{{ route('admin.tracking.force_delete',$provider->id) }}"><i
                            class="fas fa-trash"></i></button>@endcan
                    @else
                    @can('tracking_provider_view')<button class="btn btn-sm btn-outline-info btn-show"
                        data-url="{{ route('admin.tracking.show',$provider->id) }}"><i
                            class="fas fa-eye"></i></button>@endcan
                    @can('tracking_provider_update')<button class="btn btn-sm btn-outline-primary btn-edit"
                        data-url="{{ route('admin.tracking.edit',$provider->id) }}"><i
                            class="fas fa-pen"></i></button>@endcan
                    @can('tracking_test_event')<button class="btn btn-sm btn-outline-dark btn-test"
                        data-url="{{ route('admin.tracking.test',$provider->id) }}"><i
                            class="fas fa-vial"></i></button>@endcan
                    @can('tracking_provider_toggle')<button class="btn btn-sm btn-outline-warning btn-toggle"
                        data-url="{{ route('admin.tracking.toggle',$provider->id) }}"><i
                            class="fas fa-power-off"></i></button>@endcan
                    @can('tracking_provider_delete')<button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="{{ route('admin.tracking.destroy',$provider->id) }}"><i
                            class="fas fa-trash"></i></button>@endcan
                    @endif
                </td>
            </tr>@empty<tr>
                <td colspan="8" class="text-center py-5 text-muted">No tracking providers found.</td>
            </tr>@endforelse
        </tbody>
    </table>
</div>@if($trackingProviders->hasPages())<div class="p-3">{{ $trackingProviders->links() }}</div>@endif