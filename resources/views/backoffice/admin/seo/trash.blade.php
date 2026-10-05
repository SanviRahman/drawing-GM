@extends('backoffice.admin.layouts.app')
@section('title', $title)
@section('content')
<div id="seo-manager"
     class="container-fluid py-3"
     data-index-url="{{ route('admin.seo.trashed') }}"
     data-bulk-url="{{ route('admin.seo.multiple_action') }}"
     data-targets-url="{{ route('admin.seo.targets') }}">
    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">
        <a href="{{ route('admin.seo.index') }}" class="btn btn-secondary btn-sm font-weight-bold"><i class="fas fa-arrow-left mr-1"></i>Back to SEO</a>
        <select id="bulk_action" class="form-control form-control-sm" style="width:210px;">
            <option value="">-- Bulk Actions --</option>
            @can('seo_meta_restore')<option value="restore">Restore Selected</option>@endcan
            @can('seo_meta_force_delete')<option value="force_delete">Permanently Delete</option>@endcan
        </select>
        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3" id="btnApplyBulk">APPLY</button>
    </div>
    <div class="card shadow-sm border-0 mb-3"><div class="card-body px-3 py-2">
        <form id="filterForm" method="GET" action="{{ route('admin.seo.trashed') }}">
            <div class="row align-items-end">
                <div class="col-md-3"><label class="small text-muted font-weight-bold">CONTENT TYPE</label><select name="type" id="filter_type" class="form-control form-control-sm"><option value="">All Types</option>@foreach($typeLabels as $key=>$label)<option value="{{ $key }}" {{ request('type')===$key?'selected':'' }}>{{ $label }}</option>@endforeach</select></div>
                <div class="col-md-8"><label class="small text-muted font-weight-bold">SEARCH</label><input type="text" name="search" id="table_search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Meta title or canonical URL..."></div>
                <div class="col-md-1"><button type="button" id="btnResetFilter" class="btn btn-outline-danger btn-sm btn-block"><i class="fas fa-sync-alt"></i></button></div>
            </div>
        </form>
    </div></div>
    <div class="card shadow-sm border-0"><div class="card-header bg-white"><h3 class="card-title font-weight-bold text-danger"><i class="fas fa-trash-alt mr-1"></i>SEO Trash</h3></div><div class="card-body p-0" id="content-wrapper">@include('backoffice.admin.seo.partials.table',['seoMetas'=>$seoMetas,'isTrash'=>true])</div></div>
</div>
<div class="modal fade" id="ajaxModal" tabindex="-1"><div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content"><div class="modal-header border-0"><h5 id="modal-title" class="modal-title font-weight-bold text-primary">SEO Metadata</h5><button class="close" data-dismiss="modal">&times;</button></div><div class="modal-body" id="modal-body"></div></div></div></div>
@include('backoffice.admin.seo.partials.script')
@endsection
@section('plugins.Select2', true)
@section('plugins.Sweetalert2', true)

@push('css')
<style>
#content-wrapper.loading{opacity:.55;pointer-events:none}
.seo-social-thumb{width:64px!important;height:44px!important;max-width:64px!important;object-fit:cover;border-radius:6px;display:block}
#ajaxModal .select2-container{width:100%!important}
#ajaxModal .select2-container--default .select2-selection--single{height:38px!important;min-height:38px!important;display:flex!important;align-items:center!important}
#ajaxModal .select2-container--default .select2-selection--single .select2-selection__rendered{width:100%!important;line-height:normal!important;text-align:center!important;padding-left:30px!important;padding-right:30px!important}
#ajaxModal .select2-container--default .select2-selection--single .select2-selection__arrow{height:36px!important;top:1px!important}
</style>
@endpush
