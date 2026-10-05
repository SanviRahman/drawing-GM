@extends('backoffice.admin.layouts.app')

@section('content')
    <div id="faq-manager"
         class="container-fluid py-3"
         data-mode="active"
         data-index-url="{{ route('admin.faqs.index') }}"
         data-create-url="{{ route('admin.faqs.create') }}"
         data-bulk-url="{{ route('admin.faqs.multiple_action') }}"
         data-trash-url="{{ route('admin.faqs.trashed') }}">

        <div class="d-flex align-items-center flex-wrap mb-3" style="gap: 6px;">
            @can('faq_create')
                <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="btnAddRecord">
                    <i class="fas fa-plus mr-1"></i>Add New FAQ
                </button>
            @endcan

            @can('faq_trash')
                <a href="{{ route('admin.faqs.trashed') }}" class="btn btn-outline-danger btn-sm font-weight-bold shadow-sm">
                    <i class="fas fa-trash-alt mr-1"></i>Trash Bin
                </a>
            @endcan

            <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width: 200px;">
                <option value="">-- Bulk Actions --</option>
                @can('faq_toggle')
                    <option value="activate">Mark as Active</option>
                    <option value="deactivate">Mark as Inactive</option>
                @endcan
                @can('faq_delete')
                    <option value="delete">Move to Trash</option>
                @endcan
            </select>

            <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">APPLY</button>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body px-3 py-2">
                <form id="filterForm" action="{{ route('admin.faqs.index') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-2 mb-2 mb-md-0">
                            <label for="filter_status" class="text-muted small font-weight-bold text-uppercase mb-1">Status</label>
                            <select name="status" id="filter_status" class="form-control form-control-sm shadow-none">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-2 mb-md-0">
                            <label for="filter_target_type" class="text-muted small font-weight-bold text-uppercase mb-1">Assigned To</label>
                            <select name="target_type" id="filter_target_type" class="form-control form-control-sm shadow-none">
                                <option value="">All Targets</option>
                                <option value="page" {{ request('target_type') === 'page' ? 'selected' : '' }}>Pages</option>
                                <option value="service" {{ request('target_type') === 'service' ? 'selected' : '' }}>Services</option>
                                <option value="location" {{ request('target_type') === 'location' ? 'selected' : '' }}>Locations</option>
                                <option value="unassigned" {{ request('target_type') === 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                            </select>
                        </div>

                        <div class="col-md-5 mb-2 mb-md-0">
                            <label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search FAQs</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" id="table_search" class="form-control shadow-none" value="{{ request('search') }}" autocomplete="off" placeholder="Search question or answer...">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary" id="btnSearch" title="Search"><i class="fas fa-search"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm" id="btnResetFilter">
                                <i class="fas fa-sync-alt mr-1"></i>Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @can('faq_list')
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white px-3 py-3 border-bottom">
                    <h3 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-question-circle text-primary mr-1"></i>FAQ List
                    </h3>
                </div>
                <div class="card-body p-0" id="content-wrapper" style="min-height: 340px;">
                    @include('backoffice.admin.faqs.partials.table', ['faqs' => $faqs, 'isTrash' => false])
                </div>
            </div>
        @else
            <div class="alert alert-warning border-0 shadow-sm mt-4 font-weight-bold">
                <i class="fas fa-exclamation-triangle mr-2"></i>You do not have permission to view FAQs.
            </div>
        @endcan
    </div>

    @include('backoffice.admin.faqs.partials.script')
@endsection

@section('plugins.Sweetalert2', true)
@section('plugins.Select2', true)
