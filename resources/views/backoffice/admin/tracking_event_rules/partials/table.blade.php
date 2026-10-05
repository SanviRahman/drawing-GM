@php
    $isTrashMode = isset($isTrash) && (bool) $isTrash;
@endphp
<div class="table-responsive">
    <table class="table table-hover mb-0 text-nowrap">
        <thead class="thead-light">
            <tr>
                <th class="text-center"><input id="checkAll" type="checkbox"></th>
                <th>Provider</th>
                <th>Internal Event</th>
                <th>Provider Event</th>
                <th class="text-center">Consent</th>
                <th class="text-center">Status</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trackingEventRules as $rule)<tr>
                <td class="text-center"><input type="checkbox" class="row-checkbox" value="{{ $rule->id }}"></td>
                <td class="font-weight-bold">{{ $rule->provider?->label() ?? 'Unavailable' }}</td>
                <td><code>{{ $rule->internal_event }}</code></td>
                <td>{{ $rule->provider_event }}</td>
                <td class="text-center"><span
                        class="badge badge-{{ $rule->requires_marketing_consent?'warning':'secondary' }}">{{ $rule->requires_marketing_consent?'Required':'No' }}</span>
                </td>
                <td class="text-center"><span
                        class="badge badge-{{ $rule->is_enabled?'success':'secondary' }}">{{ $rule->is_enabled?'Enabled':'Disabled' }}</span>
                </td>
                <td class="text-center">
                    @if($isTrashMode)
                    @can('tracking_event_rule_restore')<button class="btn btn-sm btn-outline-success btn-restore"
                        data-url="{{ route('admin.tracking_event_rules.restore',$rule->id) }}"><i
                            class="fas fa-undo"></i></button>@endcan
                    @can('tracking_event_rule_force_delete')<button
                        class="btn btn-sm btn-outline-danger btn-force-delete"
                        data-url="{{ route('admin.tracking_event_rules.force_delete',$rule->id) }}"><i
                            class="fas fa-trash"></i></button>@endcan
                    @else
                    @can('tracking_event_rule_view')<button class="btn btn-sm btn-outline-info btn-show"
                        data-url="{{ route('admin.tracking_event_rules.show',$rule->id) }}"><i
                            class="fas fa-eye"></i></button>@endcan
                    @can('tracking_event_rule_update')<button class="btn btn-sm btn-outline-primary btn-edit"
                        data-url="{{ route('admin.tracking_event_rules.edit',$rule->id) }}"><i
                            class="fas fa-pen"></i></button>@endcan
                    @can('tracking_event_rule_toggle')<button class="btn btn-sm btn-outline-warning btn-toggle"
                        data-url="{{ route('admin.tracking_event_rules.toggle',$rule->id) }}"><i
                            class="fas fa-power-off"></i></button>@endcan
                    @can('tracking_event_rule_delete')<button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="{{ route('admin.tracking_event_rules.destroy',$rule->id) }}"><i
                            class="fas fa-trash"></i></button>@endcan
                    @endif
                </td>
            </tr>@empty<tr>
                <td colspan="7" class="text-center py-5 text-muted">No tracking event rules found.</td>
            </tr>@endforelse
        </tbody>
    </table>
</div>@if($trackingEventRules->hasPages())<div class="p-3">{{ $trackingEventRules->links() }}</div>@endif