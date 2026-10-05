@php($isTrashMode = isset($isTrash) && (bool)$isTrash)
<div class="table-responsive"><table class="table table-hover mb-0 text-nowrap"><thead class="thead-light"><tr><th class="text-center"><input id="checkAll" type="checkbox"></th><th>Date</th><th>Actor</th><th>Action</th><th>Auditable</th><th>IP</th><th class="text-center">Actions</th></tr></thead><tbody>
@forelse($auditLogs as $log)
<tr><td class="text-center align-middle"><input class="row-checkbox" type="checkbox" value="{{ $log->id }}"></td><td class="align-middle">{{ $log->created_at?->format('d M Y H:i:s') }}</td><td class="align-middle">{{ $log->actor?->name ?? 'System/Guest' }}<div class="small text-muted">{{ class_basename((string)$log->actor_type) }}</div></td><td class="align-middle"><code>{{ $log->action }}</code></td><td class="align-middle">{{ class_basename($log->auditable_type) }} @if($log->auditable_id)<span class="text-muted">#{{ $log->auditable_id }}</span>@endif</td><td class="align-middle">{{ $log->ip_address ?: '—' }}</td><td class="text-center align-middle">
@if($isTrashMode)
@can('audit_log_restore')<button type="button" class="btn btn-sm btn-outline-success btn-restore" data-url="{{ route('admin.audit_logs.restore',$log->id) }}" title="Restore"><i class="fas fa-undo"></i></button>@endcan
@can('audit_log_force_delete')<button type="button" class="btn btn-sm btn-outline-danger btn-force-delete" data-url="{{ route('admin.audit_logs.force_delete',$log->id) }}" title="Force Delete"><i class="fas fa-trash"></i></button>@endcan
@else
@can('audit_log_view')<button type="button" class="btn btn-sm btn-outline-info btn-show" data-url="{{ route('admin.audit_logs.show',$log->id) }}" title="View"><i class="fas fa-eye"></i></button>@endcan
@can('audit_log_delete')<button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-url="{{ route('admin.audit_logs.destroy',$log->id) }}" title="Trash"><i class="fas fa-trash"></i></button>@endcan
@endif
</td></tr>
@empty<tr><td colspan="7" class="text-center py-5 text-muted">No audit logs found.</td></tr>@endforelse
</tbody></table></div>
@if($auditLogs->hasPages())<div class="p-3">{{ $auditLogs->links('pagination::bootstrap-4') }}</div>@endif
