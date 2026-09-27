@extends('backoffice.admin.layouts.app')
@section('content')
    <div id="page-manager"
         class="container-fluid py-3"
         data-mode="active"
         data-index-url="{{ route('admin.admins.index') }}"
         data-create-url="{{ route('admin.admins.create') }}"
         data-bulk-url="{{ route('admin.admins.multiple_action') }}"
         data-trash-url="{{ route('admin.admins.trashed') }}">


        <!-- Top Action Bar -->
        <div class="d-flex align-items-center flex-wrap mb-3" style="gap: 6px;">
            @can('admin_create')
                <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="btnAddRecord">
                    <i class="fas fa-plus mr-1"></i>Add New Admin
                </button>
            @endcan

            @can('admin_trash')
                <a href="{{ route('admin.admins.trashed') }}" class="btn btn-outline-danger btn-sm font-weight-bold shadow-sm">
                    <i class="fas fa-trash-alt mr-1"></i>Trash Bin
                </a>
            @endcan

            <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width: 190px;">
                <option value="">-- Bulk Actions --</option>
                <option value="active">Mark as Active</option>
                <option value="inactive">Mark as Inactive</option>
                @can('admin_delete')
                    <option value="delete">Move to Trash</option>
                @endcan
            </select>

            <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">
                APPLY
            </button>
        </div>

        <!-- Filters Section -->
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body px-3 py-2">
                <div class="row align-items-end">
                    <!-- Filter by Role -->
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label for="filter_role" class="text-muted small font-weight-bold text-uppercase mb-1">Filter By Role</label>
                        <select id="filter_role" class="form-control form-control-sm shadow-none">
                            <option value="">All Roles</option>
                            @if(isset($roles))
                                @foreach($roles as $role)
                                    <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Search Input -->
                    <div class="col-md-7 mb-2 mb-md-0">
                        <label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search Admins</label>
                        <div class="input-group input-group-sm">
                            <input type="text" id="table_search" class="form-control shadow-none" autocomplete="off" placeholder="Search by name, email...">
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
        @can('admin_list')
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white px-3 py-3 border-bottom">
                    <h3 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-list text-primary mr-1"></i>Admin Users List
                    </h3>
                </div>
                <div class="card-body p-0" id="content-wrapper" style="min-height: 340px;">
                    @include('backoffice.admin.admins.partials.table', ['admins' => $admins, 'isTrash' => false])
                </div>
            </div>
        @else
            <div class="alert alert-warning border-0 shadow-sm mt-4 font-weight-bold">
                <i class="fas fa-exclamation-triangle mr-2"></i> You do not have permission to view admins.
            </div>
        @endcan
    </div>

    <!-- AJAX Modal -->
    <div class="modal fade" id="ajaxModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title font-weight-bold text-primary" id="modal-title">Admin Management</h5>
                    <button type="button" class="close px-4 shadow-none" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4" id="modal-body"></div>
            </div>
        </div>
    </div>
@endsection

@section('plugins.Sweetalert2', true)
@section('plugins.Select2', true)

@push('css')
    <style>
        #content-wrapper.loading { opacity: 0.5; pointer-events: none; transition: opacity 0.3s ease-in-out; }
    </style>
@endpush

@section('js')
    @include('backoffice.admin.admins.partials.script')
@endsection
