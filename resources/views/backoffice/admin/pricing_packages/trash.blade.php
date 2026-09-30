@extends('backoffice.admin.layouts.app')

@section('title', $title)

@section('content')
<div id="pricing-package-manager" class="container-fluid py-3">
    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;"><a href="{{ route('admin.pricing_packages.index') }}" class="btn btn-secondary btn-sm font-weight-bold shadow-sm"><i class="fas fa-arrow-left mr-1"></i>Back to Pricing Packages</a><select id="bulkAction" class="form-control form-control-sm shadow-none" style="width:210px;"><option value="">-- Bulk Actions --</option>
        @can('pricing_package_restore')
            <option value="restore">Restore Selected</option>
        @endcan
        @can('pricing_package_force_delete')
            <option value="force_delete">Permanently Delete</option>
        @endcan
    </select><button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="applyBulk">APPLY</button></div>

    <div class="card shadow-sm border-0 mb-3"><div class="card-body px-3 py-2"><form id="filterForm" method="GET" action="{{ route('admin.pricing_packages.trashed') }}"><div class="row align-items-end">
        <div class="col-md-3 mb-2 mb-md-0"><label for="filter_service" class="text-muted small font-weight-bold text-uppercase mb-1">Service</label><select name="service_id" id="filter_service" class="form-control form-control-sm shadow-none"><option value="">All Services</option>
            @foreach($services as $service)
                <option value="{{ $service->id }}" {{ (string) request('service_id') === (string) $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
            @endforeach
        </select></div>
        <div class="col-md-3 mb-2 mb-md-0"><label for="filter_location" class="text-muted small font-weight-bold text-uppercase mb-1">Location</label><select name="location_id" id="filter_location" class="form-control form-control-sm shadow-none"><option value="">All Locations</option>
            @foreach($locations as $location)
                <option value="{{ $location->id }}" {{ (string) request('location_id') === (string) $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
            @endforeach
        </select></div>
        <div class="col-md-5 mb-2 mb-md-0"><label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search Trashed Packages</label><div class="input-group input-group-sm"><input type="text" name="search" id="table_search" class="form-control shadow-none" value="{{ request('search') }}" autocomplete="off" placeholder="Search packages..."><div class="input-group-append"><button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button></div></div></div>
        <div class="col-md-1"><button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm btn-reset-filter"><i class="fas fa-sync-alt"></i></button></div>
    </div></form></div></div>

    <div class="card shadow-sm border-0"><div class="card-header bg-white px-3 py-3 border-bottom text-danger"><h3 class="card-title font-weight-bold mb-0"><i class="fas fa-trash-alt mr-1"></i>Pricing Packages Trash</h3></div><div class="card-body p-0" id="tableContainer" style="min-height:340px;">@include('backoffice.admin.pricing_packages.partials.table', ['packages' => $packages, 'isTrash' => true])</div></div>
</div>

@include('backoffice.admin.pricing_packages.partials.script')
@endsection

@section('plugins.Sweetalert2', true)
