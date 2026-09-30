@extends('backoffice.admin.layouts.app')

@section('title', $title)

@section('content')
<div id="location-manager" class="container-fluid py-3" data-mode="trash" data-index-url="{{ route('admin.locations.trashed') }}" data-bulk-url="{{ route('admin.locations.multiple_action') }}">
    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">
        <a href="{{ route('admin.locations.index') }}" class="btn btn-outline-primary btn-sm font-weight-bold shadow-sm"><i class="fas fa-arrow-left mr-1"></i>Back to Locations</a>
        <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width:190px;">
            <option value="">-- Bulk Actions --</option>
            @if(auth('admin')->user()?->can('location_restore'))
                <option value="restore">Restore</option>
            @endif
            @if(auth('admin')->user()?->can('location_force_delete'))
                <option value="force_delete">Permanently Delete</option>
            @endif
        </select>
        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">APPLY</button>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-3 py-2">
            <div class="row align-items-end">
                <div class="col-md-2 mb-2 mb-md-0">
                    <label for="filter_status" class="text-muted small font-weight-bold text-uppercase mb-1">Status</label>
                    <select id="filter_status" class="form-control form-control-sm shadow-none">
                        <option value="">All Statuses</option>
                        @foreach(\App\Models\Location::STATUSES as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 mb-2 mb-md-0">
                    <label for="filter_region" class="text-muted small font-weight-bold text-uppercase mb-1">Region</label>
                    <select id="filter_region" class="form-control form-control-sm shadow-none">
                        <option value="">All Regions</option>
                        @foreach($regions as $region)
                            <option value="{{ $region }}">{{ $region }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-5 mb-2 mb-md-0">
                    <label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search Trashed Locations</label>
                    <div class="input-group input-group-sm"><input type="text" id="table_search" class="form-control shadow-none" autocomplete="off" placeholder="Search name, slug, region or summary..."><div class="input-group-append"><button type="button" class="btn btn-outline-secondary" id="btnClearSearch" title="Clear"><i class="fas fa-times"></i></button><button type="button" class="btn btn-primary" id="btnSearch" title="Search"><i class="fas fa-search"></i></button></div></div>
                </div>

                <div class="col-md-2"><button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm" id="btnResetFilter"><i class="fas fa-sync-alt mr-1"></i>Reset</button></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white px-3 py-3 border-bottom"><h3 class="card-title font-weight-bold text-dark mb-0"><i class="fas fa-trash-alt text-danger mr-1"></i>Trashed Locations</h3></div>
        <div class="card-body p-0" id="content-wrapper" style="min-height:340px;">@include('backoffice.admin.locations.partials.table', ['locations' => $locations, 'isTrash' => true])</div>
    </div>
</div>
@endsection

@section('plugins.Sweetalert2', true)

@push('css')
<style>#content-wrapper.loading{opacity:.5;pointer-events:none;transition:opacity .3s ease-in-out}.location-table td{vertical-align:middle}.location-title-cell{min-width:240px}.location-thumb{width:64px;height:54px;object-fit:cover}</style>
@endpush

@section('js')
@include('backoffice.admin.locations.partials.script')
@endsection
