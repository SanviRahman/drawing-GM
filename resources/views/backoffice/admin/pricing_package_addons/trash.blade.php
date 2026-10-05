@extends('backoffice.admin.layouts.app')

@section('title', $title)

@section('content')
<div id="pricing-package-addon-manager"
     class="container-fluid py-3"
     data-index-url="{{ route('admin.pricing_package_addons.trashed') }}"
     data-create-url=""
     data-bulk-url="{{ route('admin.pricing_package_addons.multiple_action') }}">

    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">
        <a href="{{ route('admin.pricing_package_addons.index') }}" class="btn btn-secondary btn-sm font-weight-bold shadow-sm">
            <i class="fas fa-arrow-left mr-1"></i>Back to Package Add-ons
        </a>

        <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width:210px;">
            <option value="">-- Bulk Actions --</option>
            @can('pricing_package_addon_restore')
                <option value="restore">Restore Selected</option>
            @endcan
            @can('pricing_package_addon_force_delete')
                <option value="force_delete">Permanently Delete</option>
            @endcan
        </select>

        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">
            APPLY
        </button>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-3 py-2">
            <form id="filterForm" method="GET" action="{{ route('admin.pricing_package_addons.trashed') }}">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label for="filter_package" class="text-muted small font-weight-bold text-uppercase mb-1">Pricing Package</label>
                        <select name="pricing_package_id" id="filter_package" class="form-control form-control-sm shadow-none">
                            <option value="">All Packages</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" {{ (string) request('pricing_package_id') === (string) $package->id ? 'selected' : '' }}>
                                    {{ $package->name }}{{ $package->trashed() ? ' [Trashed]' : '' }}
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
                                    {{ $addon->name }}{{ $addon->trashed() ? ' [Trashed]' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-5 mb-2 mb-md-0">
                        <label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search Trashed Mappings</label>
                        <div class="input-group input-group-sm">
                            <input type="text"
                                   name="search"
                                   id="table_search"
                                   class="form-control shadow-none"
                                   value="{{ request('search') }}"
                                   autocomplete="off"
                                   placeholder="Package or add-on name...">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm" id="btnResetFilter">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white px-3 py-3 border-bottom text-danger">
            <h3 class="card-title font-weight-bold mb-0">
                <i class="fas fa-trash-alt mr-1"></i>Pricing Package Add-ons Trash
            </h3>
        </div>
        <div class="card-body p-0" id="content-wrapper" style="min-height:340px;">
            @include('backoffice.admin.pricing_package_addons.partials.table', [
                'mappings' => $mappings,
                'isTrash' => true,
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
