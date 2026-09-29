@extends('backoffice.admin.layouts.app')

@section('content')
<div id="page-section-manager" data-index-url="{{ route('admin.page_sections.index') }}" data-create-url="{{ route('admin.page_sections.create') }}" data-bulk-url="{{ route('admin.page_sections.multiple_action') }}">
    <div class="row mb-3">
        <div class="col-md-6"><h1 class="h4 mb-1 font-weight-bold text-dark">Page Sections</h1><p class="text-muted mb-0">Attach configured section definitions to pages, control order, schedule and visibility.</p></div>
        <div class="col-md-6 text-md-right mt-3 mt-md-0">
            @if(auth('admin')->user()?->can('page_section_trash'))<a href="{{ route('admin.page_sections.trashed') }}" class="btn btn-outline-danger btn-sm mr-1"><i class="fas fa-trash-alt mr-1"></i>Trash</a>@endif
            @if(auth('admin')->user()?->can('page_section_create'))<button type="button" class="btn btn-primary btn-sm" id="btnAddRecord"><i class="fas fa-plus mr-1"></i>Add Page Section</button>@endif
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <div class="row align-items-end">
                <div class="col-lg-2 col-md-4 mb-2 mb-lg-0"><label for="bulk_action" class="text-muted small font-weight-bold text-uppercase mb-1">Bulk Action</label><div class="input-group input-group-sm"><select id="bulk_action" class="form-control shadow-none"><option value="">Choose...</option><option value="activate">Activate</option><option value="deactivate">Deactivate</option><option value="delete">Move to Trash</option></select><div class="input-group-append"><button type="button" class="btn btn-outline-primary" id="btnApplyBulk">Apply</button></div></div></div>
                <div class="col-lg-2 col-md-4 mb-2 mb-lg-0"><label for="filter_page" class="text-muted small font-weight-bold text-uppercase mb-1">Page</label><select id="filter_page" class="form-control form-control-sm shadow-none"><option value="">All Pages</option>@foreach($filters['pages'] as $page)<option value="{{ $page->id }}" @selected((string) request('page_id') === (string) $page->id)>{{ $page->title }}</option>@endforeach</select></div>
                <div class="col-lg-2 col-md-4 mb-2 mb-lg-0"><label for="filter_definition" class="text-muted small font-weight-bold text-uppercase mb-1">Section Type</label><select id="filter_definition" class="form-control form-control-sm shadow-none"><option value="">All Types</option>@foreach($filters['section_definitions'] as $definition)<option value="{{ $definition->id }}" @selected((string) request('section_definition_id') === (string) $definition->id)>{{ $definition->name }}</option>@endforeach</select></div>
                <div class="col-lg-1 col-md-3 mb-2 mb-lg-0"><label for="filter_active" class="text-muted small font-weight-bold text-uppercase mb-1">Status</label><select id="filter_active" class="form-control form-control-sm shadow-none"><option value="">All</option><option value="1">Active</option><option value="0">Inactive</option></select></div>
                <div class="col-lg-3 col-md-6 mb-2 mb-lg-0"><label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search</label><div class="input-group input-group-sm"><input type="text" id="table_search" class="form-control shadow-none" autocomplete="off" placeholder="Heading, page, section, theme..."><div class="input-group-append"><button type="button" class="btn btn-outline-secondary" id="btnClearSearch" title="Clear"><i class="fas fa-times"></i></button><button type="button" class="btn btn-primary" id="btnSearch" title="Search"><i class="fas fa-search"></i></button></div></div></div>
                <div class="col-lg-2 col-md-3"><button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm" id="btnResetFilter"><i class="fas fa-sync-alt mr-1"></i>Reset</button></div>
            </div>
        </div>
    </div>

    @if(auth('admin')->user()?->can('page_section_list'))
        <div class="card shadow-sm border-0"><div class="card-header bg-white px-3 py-3 border-bottom"><h3 class="card-title font-weight-bold text-dark mb-0"><i class="fas fa-th-large text-primary mr-1"></i>Configured Page Sections</h3></div><div class="card-body p-0" id="content-wrapper" style="min-height:340px;">@include('backoffice.admin.page_sections.partials.table', ['pageSections' => $pageSections, 'isTrash' => false])</div></div>
    @else
        <div class="alert alert-warning border-0 shadow-sm mt-4 font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i>You do not have permission to view page sections.</div>
    @endif
</div>

<div class="modal fade" id="ajaxModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document"><div class="modal-content border-0 shadow-lg"><div class="modal-header border-bottom-0 pb-0"><h5 class="modal-title font-weight-bold text-primary" id="modal-title">Page Section</h5><button type="button" class="close px-4 shadow-none" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body p-4" id="modal-body"></div></div></div></div>
@endsection

@section('plugins.Sweetalert2', true)

@push('css')
<style>#content-wrapper.loading{opacity:.5;pointer-events:none;transition:opacity .3s ease-in-out}.page-section-table td{vertical-align:middle}.payload-preview{max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}</style>
@endpush

@section('js')
@include('backoffice.admin.page_sections.partials.script')
@endsection
