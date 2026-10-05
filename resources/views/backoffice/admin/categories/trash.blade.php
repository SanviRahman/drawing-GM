@extends('backoffice.admin.layouts.app')

@section('title', $title)

@section('content')
<div id="category-manager"
     class="container-fluid py-3"
     data-index-url="{{ route('admin.categories.trashed') }}"
     data-create-url=""
     data-bulk-url="{{ route('admin.categories.multiple_action') }}">

    <div class="d-flex align-items-center flex-wrap mb-3" style="gap: 6px;">
        @can('blog_category_list')
            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary btn-sm font-weight-bold shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i>Back to Categories
            </a>
        @endcan

        <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width: 210px;">
            <option value="">-- Bulk Actions --</option>
            @can('blog_category_restore')
                <option value="restore">Restore Selected</option>
            @endcan
            @can('blog_category_force_delete')
                <option value="force_delete">Permanently Delete</option>
            @endcan
        </select>

        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">
            APPLY
        </button>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-3 py-2">
            <form id="filterForm" method="GET" action="{{ route('admin.categories.trashed') }}">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label for="filter_status" class="text-muted small font-weight-bold text-uppercase mb-1">Previous Status</label>
                        <select name="status" id="filter_status" class="form-control form-control-sm shadow-none">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-8 mb-2 mb-md-0">
                        <label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search Trashed Categories</label>
                        <div class="input-group input-group-sm">
                            <input type="text"
                                   name="search"
                                   id="table_search"
                                   class="form-control shadow-none"
                                   value="{{ request('search') }}"
                                   autocomplete="off"
                                   placeholder="Name, slug or description...">
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
        <div class="card-header bg-white px-3 py-3 border-bottom text-danger">
            <h3 class="card-title font-weight-bold mb-0">
                <i class="fas fa-trash-alt mr-1"></i>Blog Categories Trash
            </h3>
        </div>
        <div class="card-body p-0" id="content-wrapper" style="min-height: 340px;">
            @include('backoffice.admin.categories.partials.table', [
                'categories' => $categories,
                'isTrash' => true,
            ])
        </div>
    </div>
</div>

@include('backoffice.admin.categories.partials.script')
@endsection

@section('plugins.Sweetalert2', true)

@push('css')
<style>
    #content-wrapper.loading {
        opacity: .55;
        pointer-events: none;
        transition: opacity .2s ease-in-out;
    }

    .category-description-preview {
        max-width: 360px;
        white-space: normal;
        line-height: 1.35;
    }

    @media (max-width: 767.98px) {
        .category-table,
        .category-table tbody,
        .category-table tr,
        .category-table td {
            display: block;
            width: 100%;
        }

        .category-table thead {
            display: none;
        }

        .category-table tr {
            margin-bottom: 1rem;
            border: 1px solid #e3e6f0 !important;
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, .05);
        }

        .category-table td {
            display: flex !important;
            justify-content: space-between;
            align-items: center;
            border: 0 !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 12px 15px !important;
            text-align: right;
            white-space: normal !important;
        }

        .category-table td:last-child {
            border-bottom: 0 !important;
        }

        .category-table td::before {
            content: attr(data-label);
            margin-right: 1rem;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            color: #858796;
            text-align: left;
        }

        .category-table td.action-cell {
            justify-content: center;
            background: #f8f9fc;
        }

        .category-table td.action-cell::before {
            display: none;
        }
    }
</style>
@endpush
