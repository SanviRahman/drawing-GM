@extends('backoffice.admin.layouts.app')

@section('title', $title)

@section('content')
<div id="pricing-item-manager" class="container-fluid py-3" data-mode="active" data-index-url="{{ route('admin.pricing_items.index') }}" data-create-url="{{ route('admin.pricing_items.create') }}" data-bulk-url="{{ route('admin.pricing_items.multiple_action') }}">
    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">
        @can('pricing_item_create')
            <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="btnAddRecord"><i class="fas fa-plus mr-1"></i>Add Pricing Item</button>
        @endcan

        @can('pricing_item_trash')
            <a href="{{ route('admin.pricing_items.trashed') }}" class="btn btn-outline-danger btn-sm font-weight-bold shadow-sm"><i class="fas fa-trash-alt mr-1"></i>Trash Bin</a>
        @endcan

        <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width:190px;">
            <option value="">-- Bulk Actions --</option>
            @can('pricing_item_toggle')
                <option value="activate">Activate</option>
                <option value="deactivate">Deactivate</option>
            @endcan
            @can('pricing_item_delete')
                <option value="delete">Move to Trash</option>
            @endcan
        </select>

        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">APPLY</button>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-3 py-2">
            <form id="filterForm" method="GET" action="{{ route('admin.pricing_items.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label for="filter_package" class="text-muted small font-weight-bold text-uppercase mb-1">Package</label>
                        <select name="pricing_package_id" id="filter_package" class="form-control form-control-sm shadow-none">
                            <option value="">All Packages</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" {{ (string) request('pricing_package_id') === (string) $package->id ? 'selected' : '' }}>{{ $package->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 mb-2 mb-md-0">
                        <label for="filter_type" class="text-muted small font-weight-bold text-uppercase mb-1">Price Type</label>
                        <select name="price_type" id="filter_type" class="form-control form-control-sm shadow-none">
                            <option value="">All Types</option>
                            <option value="fixed" {{ request('price_type') === 'fixed' ? 'selected' : '' }}>Fixed</option>
                            <option value="from" {{ request('price_type') === 'from' ? 'selected' : '' }}>From</option>
                            <option value="range" {{ request('price_type') === 'range' ? 'selected' : '' }}>Range</option>
                            <option value="call" {{ request('price_type') === 'call' ? 'selected' : '' }}>Call for Price</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2 mb-md-0">
                        <label for="filter_status" class="text-muted small font-weight-bold text-uppercase mb-1">Status</label>
                        <select name="status" id="filter_status" class="form-control form-control-sm shadow-none">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-3 mb-2 mb-md-0">
                        <label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search Items</label>
                        <div class="input-group input-group-sm"><input type="text" name="search" id="table_search" class="form-control shadow-none" value="{{ request('search') }}" autocomplete="off" placeholder="Search label, package, unit..."><div class="input-group-append"><button type="button" class="btn btn-outline-secondary" id="btnClearSearch" title="Clear"><i class="fas fa-times"></i></button><button class="btn btn-primary" type="submit" title="Search"><i class="fas fa-search"></i></button></div></div>
                    </div>

                    <div class="col-md-2"><button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm" id="btnResetFilter"><i class="fas fa-sync-alt mr-1"></i>Reset</button></div>
                </div>
            </form>
        </div>
    </div>

    @can('pricing_item_list')
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white px-3 py-3 border-bottom"><h3 class="card-title font-weight-bold text-dark mb-0"><i class="fas fa-list text-primary mr-1"></i>Pricing Items List</h3></div>
            <div class="card-body p-0" id="content-wrapper" style="min-height:340px;">@include('backoffice.admin.pricing_items.partials.table', ['items' => $items, 'isTrash' => false])</div>
        </div>
    @else
        <div class="alert alert-warning border-0 shadow-sm"><i class="fas fa-exclamation-triangle mr-2"></i>You do not have permission to view pricing items.</div>
    @endcan
</div>

<div class="modal fade" id="ajaxModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-centered" role="document"><div class="modal-content border-0 shadow-lg"><div class="modal-header py-2"><h5 class="modal-title font-weight-bold text-primary mb-0" id="modal-title">Pricing Item</h5><button type="button" class="close shadow-none" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body p-3" id="modal-body"></div></div></div></div>
@endsection

@section('plugins.Sweetalert2', true)

@push('css')
<style>#content-wrapper.loading{opacity:.55;pointer-events:none;transition:opacity .2s ease}.pricing-item-table td{vertical-align:middle}</style>
@endpush

@section('js')
@include('backoffice.admin.pricing_items.partials.script')
@endsection
