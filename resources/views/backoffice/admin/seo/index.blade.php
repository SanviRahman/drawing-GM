@extends('backoffice.admin.layouts.app')

@section('title', $title)

@section('content')
<div id="seo-manager"
     class="container-fluid py-3"
     data-index-url="{{ route('admin.seo.index') }}"
     data-create-url="{{ route('admin.seo.create') }}"
     data-bulk-url="{{ route('admin.seo.multiple_action') }}"
     data-targets-url="{{ route('admin.seo.targets') }}">

    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">
        @can('seo_meta_create')
            <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="btnAddRecord">
                <i class="fas fa-plus mr-1"></i>Add SEO Metadata
            </button>
        @endcan
        @can('seo_meta_trash')
            <a href="{{ route('admin.seo.trashed') }}" class="btn btn-outline-danger btn-sm font-weight-bold shadow-sm">
                <i class="fas fa-trash-alt mr-1"></i>Trash Bin
            </a>
        @endcan
        <select id="bulk_action" class="form-control form-control-sm" style="width:190px;">
            <option value="">-- Bulk Actions --</option>
            @can('seo_meta_delete')<option value="delete">Move to Trash</option>@endcan
        </select>
        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3" id="btnApplyBulk">APPLY</button>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-3 py-2">
            <form id="filterForm" method="GET" action="{{ route('admin.seo.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label class="text-muted small font-weight-bold text-uppercase">Content Type</label>
                        <select name="type" id="filter_type" class="form-control form-control-sm">
                            <option value="">All Types</option>
                            @foreach($typeLabels as $key => $label)
                                <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label class="text-muted small font-weight-bold text-uppercase">Sitemap</label>
                        <select name="sitemap" id="filter_sitemap" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="yes" {{ request('sitemap') === 'yes' ? 'selected' : '' }}>Included</option>
                            <option value="no" {{ request('sitemap') === 'no' ? 'selected' : '' }}>Excluded</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-2 mb-md-0">
                        <label class="text-muted small font-weight-bold text-uppercase">Search</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" id="table_search" class="form-control" value="{{ request('search') }}" placeholder="Meta title, canonical URL or OG title...">
                            <div class="input-group-append"><button class="btn btn-primary"><i class="fas fa-search"></i></button></div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-block" id="btnResetFilter"><i class="fas fa-sync-alt"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h3 class="card-title font-weight-bold"><i class="fas fa-search text-primary mr-1"></i>SEO Metadata List</h3>
        </div>
        <div class="card-body p-0" id="content-wrapper" style="min-height:340px;">
            @include('backoffice.admin.seo.partials.table', ['seoMetas' => $seoMetas, 'isTrash' => false])
        </div>
    </div>
</div>

<div class="modal fade" id="ajaxModal" data-backdrop="static" data-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title font-weight-bold text-primary" id="modal-title">SEO Metadata</h5>
                <button type="button" class="close px-4" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body p-4" id="modal-body"></div>
        </div>
    </div>
</div>

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
#ajaxModal .select2-container--default .select2-selection--single .select2-selection__rendered{width:100%!important;line-height:normal!important;text-align:left!important;padding-left:30px!important;padding-right:30px!important}
#ajaxModal .select2-container--default .select2-selection--single .select2-selection__arrow{height:36px!important;top:1px!important}
</style>
@endpush
