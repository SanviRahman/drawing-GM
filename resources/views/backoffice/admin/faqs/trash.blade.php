@extends('backoffice.admin.layouts.app')

@section('content')
    <div id="faq-manager"
         class="container-fluid py-3"
         data-mode="trash"
         data-index-url="{{ route('admin.faqs.trashed') }}"
         data-bulk-url="{{ route('admin.faqs.multiple_action') }}">

        <div class="d-flex align-items-center flex-wrap mb-3" style="gap: 6px;">
            @can('faq_list')
                <a href="{{ route('admin.faqs.index') }}" class="btn btn-secondary btn-sm font-weight-bold shadow-sm">
                    <i class="fas fa-arrow-left mr-1"></i>Back to FAQs
                </a>
            @endcan

            <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width: 220px;">
                <option value="">-- Bulk Actions --</option>
                @can('faq_restore')
                    <option value="restore">Restore Selected</option>
                @endcan
                @can('faq_force_delete')
                    <option value="force_delete">Permanently Delete</option>
                @endcan
            </select>

            <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">APPLY</button>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body px-3 py-2">
                <form id="filterForm" action="{{ route('admin.faqs.trashed') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label for="filter_target_type" class="text-muted small font-weight-bold text-uppercase mb-1">Assigned To</label>
                            <select name="target_type" id="filter_target_type" class="form-control form-control-sm shadow-none">
                                <option value="">All Targets</option>
                                <option value="page" {{ request('target_type') === 'page' ? 'selected' : '' }}>Pages</option>
                                <option value="service" {{ request('target_type') === 'service' ? 'selected' : '' }}>Services</option>
                                <option value="location" {{ request('target_type') === 'location' ? 'selected' : '' }}>Locations</option>
                                <option value="menu_item" {{ request('target_type') === 'menu_item' ? 'selected' : '' }}>Primary Navbar</option>
                                <option value="unassigned" {{ request('target_type') === 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                            </select>
                        </div>

                        <div class="col-md-7 mb-2 mb-md-0">
                            <label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search Trashed FAQs</label>
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

        @can('faq_trash')
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white px-3 py-3 border-bottom text-danger">
                    <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-trash-alt mr-2"></i>FAQ Trash Bin</h3>
                </div>
                <div class="card-body p-0" id="content-wrapper" style="min-height: 340px;">
                    @include('backoffice.admin.faqs.partials.table', ['faqs' => $faqs, 'isTrash' => true])
                </div>
            </div>
        @else
            <div class="alert alert-warning border-0 shadow-sm mt-4 font-weight-bold">
                <i class="fas fa-exclamation-triangle mr-2"></i>You do not have permission to view trashed FAQs.
            </div>
        @endcan
    </div>

    @include('backoffice.admin.faqs.partials.script')
@endsection

@section('plugins.Sweetalert2', true)
@section('plugins.Select2', true)
