@extends('backoffice.admin.layouts.app')

@section('content')
<div id="page-manager" class="container-fluid py-3" data-mode="active" data-index-url="{{ route('admin.galleries.index') }}" data-create-url="{{ route('admin.galleries.create') }}" data-bulk-url="{{ route('admin.galleries.multiple_action') }}" data-trash-url="{{ route('admin.galleries.trashed') }}">

    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">
        @can('gallery_create')
        <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="btnAddRecord"><i class="fas fa-plus mr-1"></i>Add New Gallery</button>
        @endcan

        @can('gallery_trash')
        <a href="{{ route('admin.galleries.trashed') }}" class="btn btn-outline-danger btn-sm font-weight-bold shadow-sm"><i class="fas fa-trash-alt mr-1"></i>Trash Bin</a>
        @endcan

        <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width:190px;">
            <option value="">-- Bulk Actions --</option>
            <option value="active">Mark as Active</option>
            <option value="inactive">Mark as Inactive</option>
            @can('gallery_delete')
            <option value="delete">Move to Trash</option>
            @endcan
        </select>

        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">APPLY</button>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-3 py-2">
            <div class="row align-items-end">
                <div class="col-md-8 mb-2 mb-md-0">
                    <label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search Galleries</label>
                    <div class="input-group input-group-sm">
                        <input type="text" id="table_search" class="form-control shadow-none" autocomplete="off" placeholder="Search gallery name...">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" id="btnClearSearch"><i class="fas fa-times"></i></button>
                            <button type="button" class="btn btn-primary" id="btnSearch"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </div>

                <div class="col-md-2 mb-2 mb-md-0">
                    <label class="text-muted small font-weight-bold text-uppercase mb-1">Status</label>
                    <select id="filter_status" class="form-control form-control-sm shadow-none">
                        <option value="">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm" id="btnResetFilter"><i class="fas fa-sync-alt mr-1"></i>Reset</button>
                </div>
            </div>
        </div>
    </div>

    @can('gallery_list')
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white px-3 py-3 border-bottom">
            <h3 class="card-title font-weight-bold text-dark mb-0"><i class="fas fa-images text-primary mr-1"></i>Gallery List</h3>
        </div>

        <div class="card-body p-0" id="content-wrapper" style="min-height:340px;">
            @include('backoffice.admin.galleries.partials.table',['galleries'=>$galleries,'isTrash'=>false])
        </div>
    </div>
    @else
    <div class="alert alert-warning border-0 shadow-sm mt-4 font-weight-bold"><i class="fas fa-exclamation-triangle mr-2"></i>You do not have permission to view galleries.</div>
    @endcan

</div>

<div class="modal fade" id="ajaxModal" data-backdrop="static" data-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title font-weight-bold text-primary" id="modal-title">Gallery Management</h5>
                <button type="button" class="close px-4 shadow-none" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4" id="modal-body"></div>
        </div>
    </div>
</div>
@endsection

@section('plugins.Sweetalert2',true)
@section('plugins.Select2',true)

@push('css')
<style>#content-wrapper.loading{opacity:.5;pointer-events:none;transition:opacity .3s ease-in-out;}</style>
@endpush

@section('js')
@include('backoffice.admin.galleries.partials.script')
@endsection