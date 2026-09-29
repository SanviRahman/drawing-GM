@extends('backoffice.admin.layouts.app')

@section('content')
<div id="page-manager"
     class="container-fluid py-3"
     data-mode="trash"
     data-index-url="{{ route('admin.media.trashed') }}"
     data-bulk-url="{{ route('admin.media.multiple_action') }}">

    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">
        <a href="{{ route('admin.media.index') }}" class="btn btn-outline-primary btn-sm font-weight-bold">
            <i class="fas fa-arrow-left mr-1"></i>Active Media
        </a>
        @can('media_restore')
            <button type="button" class="btn btn-success btn-sm font-weight-bold" id="btnBulkRestore"><i class="fas fa-undo mr-1"></i>Restore Selected</button>
        @endcan
        @can('media_force_delete')
            <button type="button" class="btn btn-danger btn-sm font-weight-bold" id="btnBulkForceDelete"><i class="fas fa-fire mr-1"></i>Force Delete Selected</button>
        @endcan
    </div>

    <div class="alert alert-warning border-0 shadow-sm">
        <i class="fas fa-exclamation-triangle mr-1"></i>
        Trash items still keep their physical files. Force Delete permanently removes the media row and stored file.
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <div class="row align-items-end">
                <div class="col-lg-5 col-md-6 mb-2 mb-lg-0">
                    <label class="small text-muted font-weight-bold text-uppercase mb-1">Search</label>
                    <input type="search" id="table_search" class="form-control form-control-sm" placeholder="Search trashed media..." autocomplete="off">
                </div>
                <div class="col-lg-2 col-md-3 mb-2 mb-lg-0">
                    <label class="small text-muted font-weight-bold text-uppercase mb-1">Type</label>
                    <select id="filter_type" class="form-control form-control-sm">
                        <option value="">All Types</option><option value="image">Images</option><option value="video">Videos</option><option value="file">Files</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-3 mb-2 mb-lg-0">
                    <label class="small text-muted font-weight-bold text-uppercase mb-1">Collection</label>
                    <select id="filter_collection" class="form-control form-control-sm"><option value="">All Collections</option>@foreach($filters['collections'] as $collection)<option value="{{ $collection }}">{{ $collection }}</option>@endforeach</select>
                </div>
                <div class="col-lg-2">
                    <div class="btn-group btn-group-sm btn-block">
                        <button type="button" class="btn btn-primary" id="btnSearch"><i class="fas fa-search"></i></button>
                        <button type="button" class="btn btn-outline-secondary" id="btnResetFilter"><i class="fas fa-sync-alt"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3"><h3 class="card-title font-weight-bold mb-0"><i class="fas fa-trash-alt text-danger mr-1"></i>Media Trash Bin</h3></div>
        <div class="card-body p-0" id="content-wrapper">
            @include('backoffice.admin.media.partials.table', ['media' => $media, 'isTrash' => true])
        </div>
    </div>
</div>
@endsection

@section('plugins.Sweetalert2', true)
@section('js')
    @include('backoffice.admin.media.partials.script')
@endsection
