@extends('backoffice.admin.layouts.app')

@section('title', $title)

@section('content')
<div id="pricing-package-addon-manager"
     class="container-fluid py-3"
     data-index-url="{{ route('admin.pricing_package_addons.index') }}"
     data-create-url="{{ route('admin.pricing_package_addons.create') }}"
     data-bulk-url="{{ route('admin.pricing_package_addons.multiple_action') }}">

    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">
        @can('pricing_package_addon_create')
            <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="btnAddRecord">
                <i class="fas fa-plus mr-1"></i>Add Package Add-on
            </button>
        @endcan

        @can('pricing_package_addon_trash')
            <a href="{{ route('admin.pricing_package_addons.trashed') }}"
               class="btn btn-outline-danger btn-sm font-weight-bold shadow-sm">
                <i class="fas fa-trash-alt mr-1"></i>Trash Bin
            </a>
        @endcan

        <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width:190px;">
            <option value="">-- Bulk Actions --</option>
            @can('pricing_package_addon_delete')
                <option value="delete">Move to Trash</option>
            @endcan
        </select>

        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">
            APPLY
        </button>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-3 py-2">
            <form id="filterForm" method="GET" action="{{ route('admin.pricing_package_addons.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label for="filter_package" class="text-muted small font-weight-bold text-uppercase mb-1">Pricing Package</label>
                        <select name="pricing_package_id" id="filter_package" class="form-control form-control-sm shadow-none">
                            <option value="">All Packages</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" {{ (string) request('pricing_package_id') === (string) $package->id ? 'selected' : '' }}>
                                    {{ $package->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 mb-2 mb-md-0">
                        <label for="filter_addon" class="text-muted small font-weight-bold text-uppercase mb-1">Pricing Add-on</label>
                        <select name="pricing_addon_id" id="filter_addon" class="form-control form-control-sm shadow-none">
                            <option value="">All Add-ons</option>
                            @foreach($addons as $addon)
                                <option value="{{ $addon->id }}" {{ (string) request('pricing_addon_id') === (string) $addon->id ? 'selected' : '' }}>
                                    {{ $addon->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-5 mb-2 mb-md-0">
                        <label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <input type="text"
                                   name="search"
                                   id="table_search"
                                   class="form-control shadow-none"
                                   value="{{ request('search') }}"
                                   autocomplete="off"
                                   placeholder="Package name, add-on name or unit...">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit" title="Search">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm" id="btnResetFilter" title="Reset">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white px-3 py-3 border-bottom">
            <h3 class="card-title font-weight-bold text-dark mb-0">
                <i class="fas fa-link text-primary mr-1"></i>Pricing Package Add-ons List
            </h3>
        </div>
        <div class="card-body p-0" id="content-wrapper" style="min-height:340px;">
            @include('backoffice.admin.pricing_package_addons.partials.table', [
                'mappings' => $mappings,
                'isTrash' => false,
            ])
        </div>
    </div>
</div>

@include('backoffice.admin.pricing_package_addons.partials.script')
@endsection

@section('plugins.Sweetalert2', true)

@push('css')
<style>
    .pricing-package-addon-table td { vertical-align: middle; }
    .pricing-package-addon-thumb { width: 54px; height: 54px; object-fit: cover; }
    .pricing-package-addon-empty-thumb { width: 54px; height: 54px; display: flex; align-items: center; justify-content: center; }
    .override-preview { max-width: 280px; white-space: normal; word-break: break-word; }
</style>
@endpush
