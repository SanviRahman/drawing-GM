@extends('backoffice.admin.layouts.app')

@section('content')
<div id="section-media-manager" data-index-url="{{ route('admin.section_media.trashed') }}" data-bulk-url="{{ route('admin.section_media.multiple_action') }}">
    <div class="row mb-3">
        <div class="col-md-6"><h1 class="h4 mb-1 font-weight-bold text-dark">Trashed Section Media</h1><p class="text-muted mb-0">Restore media assignments or permanently delete their mapping records.</p></div>
        <div class="col-md-6 text-md-right mt-3 mt-md-0"><a href="{{ route('admin.section_media.index') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-arrow-left mr-1"></i>Back to Section Media</a></div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <div class="row align-items-end">
                <div class="col-lg-3 col-md-5 mb-2 mb-lg-0"><label for="bulk_action" class="text-muted small font-weight-bold text-uppercase mb-1">Bulk Action</label><div class="input-group input-group-sm"><select id="bulk_action" class="form-control shadow-none"><option value="">Choose...</option><option value="restore">Restore</option><option value="force_delete">Permanently Delete</option></select><div class="input-group-append"><button type="button" class="btn btn-outline-primary" id="btnApplyBulk">Apply</button></div></div></div>
                <div class="col-lg-2 col-md-4 mb-2 mb-lg-0"><label for="filter_page" class="text-muted small font-weight-bold text-uppercase mb-1">Page</label><select id="filter_page" class="form-control form-control-sm shadow-none"><option value="">All Pages</option>@foreach($filters['pages'] as $page)<option value="{{ $page->id }}">{{ $page->title }}</option>@endforeach</select></div>
                <div class="col-lg-3 col-md-4 mb-2 mb-lg-0"><label for="filter_section" class="text-muted small font-weight-bold text-uppercase mb-1">Page Section</label><select id="filter_section" class="form-control form-control-sm shadow-none"><option value="">All Sections</option>@foreach($filters['page_sections'] as $section)<option value="{{ $section->id }}">#{{ $section->id }} — {{ $section->page?->title ?? 'Deleted Page' }} / {{ $section->sectionDefinition?->name ?? 'Deleted Definition' }}</option>@endforeach</select></div>
                <div class="col-lg-2 col-md-4 mb-2 mb-lg-0"><label for="filter_role" class="text-muted small font-weight-bold text-uppercase mb-1">Role</label><select id="filter_role" class="form-control form-control-sm shadow-none"><option value="">All Roles</option>@foreach($filters['roles'] as $role)<option value="{{ $role }}">{{ $role }}</option>@endforeach</select></div>
                <div class="col-lg-2 col-md-5 mb-2 mb-lg-0"><label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search</label><div class="input-group input-group-sm"><input type="text" id="table_search" class="form-control shadow-none" autocomplete="off" placeholder="Media, role, page..."><div class="input-group-append"><button type="button" class="btn btn-outline-secondary" id="btnClearSearch"><i class="fas fa-times"></i></button><button type="button" class="btn btn-primary" id="btnSearch"><i class="fas fa-search"></i></button></div></div></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0"><div class="card-header bg-white px-3 py-3 border-bottom"><h3 class="card-title font-weight-bold text-dark mb-0"><i class="fas fa-trash-alt text-danger mr-1"></i>Trash Bin</h3></div><div class="card-body p-0" id="content-wrapper" style="min-height:340px;">@include('backoffice.admin.section_media.partials.table', ['sectionMedia' => $sectionMedia, 'isTrash' => true])</div></div>
</div>
@endsection

@section('plugins.Sweetalert2', true)

@push('css')
<style>#content-wrapper.loading{opacity:.5;pointer-events:none;transition:opacity .3s ease-in-out}.section-media-table td{vertical-align:middle}.section-media-preview{width:62px;height:52px;object-fit:cover}.section-media-caption{max-width:260px}</style>
@endpush

@section('js')
@include('backoffice.admin.section_media.partials.script')
@endsection
