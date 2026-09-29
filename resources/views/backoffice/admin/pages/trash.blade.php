@extends('backoffice.admin.layouts.app')

@section('content')
<div id="page-manager" class="container-fluid py-3" data-mode="trash" data-index-url="{{ route('admin.pages.trashed') }}" data-create-url="" data-bulk-url="{{ route('admin.pages.multiple_action') }}" data-trash-url="{{ route('admin.pages.trashed') }}">
    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;"><a href="{{ route('admin.pages.index') }}" class="btn btn-outline-primary btn-sm font-weight-bold shadow-sm"><i class="fas fa-arrow-left mr-1"></i>Active Pages</a><select id="bulk_action" class="form-control form-control-sm shadow-none" style="width:190px;"><option value="">-- Bulk Actions --</option>@if(auth('admin')->user()?->can('page_restore'))<option value="restore">Restore</option>@endif @if(auth('admin')->user()?->can('page_force_delete'))<option value="force_delete">Force Delete</option>@endif</select><button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">APPLY</button></div>

    <div class="card shadow-sm border-0 mb-3"><div class="card-body px-3 py-2"><div class="row align-items-end"><div class="col-md-3 mb-2 mb-md-0"><label for="filter_status" class="text-muted small font-weight-bold text-uppercase mb-1">Status</label><select id="filter_status" class="form-control form-control-sm shadow-none"><option value="">All Statuses</option>@foreach(\App\Models\Page::STATUSES as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div><div class="col-md-3 mb-2 mb-md-0"><label for="filter_template" class="text-muted small font-weight-bold text-uppercase mb-1">Template</label><select id="filter_template" class="form-control form-control-sm shadow-none"><option value="">All Templates</option>@foreach($templates as $template)<option value="{{ $template }}">{{ $template }}</option>@endforeach</select></div><div class="col-md-4 mb-2 mb-md-0"><label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search Trash</label><div class="input-group input-group-sm"><input type="text" id="table_search" class="form-control shadow-none" autocomplete="off" placeholder="Search title, slug or excerpt..."><div class="input-group-append"><button type="button" class="btn btn-outline-secondary" id="btnClearSearch" title="Clear"><i class="fas fa-times"></i></button><button type="button" class="btn btn-primary" id="btnSearch" title="Search"><i class="fas fa-search"></i></button></div></div></div><div class="col-md-2"><button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm" id="btnResetFilter"><i class="fas fa-sync-alt mr-1"></i>Reset</button></div></div></div></div>

    <div class="card shadow-sm border-0"><div class="card-header bg-white px-3 py-3 border-bottom"><h3 class="card-title font-weight-bold text-dark mb-0"><i class="fas fa-trash-alt text-danger mr-1"></i>Trashed Pages</h3></div><div class="card-body p-0" id="content-wrapper" style="min-height:340px;">@include('backoffice.admin.pages.partials.table', ['pages' => $pages, 'isTrash' => true])</div></div>
</div>

<div class="modal fade" id="ajaxModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document"><div class="modal-content border-0 shadow-lg"><div class="modal-header border-bottom-0 pb-0"><h5 class="modal-title font-weight-bold text-primary" id="modal-title">Page Details</h5><button type="button" class="close px-4 shadow-none" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body p-4" id="modal-body"></div></div></div></div>
@endsection

@section('plugins.Sweetalert2', true)

@push('css')
<style>#content-wrapper.loading{opacity:.5;pointer-events:none;transition:opacity .3s ease-in-out}.page-table td{vertical-align:middle}.page-title-cell{min-width:240px}</style>
@endpush

@section('js')
@include('backoffice.admin.pages.partials.script')
@endsection
