@extends('backoffice.admin.layouts.app')

@section('title', $title)

@section('content')
<div id="pricing-addon-manager" class="container-fluid py-3" data-index-url="{{ route('admin.pricing_addons.trashed') }}" data-create-url="" data-bulk-url="{{ route('admin.pricing_addons.multiple_action') }}">
    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;"><a href="{{ route('admin.pricing_addons.index') }}" class="btn btn-secondary btn-sm font-weight-bold shadow-sm"><i class="fas fa-arrow-left mr-1"></i>Back to Pricing Add-ons</a><select id="bulk_action" class="form-control form-control-sm shadow-none" style="width:210px;"><option value="">-- Bulk Actions --</option>
        @can('pricing_addon_restore')
            <option value="restore">Restore Selected</option>
        @endcan
        @can('pricing_addon_force_delete')
            <option value="force_delete">Permanently Delete</option>
        @endcan
    </select><button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">APPLY</button></div>

    <div class="card shadow-sm border-0 mb-3"><div class="card-body px-3 py-2"><form id="filterForm" method="GET" action="{{ route('admin.pricing_addons.trashed') }}"><div class="row align-items-end">
        <div class="col-md-3 mb-2 mb-md-0"><label for="filter_type" class="text-muted small font-weight-bold text-uppercase mb-1">Price Type</label><select name="price_type" id="filter_type" class="form-control form-control-sm shadow-none"><option value="">All Price Types</option><option value="fixed" {{ request('price_type') === 'fixed' ? 'selected' : '' }}>Fixed</option><option value="from" {{ request('price_type') === 'from' ? 'selected' : '' }}>From</option><option value="range" {{ request('price_type') === 'range' ? 'selected' : '' }}>Range</option><option value="call" {{ request('price_type') === 'call' ? 'selected' : '' }}>Call for Price</option></select></div>
        <div class="col-md-8 mb-2 mb-md-0"><label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search Trashed Add-ons</label><div class="input-group input-group-sm"><input type="text" name="search" id="table_search" class="form-control shadow-none" value="{{ request('search') }}" autocomplete="off" placeholder="Search add-ons..."><div class="input-group-append"><button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button></div></div></div>
        <div class="col-md-1"><button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm" id="btnResetFilter"><i class="fas fa-sync-alt"></i></button></div>
    </div></form></div></div>

    <div class="card shadow-sm border-0"><div class="card-header bg-white px-3 py-3 border-bottom text-danger"><h3 class="card-title font-weight-bold mb-0"><i class="fas fa-trash-alt mr-1"></i>Pricing Add-ons Trash</h3></div><div class="card-body p-0" id="content-wrapper" style="min-height:340px;">@include('backoffice.admin.pricing_addons.partials.table', ['addons' => $addons, 'isTrash' => true])</div></div>
</div>

@include('backoffice.admin.pricing_addons.partials.script')
@endsection

@section('plugins.Sweetalert2', true)

@push('css')
<style>.pricing-addon-table td{vertical-align:middle}.pricing-addon-thumb{width:54px;height:54px;object-fit:cover}.pricing-addon-empty-thumb{width:54px;height:54px;display:flex;align-items:center;justify-content:center}</style>
@endpush
