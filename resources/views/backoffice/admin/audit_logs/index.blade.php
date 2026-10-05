@extends('backoffice.admin.layouts.app')
@section('title', $title)
@section('content')
<div id="audit-manager" class="container-fluid py-3"
     data-index-url="{{ route('admin.audit_logs.index') }}"
     data-bulk-url="{{ route('admin.audit_logs.multiple_action') }}"
     data-export-url="{{ route('admin.audit_logs.export') }}">
    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">
        @can('audit_log_trash')
            <a href="{{ route('admin.audit_logs.trashed') }}" class="btn btn-outline-danger btn-sm font-weight-bold"><i class="fas fa-trash-alt mr-1"></i>Trash Bin</a>
        @endcan
        @can('audit_log_export')
            <button type="button" class="btn btn-outline-success btn-sm font-weight-bold" id="btnExport"><i class="fas fa-file-csv mr-1"></i>Export CSV</button>
        @endcan
        @can('audit_log_delete')
            <select id="bulk_action" class="form-control form-control-sm" style="width:190px;"><option value="">-- Bulk Actions --</option><option value="delete">Move to Trash</option></select>
            <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3" id="btnApplyBulk">APPLY</button>
        @endcan
    </div>

    <div class="card shadow-sm border-0 mb-3"><div class="card-body px-3 py-2">
        <form id="filterForm" action="{{ route('admin.audit_logs.index') }}" method="GET">
            <div class="row align-items-end">
                <div class="col-md-3"><label class="small text-muted font-weight-bold">ACTION</label><select name="action" id="filter_action" class="form-control form-control-sm"><option value="">All Actions</option>@foreach($actions as $action)<option value="{{ $action }}" {{ request('action')===$action?'selected':'' }}>{{ $action }}</option>@endforeach</select></div>
                <div class="col-md-2"><label class="small text-muted font-weight-bold">FROM</label><input type="date" name="date_from" id="filter_date_from" class="form-control form-control-sm" value="{{ request('date_from') }}"></div>
                <div class="col-md-2"><label class="small text-muted font-weight-bold">TO</label><input type="date" name="date_to" id="filter_date_to" class="form-control form-control-sm" value="{{ request('date_to') }}"></div>
                <div class="col-md-4"><label class="small text-muted font-weight-bold">SEARCH</label><input type="search" name="search" id="table_search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Action, actor, model, IP..."></div>
                <div class="col-md-1"><button type="button" id="btnResetFilter" class="btn btn-outline-danger btn-sm btn-block"><i class="fas fa-sync-alt"></i></button></div>
            </div>
        </form>
    </div></div>

    <div class="card shadow-sm border-0"><div class="card-header bg-white py-3"><h3 class="card-title font-weight-bold"><i class="fas fa-history text-primary mr-1"></i>Audit Log List</h3></div><div class="card-body p-0" id="content-wrapper" style="min-height:340px;">@include('backoffice.admin.audit_logs.partials.table',['auditLogs'=>$auditLogs,'isTrash'=>false])</div></div>
</div>
<div class="modal fade" id="ajaxModal" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content"><div class="modal-header border-0"><h5 class="modal-title font-weight-bold text-primary" id="modal-title">Audit Log</h5><button class="close" data-dismiss="modal">&times;</button></div><div class="modal-body" id="modal-body"></div></div></div></div>
@endsection
@section('plugins.Sweetalert2', true)
@section('js') @include('backoffice.admin.audit_logs.partials.script') @endsection
