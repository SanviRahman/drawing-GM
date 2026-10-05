@php
    $isTrashMode = isset($isTrash) && (bool) $isTrash;
@endphp

<div class="table-responsive">
<table class="table table-hover mb-0">
    <thead class="thead-light">
        <tr>
            <th style="width:42px;"><input type="checkbox" id="checkAll"></th>
            <th>Label</th>
            <th>Key</th>
            <th>Options</th>
            <th class="text-center">Required</th>
            <th class="text-center">Status</th>
            <th class="text-center">Order</th>
            <th class="text-right">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($leadFormFields as $field)
            @php
                $fieldOptions = is_array($field->options) ? $field->options : [];
            @endphp
            <tr data-id="{{ $field->id }}">
                <td class="align-middle"><input type="checkbox" class="row-checkbox" value="{{ $field->id }}"></td>
                <td class="align-middle font-weight-bold">{{ $field->label }}</td>
                <td class="align-middle"><code>{{ $field->field_key }}</code></td>
                <td class="align-middle"><span class="badge badge-light border">{{ count($fieldOptions) }} options</span></td>
                <td class="text-center align-middle">{{ $field->is_required ? 'Yes' : 'No' }}</td>
                <td class="text-center align-middle">
                    <span class="badge badge-{{ $field->is_active ? 'success' : 'secondary' }}">{{ $field->is_active ? 'Active' : 'Inactive' }}</span>
                </td>
                <td class="text-center align-middle">
                    @if($isTrashMode)
                        {{ $field->sort_order }}
                    @else
                        <input type="number" class="form-control form-control-sm text-center field-sort-order mx-auto" min="0" value="{{ $field->sort_order }}" style="width:80px;">
                    @endif
                </td>
                <td class="text-right align-middle action-cell">
                    @if($isTrashMode)
                        @can('lead_form_field_restore')
                            <button type="button" class="btn btn-sm btn-outline-success btn-restore" data-url="{{ route('admin.lead_form_fields.restore', $field->id) }}" title="Restore"><i class="fas fa-undo"></i></button>
                        @endcan
                        @can('lead_form_field_force_delete')
                            <button type="button" class="btn btn-sm btn-outline-danger btn-force-delete" data-url="{{ route('admin.lead_form_fields.force_delete', $field->id) }}" title="Permanently Delete"><i class="fas fa-trash-alt"></i></button>
                        @endcan
                    @else
                        @can('lead_form_field_view')
                            <button type="button" class="btn btn-sm btn-outline-info btn-show" data-url="{{ route('admin.lead_form_fields.show', $field->id) }}" title="View"><i class="fas fa-eye"></i></button>
                        @endcan
                        @can('lead_form_field_update')
                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit" data-url="{{ route('admin.lead_form_fields.edit', $field->id) }}" title="Edit"><i class="fas fa-pen"></i></button>
                        @endcan
                        @can('lead_form_field_toggle')
                            <button type="button" class="btn btn-sm btn-outline-{{ $field->is_active ? 'warning' : 'success' }} btn-toggle" data-url="{{ route('admin.lead_form_fields.toggle', $field->id) }}" title="{{ $field->is_active ? 'Deactivate' : 'Activate' }}"><i class="fas fa-power-off"></i></button>
                        @endcan
                        @can('lead_form_field_delete')
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete" data-url="{{ route('admin.lead_form_fields.destroy', $field->id) }}" title="Move to Trash"><i class="fas fa-trash"></i></button>
                        @endcan
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center py-5 text-muted">
                    <i class="fas fa-list-alt fa-3x text-light mb-3 d-block"></i>No booking fields found.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
</div>

@if($leadFormFields->hasPages())
<div class="d-flex justify-content-between align-items-center px-3 py-3 border-top bg-light">
    <small class="text-muted font-weight-bold">Showing {{ $leadFormFields->firstItem() ?: 0 }} to {{ $leadFormFields->lastItem() ?: 0 }} of {{ $leadFormFields->total() }}</small>
    {{ $leadFormFields->links() }}
</div>
@endif
