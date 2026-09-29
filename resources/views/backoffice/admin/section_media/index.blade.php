@extends('backoffice.admin.layouts.app')

@section('content')
<div id="section-media-manager" data-index-url="{{ route('admin.section_media.index') }}" data-create-url="{{ route('admin.section_media.create') }}" data-bulk-url="{{ route('admin.section_media.multiple_action') }}">
    <div class="row mb-3">
        <div class="col-md-6"><h1 class="h4 mb-1 font-weight-bold text-dark">Section Media</h1><p class="text-muted mb-0">Attach reusable Media Library items to Page Sections by role and display order.</p></div>
        <div class="col-md-6 text-md-right mt-3 mt-md-0">
            @if(auth('admin')->user()?->can('section_media_trash'))
                <a href="{{ route('admin.section_media.trashed') }}" class="btn btn-outline-danger btn-sm mr-1"><i class="fas fa-trash-alt mr-1"></i>Trash</a>
            @endif
            @if(auth('admin')->user()?->can('section_media_create'))
                <button type="button" class="btn btn-primary btn-sm" id="btnAddRecord"><i class="fas fa-plus mr-1"></i>Attach Media</button>
            @endif
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <div class="row align-items-end">
                <div class="col-lg-2 col-md-4 mb-2 mb-lg-0"><label for="bulk_action" class="text-muted small font-weight-bold text-uppercase mb-1">Bulk Action</label><div class="input-group input-group-sm"><select id="bulk_action" class="form-control shadow-none"><option value="">Choose...</option><option value="delete">Move to Trash</option></select><div class="input-group-append"><button type="button" class="btn btn-outline-primary" id="btnApplyBulk">Apply</button></div></div></div>
                <div class="col-lg-2 col-md-4 mb-2 mb-lg-0"><label for="filter_page" class="text-muted small font-weight-bold text-uppercase mb-1">Page</label><select id="filter_page" class="form-control form-control-sm shadow-none"><option value="">All Pages</option>@foreach($filters['pages'] as $page)<option value="{{ $page->id }}" @selected((string) request('page_id') === (string) $page->id)>{{ $page->title }}</option>@endforeach</select></div>
                <div class="col-lg-3 col-md-4 mb-2 mb-lg-0"><label for="filter_section" class="text-muted small font-weight-bold text-uppercase mb-1">Page Section</label><select id="filter_section" class="form-control form-control-sm shadow-none"><option value="">All Sections</option>@foreach($filters['page_sections'] as $section)<option value="{{ $section->id }}" @selected((string) request('page_section_id') === (string) $section->id)>#{{ $section->id }} — {{ $section->page?->title ?? 'Deleted Page' }} / {{ $section->sectionDefinition?->name ?? 'Deleted Definition' }}</option>@endforeach</select></div>
                <div class="col-lg-2 col-md-4 mb-2 mb-lg-0"><label for="filter_role" class="text-muted small font-weight-bold text-uppercase mb-1">Role</label><select id="filter_role" class="form-control form-control-sm shadow-none"><option value="">All Roles</option>@foreach($filters['roles'] as $role)<option value="{{ $role }}" @selected(request('role') === $role)>{{ $role }}</option>@endforeach</select></div>
                <div class="col-lg-2 col-md-5 mb-2 mb-lg-0"><label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search</label><div class="input-group input-group-sm"><input type="text" id="table_search" class="form-control shadow-none" autocomplete="off" placeholder="Media, role, page..."><div class="input-group-append"><button type="button" class="btn btn-outline-secondary" id="btnClearSearch" title="Clear"><i class="fas fa-times"></i></button><button type="button" class="btn btn-primary" id="btnSearch" title="Search"><i class="fas fa-search"></i></button></div></div></div>
                <div class="col-lg-1 col-md-3"><button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm" id="btnResetFilter" title="Reset filters"><i class="fas fa-sync-alt"></i></button></div>
            </div>
        </div>
    </div>

    @if(auth('admin')->user()?->can('section_media_list'))
        <div class="card shadow-sm border-0"><div class="card-header bg-white px-3 py-3 border-bottom"><h3 class="card-title font-weight-bold text-dark mb-0"><i class="far fa-images text-primary mr-1"></i>Section Media Assignments</h3></div><div class="card-body p-0" id="content-wrapper" style="min-height:340px;">@include('backoffice.admin.section_media.partials.table', ['sectionMedia' => $sectionMedia, 'isTrash' => false])</div></div>
    @else
        <div class="alert alert-warning border-0 shadow-sm mt-4 font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i>You do not have permission to view section media.</div>
    @endif
</div>

<div class="modal fade" id="ajaxModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document"><div class="modal-content border-0 shadow-lg"><div class="modal-header border-bottom-0 pb-0"><h5 class="modal-title font-weight-bold text-primary" id="modal-title">Section Media</h5><button type="button" class="close px-4 shadow-none" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body p-4" id="modal-body"></div></div></div></div>
@endsection

@section('plugins.Sweetalert2', true)

@push('css')
<style>#content-wrapper.loading{opacity:.5;pointer-events:none;transition:opacity .3s ease-in-out}.section-media-table td{vertical-align:middle}.section-media-preview{width:62px;height:52px;object-fit:cover}.section-media-caption{max-width:260px}</style>
@endpush

@section('js')
@include('backoffice.admin.section_media.partials.script')
@endsection
