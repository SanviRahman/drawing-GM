@extends('backoffice.admin.layouts.app')
@section('title',$title)
@section('content')
<div id="tracking-manager" class="container-fluid py-3" data-index-url="{{ route('admin.tracking.index') }}" data-create-url="{{ route('admin.tracking.create') }}" data-bulk-url="{{ route('admin.tracking.multiple_action') }}">
<div class="d-flex flex-wrap mb-3" style="gap:6px">
@can('tracking_provider_create')<button id="btnAddRecord" class="btn btn-primary btn-sm font-weight-bold"><i class="fas fa-plus mr-1"></i>Add Provider</button>@endcan
@can('tracking_provider_trash')<a href="{{ route('admin.tracking.trashed') }}" class="btn btn-outline-danger btn-sm font-weight-bold"><i class="fas fa-trash-alt mr-1"></i>Trash Bin</a>@endcan
<select id="bulk_action" class="form-control form-control-sm" style="width:190px"><option value="">-- Bulk Actions --</option>@can('tracking_provider_toggle')<option value="enable">Enable</option><option value="disable">Disable</option>@endcan @can('tracking_provider_delete')<option value="delete">Move to Trash</option>@endcan</select><button id="btnApplyBulk" class="btn btn-secondary btn-sm font-weight-bold px-3">APPLY</button>
</div>
<div class="card border-0 shadow-sm mb-3"><div class="card-body py-2"><form id="filterForm" action="{{ route('admin.tracking.index') }}"><div class="row align-items-end">
<div class="col-md-3"><label class="small text-muted font-weight-bold">PROVIDER</label><select id="filter_provider" name="provider" class="form-control form-control-sm"><option value="">All Providers</option>@foreach($providers as $key=>$label)<option value="{{ $key }}" {{ request('provider')===$key?'selected':'' }}>{{ $label }}</option>@endforeach</select></div>
<div class="col-md-2"><label class="small text-muted font-weight-bold">STATUS</label><select id="filter_status" name="status" class="form-control form-control-sm"><option value="">All</option><option value="enabled" {{ request('status')==='enabled'?'selected':'' }}>Enabled</option><option value="disabled" {{ request('status')==='disabled'?'selected':'' }}>Disabled</option></select></div>
<div class="col-md-2"><label class="small text-muted font-weight-bold">MODE</label><select id="filter_mode" name="mode" class="form-control form-control-sm"><option value="">All</option><option value="test" {{ request('mode')==='test'?'selected':'' }}>Test</option><option value="live" {{ request('mode')==='live'?'selected':'' }}>Live</option></select></div>
<div class="col-md-4"><label class="small text-muted font-weight-bold">SEARCH</label><input id="table_search" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Provider or public ID..."></div>
<div class="col-md-1"><button type="button" id="btnResetFilter" class="btn btn-outline-danger btn-sm btn-block"><i class="fas fa-sync-alt"></i></button></div>
</div></form></div></div>
<div class="card border-0 shadow-sm"><div class="card-header bg-white"><h3 class="card-title font-weight-bold"><i class="fas fa-chart-line text-primary mr-1"></i>Tracking Providers</h3></div><div class="card-body p-0" id="content-wrapper">@include('backoffice.admin.tracking.partials.table',['trackingProviders'=>$trackingProviders,'isTrash'=>false])</div></div>
</div>
<div class="modal fade" id="ajaxModal" data-backdrop="static" data-keyboard="false" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content"><div class="modal-header border-0"><h5 id="modal-title" class="modal-title font-weight-bold text-primary"></h5><button class="close" data-dismiss="modal">&times;</button></div><div class="modal-body" id="modal-body"></div></div></div></div>
@include('backoffice.admin.tracking.partials.script')
@endsection
@section('plugins.Sweetalert2',true)
