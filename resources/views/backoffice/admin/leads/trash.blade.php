@extends('backoffice.admin.layouts.app')

@section('content')
<div id="page-manager"
     class="container-fluid py-3"
     data-mode="trash"
     data-index-url="{{ route('admin.leads.trashed') }}"
     data-bulk-url="{{ route('admin.leads.multiple_action') }}">

    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">
        @can('lead_list')
            <a href="{{ route('admin.leads.index') }}" class="btn btn-secondary btn-sm font-weight-bold shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i>Back to Leads
            </a>
        @endcan

        <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width:210px;">
            <option value="">-- Bulk Actions --</option>
            @can('lead_restore')
                <option value="restore">Restore Selected</option>
            @endcan
            @can('lead_force_delete')
                <option value="force_delete">Permanently Delete</option>
            @endcan
        </select>

        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">APPLY</button>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-3 py-2">
            <form id="filter-form">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label class="text-muted small font-weight-bold text-uppercase mb-1">Status</label>
                        <select id="filter_status" class="form-control form-control-sm">
                            <option value="">All Statuses</option>
                            @foreach($statuses as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-7 mb-2">
                        <label class="text-muted small font-weight-bold text-uppercase mb-1">Search Trashed Leads</label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="table_search" class="form-control" placeholder="Reference, name, email or phone...">
                            <div class="input-group-append"><button type="submit" class="btn btn-primary"><i class="fas fa-search"></i></button></div>
                        </div>
                    </div>

                    <div class="col-md-2 mb-2">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold" id="btnResetFilter"><i class="fas fa-sync-alt mr-1"></i>Reset</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @can('lead_trash')
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white px-3 py-3 border-bottom text-danger">
                <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-trash-alt mr-2"></i>Lead Trash</h3>
            </div>
            <div class="card-body p-0" id="content-wrapper" style="min-height:340px;">
                @include('backoffice.admin.leads.partials.table', ['leads' => $leads, 'isTrash' => true])
            </div>
        </div>
    @endcan
</div>
@endsection

@section('plugins.Sweetalert2', true)
@section('plugins.Select2', true)

@section('js')
@include('backoffice.admin.leads.partials.script')
@endsection
