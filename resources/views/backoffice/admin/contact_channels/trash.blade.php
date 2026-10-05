@extends('backoffice.admin.layouts.app')

@section('content')
<div id="contact-channel-manager"
     class="container-fluid py-3"
     data-mode="trash"
     data-index-url="{{ route('admin.contact_channels.trashed') }}"
     data-bulk-url="{{ route('admin.contact_channels.multiple_action') }}">

    <div class="d-flex align-items-center flex-wrap mb-3" style="gap: 6px;">
        @can('contact_channel_list')
            <a href="{{ route('admin.contact_channels.index') }}" class="btn btn-secondary btn-sm font-weight-bold shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i>Back to Contact Channels
            </a>
        @endcan

        <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width: 210px;">
            <option value="">-- Bulk Actions --</option>
            @can('contact_channel_restore')
                <option value="restore">Restore Selected</option>
            @endcan
            @can('contact_channel_force_delete')
                <option value="force_delete">Permanently Delete</option>
            @endcan
        </select>

        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">APPLY</button>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-3 py-2">
            <form id="filterForm" action="{{ route('admin.contact_channels.trashed') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2 mb-md-0">
                        <label for="filter_type" class="text-muted small font-weight-bold text-uppercase mb-1">Type</label>
                        <select name="type" id="filter_type" class="form-control form-control-sm shadow-none">
                            <option value="">All Types</option>
                            @foreach($types as $key => $label)
                                <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-7 mb-2 mb-md-0">
                        <label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search Trashed Channels</label>
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

                    <div class="col-md-2">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm" id="btnResetFilter">
                            <i class="fas fa-sync-alt mr-1"></i>Reset
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @can('contact_channel_trash')
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white px-3 py-3 border-bottom text-danger">
                <h3 class="card-title font-weight-bold mb-0">
                    <i class="fas fa-trash-alt mr-2"></i>Contact Channels Trash Bin
                </h3>
            </div>
            <div class="card-body p-0" id="content-wrapper" style="min-height: 340px;">
                @include('backoffice.admin.contact_channels.partials.table', [
                    'contactChannels' => $contactChannels,
                    'isTrash' => true,
                ])
            </div>
        </div>
    @else
        <div class="alert alert-warning border-0 shadow-sm mt-4 font-weight-bold">
            <i class="fas fa-exclamation-triangle mr-2"></i>You do not have permission to view trashed contact channels.
        </div>
    @endcan

    @include('backoffice.admin.contact_channels.partials.script')
</div>
@endsection

@section('plugins.Sweetalert2', true)

@push('css')
<style>
    #content-wrapper.loading { opacity: .55; pointer-events: none; transition: opacity .2s ease-in-out; }
    .contact-channel-icon {
        width: 42px; height: 42px; border-radius: 50%; display: inline-flex;
        align-items: center; justify-content: center; font-size: 19px; color: #fff;
        box-shadow: 0 2px 6px rgba(0,0,0,.12);
    }
    @media (max-width: 767.98px) {
        .contact-channel-table, .contact-channel-table tbody, .contact-channel-table tr, .contact-channel-table td { display:block; width:100%; }
        .contact-channel-table thead { display:none; }
        .contact-channel-table tr { margin-bottom:1rem; border:1px solid #e3e6f0 !important; border-radius:10px; overflow:hidden; background:#fff; }
        .contact-channel-table td { display:flex !important; justify-content:space-between; align-items:center; border:0 !important; border-bottom:1px solid #f1f5f9 !important; padding:12px 15px !important; text-align:right; white-space:normal !important; }
        .contact-channel-table td:last-child { border-bottom:0 !important; }
        .contact-channel-table td::before { content:attr(data-label); margin-right:1rem; font-weight:700; font-size:12px; text-transform:uppercase; color:#858796; text-align:left; }
        .contact-channel-table td.action-cell { justify-content:center; background:#f8f9fc; }
        .contact-channel-table td.action-cell::before { display:none; }
    }
</style>
@endpush
