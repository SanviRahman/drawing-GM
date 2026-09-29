@extends('backoffice.admin.layouts.app')

@section('title', $title)

@section('content')
<div id="service-feature-manager" class="container-fluid py-3">
    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">
        <a href="{{ route('admin.service_features.index') }}" class="btn btn-outline-primary btn-sm font-weight-bold shadow-sm"><i class="fas fa-arrow-left mr-1"></i>Back to Features</a>

        <select id="bulkAction" class="form-control form-control-sm shadow-none" style="width:190px;">
            <option value="">-- Bulk Actions --</option>
            @if(auth('admin')->user()?->can('service_feature_restore'))
                <option value="restore">Restore</option>
            @endif
            @if(auth('admin')->user()?->can('service_feature_force_delete'))
                <option value="force_delete">Permanently Delete</option>
            @endif
        </select>

        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="applyBulk">APPLY</button>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-3 py-2">
            <form id="filterForm" method="GET" action="{{ route('admin.service_features.trashed') }}">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label for="filter_service" class="text-muted small font-weight-bold text-uppercase mb-1">Service</label>
                        <select name="service_id" id="filter_service" class="form-control form-control-sm shadow-none">
                            <option value="">All Services</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}" {{ (string) request('service_id') === (string) $service->id ? 'selected' : '' }}>{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-7 mb-2 mb-md-0">
                        <label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search Features</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" id="table_search" class="form-control shadow-none" value="{{ request('search') }}" autocomplete="off" placeholder="Search trashed features...">
                            <div class="input-group-append"><button class="btn btn-primary" type="submit" title="Search"><i class="fas fa-search"></i></button></div>
                        </div>
                    </div>

                    <div class="col-md-2"><button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm btn-reset-filter"><i class="fas fa-sync-alt mr-1"></i>Reset</button></div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white px-3 py-3 border-bottom"><h3 class="card-title font-weight-bold text-dark mb-0"><i class="fas fa-trash-alt text-danger mr-1"></i>Trashed Service Features</h3></div>
        <div class="card-body p-0" id="tableContainer" style="min-height:340px;">@include('backoffice.admin.service_features.partials.table', ['features' => $features, 'isTrash' => true])</div>
    </div>
</div>

@include('backoffice.admin.service_features.partials.script')
@endsection

@section('plugins.Sweetalert2', true)

@push('css')
<style>.service-feature-table td{vertical-align:middle}</style>
@endpush
