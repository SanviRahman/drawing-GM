@extends('backoffice.admin.layouts.app')

@section('content')
<div id="page-manager"
     class="container-fluid py-3"
     data-mode="active"
     data-index-url="{{ route('admin.lead_form_fields.index') }}"
     data-create-url="{{ route('admin.lead_form_fields.create') }}"
     data-bulk-url="{{ route('admin.lead_form_fields.multiple_action') }}"
     data-reorder-url="{{ route('admin.lead_form_fields.reorder') }}"
     data-trash-url="{{ route('admin.lead_form_fields.trashed') }}">

    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">
        @can('lead_form_field_create')
            <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="btnAddRecord">
                <i class="fas fa-plus mr-1"></i>Add Booking Field
            </button>
        @endcan

        @can('lead_form_field_trash')
            <a href="{{ route('admin.lead_form_fields.trashed') }}" class="btn btn-outline-danger btn-sm font-weight-bold shadow-sm">
                <i class="fas fa-trash-alt mr-1"></i>Trash Bin
            </a>
        @endcan

        <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width:190px;">
            <option value="">-- Bulk Actions --</option>
            @can('lead_form_field_toggle')
                <option value="active">Mark as Active</option>
                <option value="inactive">Mark as Inactive</option>
            @endcan
            @can('lead_form_field_delete')
                <option value="delete">Move to Trash</option>
            @endcan
        </select>

        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">APPLY</button>

        @can('lead_form_field_reorder')
            <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold shadow-sm ml-auto" id="btnSaveOrder">
                <i class="fas fa-sort-numeric-down mr-1"></i>Save Order
            </button>
        @endcan
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-3 py-2">
            <form id="filter-form">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="text-muted small font-weight-bold text-uppercase mb-1">Status</label>
                        <select id="filter_status" class="form-control form-control-sm shadow-none">
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-7 mb-2 mb-md-0">
                        <label class="text-muted small font-weight-bold text-uppercase mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="table_search" class="form-control shadow-none" placeholder="Label, key or placeholder..." autocomplete="off">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm" id="btnResetFilter">
                            <i class="fas fa-sync-alt mr-1"></i>Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @can('lead_form_field_list')
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white px-3 py-3 border-bottom">
                <h3 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-list-alt text-primary mr-1"></i>Booking Form Fields
                </h3>
            </div>
            <div class="card-body p-0" id="content-wrapper" style="min-height:340px;">
                @include('backoffice.admin.lead_form_fields.partials.table', ['leadFormFields' => $leadFormFields, 'isTrash' => false])
            </div>
        </div>
    @else
        <div class="alert alert-warning border-0 shadow-sm font-weight-bold">
            <i class="fas fa-exclamation-triangle mr-2"></i>You do not have permission to view booking fields.
        </div>
    @endcan
</div>

<div class="modal fade" id="ajaxModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title font-weight-bold text-primary" id="modal-title">Booking Form Field</h5>
                <button type="button" class="close px-4 shadow-none" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4" id="modal-body"></div>
        </div>
    </div>
</div>
@endsection

@section('plugins.Sweetalert2', true)

@push('css')
<style>
    #content-wrapper.loading { opacity:.5; pointer-events:none; transition:opacity .3s ease-in-out; }
</style>
@endpush

@section('js')
@include('backoffice.admin.lead_form_fields.partials.script')
@endsection
