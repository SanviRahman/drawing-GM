@extends('backoffice.admin.layouts.app')

@section('content')
    <div id="permission-page"
         class="container-fluid py-3"
         data-mode="trash"
         data-index-url="{{ route('admin.permissions.trashed') }}"
         data-bulk-url="{{ route('admin.permissions.multiple_action') }}">

        <!-- Top Action Bar -->
        <div class="d-flex align-items-center flex-wrap mb-3" style="gap: 6px;">
            @can('permission_list')
                <a href="{{ route('admin.permissions.index') }}" class="btn btn-secondary btn-sm font-weight-bold shadow-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Permissions
                </a>
            @endcan

            <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width: 210px;">
                <option value="">-- Bulk Actions --</option>
                @can('permission_restore')
                    <option value="restore">Restore Selected</option>
                @endcan
                @can('permission_force_delete')
                    <option value="force_delete">Permanently Delete</option>
                @endcan
            </select>

            <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">
                APPLY
            </button>
        </div>

        <!-- Search Section -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body px-3 py-2">
                <div class="row align-items-end">
                    <div class="col-md-10 mb-2 mb-md-0">
                        <label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search Trashed</label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="table_search" class="form-control shadow-none" autocomplete="off" placeholder="Search trashed permission name...">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-outline-secondary" id="btnClearSearch" title="Clear"><i class="fas fa-times"></i></button>
                                <button type="button" class="btn btn-primary" id="btnSearch" title="Search"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm" id="btnResetFilter">
                            <i class="fas fa-sync-alt mr-1"></i>Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table Section -->
        @can('permission_trash')
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white px-3 py-3 border-bottom text-danger">
                    <h3 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-trash-alt mr-2"></i>Trash Bin
                    </h3>
                </div>

                <div class="card-body p-0" id="content-wrapper" style="min-height: 340px;">
                    @include('backoffice.admin.permissions.partials.table', [
                        'permissions' => $permissions,
                        'isTrash' => true,
                    ])
                </div>
            </div>
        @else
            <div class="alert alert-warning border-0 shadow-sm mt-4 font-weight-bold">
                <i class="fas fa-exclamation-triangle mr-2"></i> You do not have permission to view trashed permissions.
            </div>
        @endcan
    </div>
@endsection

@section('plugins.Sweetalert2', true)

@push('css')
    <style>
        #content-wrapper.loading { opacity: 0.5; pointer-events: none; transition: opacity 0.3s ease-in-out; }
        .permission-table { min-width: 700px; margin-bottom: 0; }
        .permission-table th, .permission-table td { vertical-align: middle !important; }
    </style>
@endpush

@section('js')
    @include('backoffice.admin.permissions.partials.script')
@endsection
