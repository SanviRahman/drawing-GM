@extends('backoffice.admin.layouts.app')

@section('content')
    <div id="page-manager"
         class="container-fluid py-3"
         data-mode="trash"
         data-index-url="{{ route('admin.contact_targets.trashed') }}"
         data-bulk-url="{{ route('admin.contact_targets.multiple_action') }}">

        <div class="d-flex align-items-center flex-wrap mb-3" style="gap: 6px;">
            @can('contact_target_list')
                <a href="{{ route('admin.contact_targets.index') }}" class="btn btn-secondary btn-sm font-weight-bold shadow-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Back to Contact Targets
                </a>
            @endcan

            <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width: 210px;">
                <option value="">-- Bulk Actions --</option>
                @can('contact_target_restore')
                    <option value="restore">Restore Selected</option>
                @endcan
                @can('contact_target_force_delete')
                    <option value="force_delete">Permanently Delete</option>
                @endcan
            </select>

            <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">
                APPLY
            </button>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body px-3 py-2">
                <form id="filterForm">
                    <div class="row align-items-end">
                        <div class="col-lg-3 col-md-4 mb-2 mb-md-0">
                            <label for="filter_channel" class="text-muted small font-weight-bold text-uppercase mb-1">Contact Channel</label>
                            <select id="filter_channel" class="form-control form-control-sm shadow-none">
                                <option value="">All Channels</option>
                                @foreach($channelOptions as $channel)
                                    <option value="{{ $channel->id }}">
                                        {{ $channel->label }}{{ $channel->trashed() ? ' (Trashed)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-3 mb-2 mb-md-0">
                            <label for="filter_target_type" class="text-muted small font-weight-bold text-uppercase mb-1">Target Type</label>
                            <select id="filter_target_type" class="form-control form-control-sm shadow-none">
                                <option value="">All Types</option>
                                @foreach($targetTypes as $key => $meta)
                                    <option value="{{ $key }}">{{ $meta['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-lg-5 col-md-3 mb-2 mb-md-0">
                            <label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search</label>
                            <div class="input-group input-group-sm">
                                <input type="text" id="table_search" class="form-control shadow-none" autocomplete="off" placeholder="Channel, page, service or location...">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary" title="Search"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-2 col-md-2">
                            <button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm" id="btnResetFilter">
                                <i class="fas fa-sync-alt mr-1"></i>Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @can('contact_target_trash')
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white px-3 py-3 border-bottom text-danger">
                    <h3 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-trash-alt mr-2"></i>Contact Targets Trash
                    </h3>
                </div>
                <div class="card-body p-0" id="content-wrapper" style="min-height: 340px;">
                    @include('backoffice.admin.contact_targets.partials.table', [
                        'contactTargets' => $contactTargets,
                        'isTrash' => true,
                    ])
                </div>
            </div>
        @else
            <div class="alert alert-warning border-0 shadow-sm mt-4 font-weight-bold">
                <i class="fas fa-exclamation-triangle mr-2"></i>You do not have permission to view trashed contact targets.
            </div>
        @endcan
    </div>
@endsection

@section('plugins.Sweetalert2', true)
@section('plugins.Select2', true)

@push('css')
    <style>
        #content-wrapper.loading { opacity: .5; pointer-events: none; transition: opacity .3s ease-in-out; }
    </style>
@endpush

@section('js')
    @include('backoffice.admin.contact_targets.partials.script')
@endsection
