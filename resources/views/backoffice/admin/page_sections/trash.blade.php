@extends('backoffice.admin.layouts.app')

@section('content')
<div id="page-section-manager" data-index-url="{{ route('admin.page_sections.trashed') }}" data-bulk-url="{{ route('admin.page_sections.multiple_action') }}">
    <div class="row mb-3">
        <div class="col-md-6"><h1 class="h4 mb-1 font-weight-bold text-dark">Trashed Page Sections</h1><p class="text-muted mb-0">Restore page sections or permanently delete records that are no longer referenced by section media.</p></div>
        <div class="col-md-6 text-md-right mt-3 mt-md-0"><a href="{{ route('admin.page_sections.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left mr-1"></i>Back to Page Sections</a></div>
    </div>

    <div class="card shadow-sm border-0 mb-3"><div class="card-body py-3"><div class="row align-items-end">
        <div class="col-lg-3 col-md-5 mb-2 mb-lg-0"><label for="bulk_action" class="text-muted small font-weight-bold text-uppercase mb-1">Bulk Action</label><div class="input-group input-group-sm"><select id="bulk_action" class="form-control shadow-none"><option value="">Choose...</option><option value="restore">Restore</option><option value="force_delete">Permanently Delete</option></select><div class="input-group-append"><button type="button" class="btn btn-outline-primary" id="btnApplyBulk">Apply</button></div></div></div>
        <div class="col-lg-2 col-md-4 mb-2 mb-lg-0"><label for="filter_page" class="text-muted small font-weight-bold text-uppercase mb-1">Page</label><select id="filter_page" class="form-control form-control-sm shadow-none"><option value="">All Pages</option>@foreach($filters['pages'] as $page)<option value="{{ $page->id }}">{{ $page->title }}</option>@endforeach</select></div>
        <div class="col-lg-2 col-md-4 mb-2 mb-lg-0"><label for="filter_definition" class="text-muted small font-weight-bold text-uppercase mb-1">Section Type</label><select id="filter_definition" class="form-control form-control-sm shadow-none"><option value="">All Types</option>@foreach($filters['section_definitions'] as $definition)<option value="{{ $definition->id }}">{{ $definition->name }}</option>@endforeach</select></div>
        <div class="col-lg-3 col-md-6 mb-2 mb-lg-0"><label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search</label><div class="input-group input-group-sm"><input type="text" id="table_search" class="form-control shadow-none" autocomplete="off" placeholder="Heading, page, section, theme..."><div class="input-group-append"><button type="button" class="btn btn-outline-secondary" id="btnClearSearch"><i class="fas fa-times"></i></button><button type="button" class="btn btn-primary" id="btnSearch"><i class="fas fa-search"></i></button></div></div></div>
        <div class="col-lg-2 col-md-3"><button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold" id="btnResetFilter"><i class="fas fa-sync-alt mr-1"></i>Reset</button></div>
    </div></div></div>

    <div class="card shadow-sm border-0"><div class="card-header bg-white px-3 py-3 border-bottom"><h3 class="card-title font-weight-bold text-dark mb-0"><i class="fas fa-trash-alt text-danger mr-1"></i>Trash Bin</h3></div><div class="card-body p-0" id="content-wrapper" style="min-height:340px;">@include('backoffice.admin.page_sections.partials.table', ['pageSections' => $pageSections, 'isTrash' => true])</div></div>
</div>

<div class="modal fade" id="ajaxModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document"><div class="modal-content border-0 shadow-lg"><div class="modal-header border-bottom-0 pb-0"><h5 class="modal-title font-weight-bold text-primary" id="modal-title">Page Section</h5><button type="button" class="close px-4 shadow-none" data-dismiss="modal"><span aria-hidden="true">&times;</span></button></div><div class="modal-body p-4" id="modal-body"></div></div></div></div>
@endsection

@section('plugins.Sweetalert2', true)

@push('css')
<style>#content-wrapper.loading{opacity:.5;pointer-events:none;transition:opacity .3s ease-in-out}.page-section-table td{vertical-align:middle}.payload-preview{max-width:260px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}</style>
@endpush

@section('js')
@include('backoffice.admin.page_sections.partials.script')
@endsection
