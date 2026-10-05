@php
    $isTrashMode = isset($isTrash) && (bool) $isTrash;
@endphp
<div class="table-responsive">
    <table class="table table-hover mb-0 text-nowrap">
        <thead class="thead-light">
            <tr>
                <th class="text-center"><input type="checkbox" id="checkAll"></th>
                <th>From</th>
                <th>To</th>
                <th class="text-center">Code</th>
                <th class="text-center">Hits</th>
                <th class="text-center">Status</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($redirects as $redirect)<tr>
                <td class="text-center align-middle"><input type="checkbox" class="row-checkbox"
                        value="{{ $redirect->id }}"></td>
                <td class="align-middle"><code>{{ \Illuminate\Support\Str::limit($redirect->from_path,55) }}</code></td>
                <td class="align-middle text-muted">{{ \Illuminate\Support\Str::limit($redirect->to_url,65) }}</td>
                <td class="text-center align-middle"><span class="badge badge-info">{{ $redirect->status_code }}</span>
                </td>
                <td class="text-center align-middle">{{ number_format($redirect->hits) }}</td>
                <td class="text-center align-middle"><span
                        class="badge badge-{{ $redirect->is_active?'success':'secondary' }}">{{ $redirect->is_active?'Active':'Inactive' }}</span>
                </td>
                <td class="text-center align-middle">
                    @if($isTrashMode)
                    @can('redirect_restore')<button class="btn btn-sm btn-outline-success btn-restore"
                        data-url="{{ route('admin.redirects.restore',$redirect->id) }}"><i
                            class="fas fa-undo"></i></button>@endcan
                    @can('redirect_force_delete')<button class="btn btn-sm btn-outline-danger btn-force-delete"
                        data-url="{{ route('admin.redirects.force_delete',$redirect->id) }}"><i
                            class="fas fa-trash"></i></button>@endcan
                    @else
                    @can('redirect_view')<button class="btn btn-sm btn-outline-info btn-show"
                        data-url="{{ route('admin.redirects.show',$redirect->id) }}"><i
                            class="fas fa-eye"></i></button>@endcan
                    @can('redirect_update')<button class="btn btn-sm btn-outline-primary btn-edit"
                        data-url="{{ route('admin.redirects.edit',$redirect->id) }}"><i
                            class="fas fa-pen"></i></button><button class="btn btn-sm btn-outline-dark btn-reset-hits"
                        data-url="{{ route('admin.redirects.reset_hits',$redirect->id) }}" title="Reset hits"><i
                            class="fas fa-eraser"></i></button>@endcan
                    @can('redirect_toggle')<button class="btn btn-sm btn-outline-warning btn-toggle"
                        data-url="{{ route('admin.redirects.toggle',$redirect->id) }}"><i
                            class="fas fa-power-off"></i></button>@endcan
                    @can('redirect_delete')<button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="{{ route('admin.redirects.destroy',$redirect->id) }}"><i
                            class="fas fa-trash"></i></button>@endcan
                    @endif
                </td>
            </tr>
            @empty<tr>
                <td colspan="7" class="text-center py-5 text-muted">No redirects found.</td>
            </tr>@endforelse
        </tbody>
    </table>
</div>@if($redirects->hasPages())<div class="p-3">{{ $redirects->links() }}</div>@endif