@extends('backoffice.admin.layouts.app')

@section('content')
<div id="page-manager"
     class="container-fluid py-3"
     data-mode="active"
     data-index-url="{{ route('admin.leads.index') }}"
     data-create-url="{{ route('admin.leads.create') }}"
     data-bulk-url="{{ route('admin.leads.multiple_action') }}"
     data-export-url="{{ route('admin.leads.export') }}"
     data-trash-url="{{ route('admin.leads.trashed') }}">

    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">
        @can('lead_create')
            <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="btnAddRecord">
                <i class="fas fa-plus mr-1"></i>Add Lead
            </button>
        @endcan

        @can('lead_trash')
            <a href="{{ route('admin.leads.trashed') }}" class="btn btn-outline-danger btn-sm font-weight-bold shadow-sm">
                <i class="fas fa-trash-alt mr-1"></i>Trash Bin
            </a>
        @endcan

        <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width:210px;">
            <option value="">-- Bulk Actions --</option>
            @can('lead_change_status')
                @foreach($statuses as $key => $label)
                    <option value="status_{{ $key }}">Status: {{ $label }}</option>
                @endforeach
            @endcan
            @can('lead_delete')
                <option value="delete">Move to Trash</option>
            @endcan
        </select>

        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">APPLY</button>

        @can('lead_export')
            <button type="button" class="btn btn-outline-success btn-sm font-weight-bold shadow-sm ml-auto" id="btnExport">
                <i class="fas fa-file-csv mr-1"></i>Export CSV
            </button>
        @endcan
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-3 py-2">
            <form id="filter-form">
                <div class="row align-items-end">
                    <div class="col-xl-2 col-md-3 mb-2">
                        <label class="text-muted small font-weight-bold text-uppercase mb-1">Status</label>
                        <select id="filter_status" class="form-control form-control-sm shadow-none">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-2 col-md-3 mb-2">
                        <label class="text-muted small font-weight-bold text-uppercase mb-1">Assignee</label>
                        <select id="filter_assigned_to" class="form-control form-control-sm shadow-none">
                            <option value="">All Assignees</option>
                            @foreach($assignees as $admin)
                                <option value="{{ $admin->id }}">{{ $admin->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-2 col-md-3 mb-2">
                        <label class="text-muted small font-weight-bold text-uppercase mb-1">Location</label>
                        <select id="filter_location_id" class="form-control form-control-sm shadow-none">
                            <option value="">All Locations</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}">{{ $location->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-4 col-md-6 mb-2">
                        <label class="text-muted small font-weight-bold text-uppercase mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="table_search" class="form-control shadow-none" autocomplete="off" placeholder="Reference, name, email or phone...">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-2 col-md-3 mb-2">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm" id="btnResetFilter">
                            <i class="fas fa-sync-alt mr-1"></i>Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @can('lead_list')
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white px-3 py-3 border-bottom">
                <h3 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-user-tag text-primary mr-1"></i>Leads List
                </h3>
            </div>
            <div class="card-body p-0" id="content-wrapper" style="min-height:340px;">
                @include('backoffice.admin.leads.partials.table', ['leads' => $leads, 'isTrash' => false])
            </div>
        </div>
    @else
        <div class="alert alert-warning border-0 shadow-sm font-weight-bold">
            <i class="fas fa-exclamation-triangle mr-2"></i>You do not have permission to view leads.
        </div>
    @endcan
</div>

<div class="modal fade" id="ajaxModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title font-weight-bold text-primary" id="modal-title">Lead Management</h5>
                <button type="button" class="close px-4 shadow-none" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-4" id="modal-body"></div>
        </div>
    </div>
</div>
@endsection

@section('plugins.Sweetalert2', true)
@section('plugins.Select2', true)

@push('css')
<style>
    #content-wrapper.loading { opacity:.5; pointer-events:none; transition:opacity .3s ease-in-out; }
    .lead-attachment-thumb { width:110px; height:80px; object-fit:cover; border-radius:6px; }
    .lead-json { font-family:Consolas, Monaco, monospace; font-size:12px; }
</style>
@endpush

@section('js')
@include('backoffice.admin.leads.partials.script')
@endsection
