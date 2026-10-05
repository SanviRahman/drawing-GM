@extends('backoffice.admin.layouts.app')

@section('content')
<div id="contact-channel-manager"
     class="container-fluid py-3"
     data-mode="active"
     data-index-url="{{ route('admin.contact_channels.index') }}"
     data-create-url="{{ route('admin.contact_channels.create') }}"
     data-bulk-url="{{ route('admin.contact_channels.multiple_action') }}"
     data-reorder-url="{{ route('admin.contact_channels.reorder') }}"
     data-trash-url="{{ route('admin.contact_channels.trashed') }}">

    <div class="d-flex align-items-center flex-wrap mb-3" style="gap: 6px;">
        @can('contact_channel_create')
            <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="btnAddRecord">
                <i class="fas fa-plus mr-1"></i>Add Contact Channel
            </button>
        @endcan

        @can('contact_channel_trash')
            <a href="{{ route('admin.contact_channels.trashed') }}" class="btn btn-outline-danger btn-sm font-weight-bold shadow-sm">
                <i class="fas fa-trash-alt mr-1"></i>Trash Bin
            </a>
        @endcan

        <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width: 190px;">
            <option value="">-- Bulk Actions --</option>
            @can('contact_channel_toggle')
                <option value="activate">Mark as Active</option>
                <option value="deactivate">Mark as Inactive</option>
            @endcan
            @can('contact_channel_delete')
                <option value="delete">Move to Trash</option>
            @endcan
        </select>

        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">
            APPLY
        </button>

        @can('contact_channel_reorder')
            <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold shadow-sm" id="btnSaveOrder">
                <i class="fas fa-sort-numeric-down mr-1"></i>Save Order
            </button>
        @endcan
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-3 py-2">
            <form id="filterForm" action="{{ route('admin.contact_channels.index') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label for="filter_type" class="text-muted small font-weight-bold text-uppercase mb-1">Type</label>
                        <select name="type" id="filter_type" class="form-control form-control-sm shadow-none">
                            <option value="">All Types</option>
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 mb-2 mb-md-0">
                        <label for="filter_status" class="text-muted small font-weight-bold text-uppercase mb-1">Status</label>
                        <select name="status" id="filter_status" class="form-control form-control-sm shadow-none">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2 mb-md-0">
                        <label for="filter_default" class="text-muted small font-weight-bold text-uppercase mb-1">Default</label>
                        <select name="default" id="filter_default" class="form-control form-control-sm shadow-none">
                            <option value="">All</option>
                            <option value="yes" {{ request('default') === 'yes' ? 'selected' : '' }}>Default Only</option>
                            <option value="no" {{ request('default') === 'no' ? 'selected' : '' }}>Non-default</option>
                        </select>
                    </div>

                    <div class="col-md-5 mb-2 mb-md-0">
                        <label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <input type="text"
                                   name="search"
                                   id="table_search"
                                   value="{{ request('search') }}"
                                   class="form-control shadow-none"
                                   autocomplete="off"
                                   placeholder="Label, region, number/email/URL...">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-primary" title="Search"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm" id="btnResetFilter" title="Reset Filters">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @can('contact_channel_list')
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white px-3 py-3 border-bottom">
                <h3 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-address-book text-primary mr-1"></i>Contact Channels List
                </h3>
            </div>
            <div class="card-body p-0" id="content-wrapper" style="min-height: 340px;">
                @include('backoffice.admin.contact_channels.partials.table', [
                    'contactChannels' => $contactChannels,
                    'isTrash' => false,
                ])
            </div>
        </div>
    @else
        <div class="alert alert-warning border-0 shadow-sm mt-4 font-weight-bold">
            <i class="fas fa-exclamation-triangle mr-2"></i>You do not have permission to view contact channels.
        </div>
    @endcan

    <div class="modal fade" id="ajaxModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title font-weight-bold text-primary" id="modal-title">Contact Channel Management</h5>
                    <button type="button" class="close px-4 shadow-none" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4" id="modal-body"></div>
            </div>
        </div>
    </div>

    @include('backoffice.admin.contact_channels.partials.script')
</div>
@endsection

@section('plugins.Sweetalert2', true)

@push('css')
<style>
    #content-wrapper.loading { opacity: .55; pointer-events: none; transition: opacity .2s ease-in-out; }

    .contact-channel-icon {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 19px;
        color: #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,.12);
    }

    @media (max-width: 767.98px) {
        .contact-channel-table,
        .contact-channel-table tbody,
        .contact-channel-table tr,
        .contact-channel-table td { display: block; width: 100%; }
        .contact-channel-table thead { display: none; }
        .contact-channel-table tr {
            margin-bottom: 1rem;
            border: 1px solid #e3e6f0 !important;
            border-radius: 10px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 4px 6px rgba(0,0,0,.05);
        }
        .contact-channel-table td {
            display: flex !important;
            justify-content: space-between;
            align-items: center;
            border: 0 !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 12px 15px !important;
            text-align: right;
            white-space: normal !important;
        }
        .contact-channel-table td:last-child { border-bottom: 0 !important; }
        .contact-channel-table td::before {
            content: attr(data-label);
            margin-right: 1rem;
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            color: #858796;
            text-align: left;
        }
        .contact-channel-table td.action-cell { justify-content: center; background: #f8f9fc; }
        .contact-channel-table td.action-cell::before { display: none; }
    }
</style>
@endpush
