@extends('backoffice.admin.layouts.app')

@section('content')
<div id="page-manager"
     class="container-fluid py-3"
     data-mode="active"
     data-index-url="{{ route('admin.media.index') }}"
     data-create-url="{{ route('admin.media.create') }}"
     data-bulk-url="{{ route('admin.media.multiple_action') }}"
     data-trash-url="{{ route('admin.media.trashed') }}">

    <div class="d-flex align-items-center justify-content-between flex-wrap mb-3" style="gap:8px;">
        <div class="d-flex align-items-center flex-wrap" style="gap:6px;">
            @can('media_upload')
                <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="btnAddRecord">
                    <i class="fas fa-cloud-upload-alt mr-1"></i>Upload Media
                </button>
            @endcan
            @can('media_trash')
                <a href="{{ route('admin.media.trashed') }}" class="btn btn-outline-danger btn-sm font-weight-bold shadow-sm">
                    <i class="fas fa-trash-alt mr-1"></i>Trash Bin
                </a>
            @endcan
            @can('media_delete')
                <button type="button" class="btn btn-danger btn-sm font-weight-bold shadow-sm" id="btnBulkDelete">
                    <i class="fas fa-trash-alt mr-1"></i>Move Selected to Trash
                </button>
            @endcan
        </div>
        <button type="button" class="btn btn-outline-primary btn-sm font-weight-bold" id="btnOpenGlobalPicker">
            <i class="fas fa-photo-video mr-1"></i>Open Global Picker
        </button>
    </div>

    <div class="alert alert-info border-0 shadow-sm">
        <i class="fas fa-info-circle mr-1"></i>
        Spatie Media Library is the canonical media source. Moving media to Trash keeps the physical file; Force Delete removes it permanently.
    </div>

    <div class="row mb-3">
        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
            <div class="small-box bg-white shadow-sm border media-stat-card">
                <div class="inner"><h3>{{ number_format($stats['total']) }}</h3><p>Total Media</p></div>
                <div class="icon"><i class="fas fa-folder-open text-primary"></i></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
            <div class="small-box bg-white shadow-sm border media-stat-card">
                <div class="inner"><h3>{{ number_format($stats['images']) }}</h3><p>Images</p></div>
                <div class="icon"><i class="fas fa-image text-success"></i></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3 mb-md-0">
            <div class="small-box bg-white shadow-sm border media-stat-card">
                <div class="inner"><h3>{{ number_format($stats['videos']) }}</h3><p>Videos</p></div>
                <div class="icon"><i class="fas fa-video text-warning"></i></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="small-box bg-white shadow-sm border media-stat-card">
                <div class="inner"><h3>{{ $stats['storage_human'] }}</h3><p>Storage Used</p></div>
                <div class="icon"><i class="fas fa-hdd text-secondary"></i></div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body py-3">
            <div class="row align-items-end">
                <div class="col-lg-4 col-md-6 mb-2 mb-lg-0">
                    <label class="small text-muted font-weight-bold text-uppercase mb-1">Search</label>
                    <input type="search" id="table_search" class="form-control form-control-sm" placeholder="Search file name, collection, owner..." autocomplete="off">
                </div>
                <div class="col-lg-2 col-md-3 mb-2 mb-lg-0">
                    <label class="small text-muted font-weight-bold text-uppercase mb-1">Type</label>
                    <select id="filter_type" class="form-control form-control-sm">
                        <option value="">All Types</option>
                        <option value="image">Images</option>
                        <option value="video">Videos</option>
                        <option value="file">Files</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 mb-2 mb-lg-0">
                    <label class="small text-muted font-weight-bold text-uppercase mb-1">Collection</label>
                    <select id="filter_collection" class="form-control form-control-sm">
                        <option value="">All Collections</option>
                        @foreach($filters['collections'] as $collection)
                            <option value="{{ $collection }}">{{ $collection }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6 mb-2 mb-lg-0">
                    <label class="small text-muted font-weight-bold text-uppercase mb-1">Disk</label>
                    <select id="filter_disk" class="form-control form-control-sm">
                        <option value="">All Disks</option>
                        @foreach($filters['disks'] as $disk)
                            <option value="{{ $disk }}">{{ $disk }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-6">
                    <div class="btn-group btn-group-sm btn-block">
                        <button type="button" class="btn btn-primary" id="btnSearch"><i class="fas fa-search"></i></button>
                        <button type="button" class="btn btn-outline-secondary" id="btnResetFilter"><i class="fas fa-sync-alt"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-photo-video text-primary mr-1"></i>All Media</h3>
        </div>
        <div class="card-body p-0" id="content-wrapper">
            @include('backoffice.admin.media.partials.table', ['media' => $media, 'isTrash' => false])
        </div>
    </div>
</div>

<div class="modal fade" id="ajaxModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-primary" id="modal-title">Media</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="modal-body"></div>
        </div>
    </div>
</div>
@endsection

@section('plugins.Sweetalert2', true)

@push('css')
<style>
    .media-stat-card { border-radius: 12px; overflow: hidden; }
    .media-stat-card .inner p { color:#6c757d; font-weight:600; margin-bottom:0; }
    .media-stat-card .icon { top:12px; right:14px; font-size:48px; opacity:.22; }
    #content-wrapper.loading { opacity:.55; pointer-events:none; }
</style>
@endpush

@section('js')
    @include('backoffice.admin.media.partials.script')
@endsection
