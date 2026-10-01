@extends('backoffice.admin.layouts.app')
@section('title',$title)
@section('content')
<div id="video-manager" class="container-fluid py-3" data-mode="trash"
    data-index-url="{{ route('admin.videos.trashed') }}" data-create-url=""
    data-bulk-url="{{ route('admin.videos.multiple_action') }}">
    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">
        <a href="{{ route('admin.videos.index') }}" class="btn btn-secondary btn-sm font-weight-bold shadow-sm"><i
                class="fas fa-arrow-left mr-1"></i>Back to Videos</a>
        <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width:210px;">
            <option value="">-- Bulk Actions --</option>
            @can('video_restore')
            <option value="restore">Restore Selected</option>
            @endcan
            @can('video_force_delete')
            <option value="force_delete">Permanently Delete</option>
            @endcan
        </select>
        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm"
            id="btnApplyBulk">APPLY</button>
    </div>
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-3 py-2">
            <form id="filterForm" action="{{ route('admin.videos.trashed') }}" method="GET">
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
                    <div class="col-md-9 mb-2 mb-md-0">
                        <label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search
                            Trashed Videos</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" id="table_search" class="form-control shadow-none"
                                value="{{ request('search') }}" autocomplete="off" placeholder="Search videos...">
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
        <div class="card-header bg-white px-3 py-3 border-bottom text-danger">
            <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-trash-alt mr-1"></i>Videos Trash</h3>
        </div>
        <div class="card-body p-0" id="content-wrapper" style="min-height:340px;">
            @include('backoffice.admin.videos.partials.table',['videos'=>$videos,'isTrash'=>true])
        </div>
    </div>
</div>
@endsection
@section('plugins.Sweetalert2',true)
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
</style>
@endpush
@section('js')
@include('backoffice.admin.videos.partials.script')
@endsection