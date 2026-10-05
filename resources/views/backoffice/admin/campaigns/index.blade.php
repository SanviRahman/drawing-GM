@extends('backoffice.admin.layouts.app')
@section('title', $title)
@section('content')
<div id="campaign-manager" class="container-fluid py-3"
 data-index-url="{{ route('admin.campaigns.index') }}"
 data-create-url="{{ route('admin.campaigns.create') }}"
 data-bulk-url="{{ route('admin.campaigns.multiple_action') }}">
 <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">
  @can('campaign_create')<button type="button" class="btn btn-primary btn-sm font-weight-bold" id="btnAddRecord"><i class="fas fa-plus mr-1"></i>Add Campaign</button>@endcan
  @can('campaign_trash')<a href="{{ route('admin.campaigns.trashed') }}" class="btn btn-outline-danger btn-sm font-weight-bold"><i class="fas fa-trash-alt mr-1"></i>Trash Bin</a>@endcan
  <select id="bulk_action" class="form-control form-control-sm" style="width:200px;"><option value="">-- Bulk Actions --</option>@can('campaign_publish')<option value="publish">Publish</option>@endcan @can('campaign_update')<option value="inactive">Mark Inactive</option><option value="archive">Archive</option>@endcan @can('campaign_delete')<option value="delete">Move to Trash</option>@endcan</select>
  <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3" id="btnApplyBulk">APPLY</button>
 </div>
 <div class="card shadow-sm border-0 mb-3"><div class="card-body px-3 py-2"><form id="filterForm" action="{{ route('admin.campaigns.index') }}" method="GET"><div class="row align-items-end"><div class="col-md-3"><label class="small text-muted font-weight-bold">STATUS</label><select name="status" id="filter_status" class="form-control form-control-sm"><option value="">All Statuses</option>@foreach($statuses as $key=>$label)<option value="{{ $key }}" {{ request('status')===$key?'selected':'' }}>{{ $label }}</option>@endforeach</select></div><div class="col-md-2"><label class="small text-muted font-weight-bold">DEFAULT</label><select name="default" id="filter_default" class="form-control form-control-sm"><option value="">All</option><option value="yes" {{ request('default')==='yes'?'selected':'' }}>Default Only</option></select></div><div class="col-md-6"><label class="small text-muted font-weight-bold">SEARCH</label><input type="search" name="search" id="table_search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Title, slug or custom route..."></div><div class="col-md-1"><button type="button" id="btnResetFilter" class="btn btn-outline-danger btn-sm btn-block"><i class="fas fa-sync-alt"></i></button></div></div></form></div></div>
 <div class="card shadow-sm border-0"><div class="card-header bg-white py-3"><h3 class="card-title font-weight-bold"><i class="fas fa-bullhorn text-primary mr-1"></i>Campaign List</h3></div><div class="card-body p-0" id="content-wrapper" style="min-height:340px;">@include('backoffice.admin.campaigns.partials.table',['campaigns'=>$campaigns,'defaultCampaignId'=>$defaultCampaignId,'isTrash'=>false])</div></div>
</div>
<div class="modal fade" id="ajaxModal" data-backdrop="static" data-keyboard="false" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content border-0 shadow-lg"><div class="modal-header border-bottom-0 pb-0"><h5 class="modal-title font-weight-bold text-primary" id="modal-title">Campaign</h5><button type="button" class="close px-4" data-dismiss="modal">&times;</button></div><div class="modal-body p-4" id="modal-body"></div></div></div></div>
@endsection
@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)
@push('css')<style>#content-wrapper.loading{opacity:.55;pointer-events:none}.campaign-thumb{width:72px;height:46px;object-fit:cover;border-radius:6px}#ajaxModal .select2-container{width:100%!important}</style>@endpush
@section('js') @include('backoffice.admin.campaigns.partials.script') @endsection
