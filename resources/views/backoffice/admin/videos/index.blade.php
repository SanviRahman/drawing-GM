@extends('backoffice.admin.layouts.app')
@section('title',$title)
@section('content')
<div id="video-manager" class="container-fluid py-3" data-mode="active"
    data-index-url="{{ route('admin.videos.index') }}" data-create-url="{{ route('admin.videos.create') }}"
    data-bulk-url="{{ route('admin.videos.multiple_action') }}">
    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">
        @can('video_create')
        <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="btnAddRecord"><i
                class="fas fa-plus mr-1"></i>Add Video</button>
        @endcan
        @can('video_trash')
        <a href="{{ route('admin.videos.trashed') }}"
            class="btn btn-outline-danger btn-sm font-weight-bold shadow-sm"><i class="fas fa-trash-alt mr-1"></i>Trash
            Bin</a>
        @endcan
        <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width:190px;">
            <option value="">-- Bulk Actions --</option>
            @can('video_toggle')
            <option value="activate">Activate</option>
            <option value="deactivate">Deactivate</option>
            @endcan
            @can('video_delete')
            <option value="delete">Move to Trash</option>
            @endcan
        </select>
        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm"
            id="btnApplyBulk">APPLY</button>
    </div>
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-3 py-2">
            <form id="filterForm" action="{{ route('admin.videos.index') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label for="filter_source_type"
                            class="text-muted small font-weight-bold text-uppercase mb-1">Source Type</label>
                        <select name="source_type" id="filter_source_type"
                            class="form-control form-control-sm shadow-none">
                            <option value="">All Sources</option>
                            <option value="youtube" {{ request('source_type')==='youtube'?'selected':'' }}>YouTube
                            </option>
                            <option value="embed" {{ request('source_type')==='embed'?'selected':'' }}>Embedded</option>
                            <option value="upload" {{ request('source_type')==='upload'?'selected':'' }}>Recording /
                                Upload</option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label for="filter_processing_status"
                            class="text-muted small font-weight-bold text-uppercase mb-1">Processing</label>
                        <select name="processing_status" id="filter_processing_status"
                            class="form-control form-control-sm shadow-none">
                            <option value="">All Processing</option>
                            <option value="ready" {{ request('processing_status')==='ready'?'selected':'' }}>Ready
                            </option>
                            <option value="pending" {{ request('processing_status')==='pending'?'selected':'' }}>Pending
                            </option>
                            <option value="processing" {{ request('processing_status')==='processing'?'selected':'' }}>
                                Processing</option>
                            <option value="failed" {{ request('processing_status')==='failed'?'selected':'' }}>Failed
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 mb-md-0">
                        <label for="filter_status"
                            class="text-muted small font-weight-bold text-uppercase mb-1">Status</label>
                        <select name="status" id="filter_status" class="form-control form-control-sm shadow-none">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status')==='active'?'selected':'' }}>Active</option>
                            <option value="inactive" {{ request('status')==='inactive'?'selected':'' }}>Inactive
                            </option>
                        </select>
                    </div>
                    <div class="col-md-5 mb-2 mb-md-0">
                        <label for="table_search"
                            class="text-muted small font-weight-bold text-uppercase mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" id="table_search" class="form-control shadow-none"
                                value="{{ request('search') }}" autocomplete="off"
                                placeholder="Title, caption, video ID or URL...">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" id="btnSearch"><i
                                        class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm"
                            id="btnResetFilter"><i class="fas fa-sync-alt"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white px-3 py-3 border-bottom">
            <h3 class="card-title font-weight-bold text-dark mb-0"><i class="fas fa-video text-primary mr-1"></i>Videos
                List</h3>
        </div>
        <div class="card-body p-0" id="content-wrapper" style="min-height:340px;">
            @include('backoffice.admin.videos.partials.table',['videos'=>$videos,'isTrash'=>false])
        </div>
    </div>
</div>
<div class="modal fade" id="ajaxModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog"
    aria-labelledby="modal-title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title font-weight-bold text-primary" id="modal-title">Video Management</h5>
                <button type="button" class="close px-4 shadow-none" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body p-4" id="modal-body"></div>
        </div>
    </div>
</div>
@endsection
@section('plugins.Sweetalert2',true)
@section('plugins.Select2',true)
@push('css')
<style>
#content-wrapper.loading {
    opacity: .5;
    pointer-events: none;
    transition: opacity .3s ease-in-out;
}

.video-table td {
    vertical-align: middle;
}

.video-thumb {
    width: 64px;
    height: 52px;
    object-fit: cover;
}

.video-empty-thumb {
    width: 64px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.video-table .caption-preview {
    max-width: 300px;
    white-space: normal;
}

#ajaxModal .modal-xl {
    max-width: 1140px;
}

#ajaxModal .modal-body {
    max-height: calc(100vh - 140px);
    overflow-y: auto;
}
</style>
@endpush
@section('js')
@include('backoffice.admin.videos.partials.script')
@endsection