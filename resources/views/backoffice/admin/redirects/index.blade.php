@extends('backoffice.admin.layouts.app')
@section('title',$title)
@section('content')
<div id="redirect-manager" class="container-fluid py-3"
     data-index-url="{{ route('admin.redirects.index') }}"
     data-create-url="{{ route('admin.redirects.create') }}"
     data-bulk-url="{{ route('admin.redirects.multiple_action') }}"
     data-import-url="{{ route('admin.redirects.import') }}">
    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px">
        @can('redirect_create')<button class="btn btn-primary btn-sm font-weight-bold" id="btnAddRecord"><i class="fas fa-plus mr-1"></i>Add Redirect</button>@endcan
        @can('redirect_trash')<a href="{{ route('admin.redirects.trashed') }}" class="btn btn-outline-danger btn-sm font-weight-bold"><i class="fas fa-trash-alt mr-1"></i>Trash Bin</a>@endcan
        @can('redirect_export')<a href="{{ route('admin.redirects.export') }}" class="btn btn-outline-success btn-sm font-weight-bold"><i class="fas fa-file-csv mr-1"></i>Export CSV</a>@endcan
        @can('redirect_import')
            <button class="btn btn-outline-info btn-sm font-weight-bold" id="btnImportCsv"><i class="fas fa-file-import mr-1"></i>Import CSV</button>
            <input type="file" id="redirectCsvFile" accept=".csv,text/csv" class="d-none">
        @endcan
        <select id="bulk_action" class="form-control form-control-sm" style="width:190px">
            <option value="">-- Bulk Actions --</option>
            @can('redirect_toggle')<option value="activate">Activate</option><option value="deactivate">Deactivate</option>@endcan
            @can('redirect_delete')<option value="delete">Move to Trash</option>@endcan
        </select>
        <button class="btn btn-secondary btn-sm font-weight-bold px-3" id="btnApplyBulk">APPLY</button>
    </div>

    <div class="card border-0 shadow-sm mb-3"><div class="card-body py-2 px-3">
        <form id="filterForm" method="GET" action="{{ route('admin.redirects.index') }}"><div class="row align-items-end">
            <div class="col-md-2"><label class="small font-weight-bold text-muted">STATUS CODE</label><select id="filter_status_code" name="status_code" class="form-control form-control-sm"><option value="">All Codes</option>@foreach($statusCodes as $code=>$label)<option value="{{ $code }}" {{ (string)request('status_code')===(string)$code?'selected':'' }}>{{ $code }}</option>@endforeach</select></div>
            <div class="col-md-2"><label class="small font-weight-bold text-muted">STATUS</label><select id="filter_status" name="status" class="form-control form-control-sm"><option value="">All</option><option value="active" {{ request('status')==='active'?'selected':'' }}>Active</option><option value="inactive" {{ request('status')==='inactive'?'selected':'' }}>Inactive</option></select></div>
            <div class="col-md-7"><label class="small font-weight-bold text-muted">SEARCH</label><input id="table_search" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Source or destination..."></div>
            <div class="col-md-1"><button type="button" id="btnResetFilter" class="btn btn-outline-danger btn-sm btn-block"><i class="fas fa-sync-alt"></i></button></div>
        </div></form>
    </div></div>

    <div class="card border-0 shadow-sm"><div class="card-header bg-white"><h3 class="card-title font-weight-bold"><i class="fas fa-random text-primary mr-1"></i>Redirects List</h3></div><div class="card-body p-0" id="content-wrapper">@include('backoffice.admin.redirects.partials.table',['redirects'=>$redirects,'isTrash'=>false])</div></div>
</div>

<div class="modal fade" id="ajaxModal" data-backdrop="static" data-keyboard="false" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-header border-0"><h5 class="modal-title font-weight-bold text-primary" id="modal-title">Redirect</h5><button class="close" data-dismiss="modal">&times;</button></div><div class="modal-body" id="modal-body"></div></div></div></div>
@include('backoffice.admin.redirects.partials.script')
@endsection
@section('plugins.Sweetalert2',true)
