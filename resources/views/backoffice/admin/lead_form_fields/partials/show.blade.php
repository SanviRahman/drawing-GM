@php
    $fieldOptions = is_array($leadFormField->options) ? $leadFormField->options : [];
@endphp

<div class="row">
    <div class="col-md-8 mb-3">
        <small class="text-muted text-uppercase font-weight-bold">Label</small>
        <div class="font-weight-bold">{{ $leadFormField->label }}</div>
    </div>
    <div class="col-md-4 mb-3">
        <small class="text-muted text-uppercase font-weight-bold">Field Key</small>
        <div><code>{{ $leadFormField->field_key }}</code></div>
    </div>
    <div class="col-md-8 mb-3">
        <small class="text-muted text-uppercase font-weight-bold">Placeholder</small>
        <div>{{ $leadFormField->placeholder ?: '—' }}</div>
    </div>
    <div class="col-md-2 mb-3">
        <small class="text-muted text-uppercase font-weight-bold">Required</small>
        <div><span class="badge badge-{{ $leadFormField->is_required ? 'warning' : 'secondary' }}">{{ $leadFormField->is_required ? 'Yes' : 'No' }}</span></div>
    </div>
    <div class="col-md-2 mb-3">
        <small class="text-muted text-uppercase font-weight-bold">Status</small>
        <div><span class="badge badge-{{ $leadFormField->is_active ? 'success' : 'secondary' }}">{{ $leadFormField->is_active ? 'Active' : 'Inactive' }}</span></div>
    </div>
    <div class="col-md-12">
        <small class="text-muted text-uppercase font-weight-bold">Options</small>
        <ol class="pl-4 mt-2 mb-0">
            @forelse($fieldOptions as $option)
                <li>{{ $option }}</li>
            @empty
                <li class="text-muted">No options configured.</li>
            @endforelse
        </ol>
    </div>
</div>
