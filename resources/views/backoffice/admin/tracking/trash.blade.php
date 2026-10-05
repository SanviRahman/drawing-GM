@extends('backoffice.admin.layouts.app')
@section('title',$title)
@section('content')
<div id="tracking-manager" class="container-fluid py-3" data-index-url="{{ route('admin.tracking.trashed') }}" data-bulk-url="{{ route('admin.tracking.multiple_action') }}">
<div class="d-flex mb-3" style="gap:6px"><a href="{{ route('admin.tracking.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left mr-1"></i>Back</a><select id="bulk_action" class="form-control form-control-sm" style="width:210px"><option value="">-- Bulk Actions --</option>@can('tracking_provider_restore')<option value="restore">Restore Selected</option>@endcan @can('tracking_provider_force_delete')<option value="force_delete">Permanently Delete</option>@endcan</select><button id="btnApplyBulk" class="btn btn-secondary btn-sm">APPLY</button></div>
<div class="card border-0 shadow-sm mb-3"><div class="card-body py-2"><form id="filterForm" action="{{ route('admin.tracking.trashed') }}"><div class="row"><div class="col-md-10"><input id="table_search" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Search trashed providers..."></div><div class="col-md-2"><button type="button" id="btnResetFilter" class="btn btn-outline-danger btn-sm btn-block"><i class="fas fa-sync-alt"></i> Reset</button></div></div></form></div></div>
<div class="card border-0 shadow-sm"><div class="card-header bg-white"><h3 class="card-title font-weight-bold text-danger"><i class="fas fa-trash-alt mr-1"></i>Tracking Provider Trash</h3></div><div class="card-body p-0" id="content-wrapper">@include('backoffice.admin.tracking.partials.table',['trackingProviders'=>$trackingProviders,'isTrash'=>true])</div></div>
</div>
<div class="modal fade" id="ajaxModal" tabindex="-1"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header border-0"><h5 id="modal-title"></h5><button class="close" data-dismiss="modal">&times;</button></div><div class="modal-body" id="modal-body"></div></div></div></div>
@include('backoffice.admin.tracking.partials.script')
@endsection
@section('plugins.Sweetalert2',true)
