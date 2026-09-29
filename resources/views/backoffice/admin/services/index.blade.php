@extends('backoffice.admin.layouts.app')

@section('content')
<div id="service-manager" class="container-fluid py-3" data-mode="active" data-index-url="{{ route('admin.services.index') }}" data-create-url="{{ route('admin.services.create') }}" data-bulk-url="{{ route('admin.services.multiple_action') }}" data-trash-url="{{ route('admin.services.trashed') }}">
    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">
        @if(auth('admin')->user()?->can('service_create'))
            <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="btnAddRecord"><i class="fas fa-plus mr-1"></i>Add New Service</button>
        @endif
        @if(auth('admin')->user()?->can('service_trash'))
            <a href="{{ route('admin.services.trashed') }}" class="btn btn-outline-danger btn-sm font-weight-bold shadow-sm"><i class="fas fa-trash-alt mr-1"></i>Trash Bin</a>
        @endif
        <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width:190px;">
            <option value="">-- Bulk Actions --</option>
            @if(auth('admin')->user()?->can('service_publish'))
                <option value="publish">Publish</option>
            @endif
            @if(auth('admin')->user()?->can('service_unpublish'))
                <option value="unpublish">Move to Draft</option>
            @endif
            @if(auth('admin')->user()?->can('service_update'))
                <option value="archive">Archive</option>
            @endif
            @if(auth('admin')->user()?->can('service_delete'))
                <option value="delete">Move to Trash</option>
            @endif
        </select>
        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">APPLY</button>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-3 py-2">
            <div class="row align-items-end">
                <div class="col-md-2 mb-2 mb-md-0"><label for="filter_status" class="text-muted small font-weight-bold text-uppercase mb-1">Status</label><select id="filter_status" class="form-control form-control-sm shadow-none"><option value="">All Statuses</option>@foreach(\App\Models\Service::STATUSES as $value => $label)<option value="{{ $value }}">{{ $label }}</option>@endforeach</select></div>
                <div class="col-md-2 mb-2 mb-md-0"><label for="filter_featured" class="text-muted small font-weight-bold text-uppercase mb-1">Featured</label><select id="filter_featured" class="form-control form-control-sm shadow-none"><option value="">All Services</option><option value="1">Featured Only</option><option value="0">Not Featured</option></select></div>
                <div class="col-md-6 mb-2 mb-md-0"><label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search Services</label><div class="input-group input-group-sm"><input type="text" id="table_search" class="form-control shadow-none" autocomplete="off" placeholder="Search name, slug or summary..."><div class="input-group-append"><button type="button" class="btn btn-outline-secondary" id="btnClearSearch" title="Clear"><i class="fas fa-times"></i></button><button type="button" class="btn btn-primary" id="btnSearch" title="Search"><i class="fas fa-search"></i></button></div></div></div>
                <div class="col-md-2"><button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm" id="btnResetFilter"><i class="fas fa-sync-alt mr-1"></i>Reset</button></div>
            </div>
        </div>
    </div>

    @if(auth('admin')->user()?->can('service_list'))
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white px-3 py-3 border-bottom"><h3 class="card-title font-weight-bold text-dark mb-0"><i class="fas fa-tools text-primary mr-1"></i>Services List</h3></div>
            <div class="card-body p-0" id="content-wrapper" style="min-height:340px;">@include('backoffice.admin.services.partials.table', ['services' => $services, 'isTrash' => false])</div>
        </div>
    @else
        <div class="alert alert-warning border-0 shadow-sm mt-4 font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i>You do not have permission to view services.</div>
    @endif
</div>

<div class="modal fade" id="ajaxModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document"><div class="modal-content border-0 shadow-lg"><div class="modal-header border-bottom-0 pb-0"><h5 class="modal-title font-weight-bold text-primary" id="modal-title">Services Management</h5><button type="button" class="close px-4 shadow-none" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body p-4" id="modal-body"></div></div></div></div>
@endsection

@section('plugins.Sweetalert2', true)

@push('css')
<style>#content-wrapper.loading{opacity:.5;pointer-events:none;transition:opacity .3s ease-in-out}.service-table td{vertical-align:middle}.service-title-cell{min-width:240px}.service-thumb{width:64px;height:54px;object-fit:cover}</style>
@endpush

@section('js')
@include('backoffice.admin.services.partials.script')
@endsection
