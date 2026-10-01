@extends('backoffice.admin.layouts.app')
@section('title',$title)
@section('content')
<div id="testimonial-manager" class="container-fluid py-3" data-mode="trash"
    data-index-url="{{ route('admin.testimonials.trashed') }}"
    data-bulk-url="{{ route('admin.testimonials.multiple_action') }}">
    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">
        <a href="{{ route('admin.testimonials.index') }}" class="btn btn-secondary btn-sm font-weight-bold"><i
                class="fas fa-arrow-left mr-1"></i>Back to Testimonials</a>
        <select id="bulk_action" class="form-control form-control-sm" style="width:210px;">
            <option value="">-- Bulk Actions --</option>
            @can('testimonial_restore')<option value="restore">Restore Selected</option>@endcan
            @can('testimonial_force_delete')<option value="force_delete">Permanently Delete</option>@endcan
        </select>
        <button type="button" class="btn btn-secondary btn-sm font-weight-bold" id="btnApplyBulk">APPLY</button>
    </div>
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-3 py-2">
            <form id="filterForm" action="{{ route('admin.testimonials.trashed') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-3"><label class="small font-weight-bold">Type</label><select name="type"
                            id="filter_type" class="form-control form-control-sm">
                            <option value="">All Types</option>
                            <option value="text">Text</option>
                            <option value="whatsapp_screenshot">WhatsApp Screenshot</option>
                            <option value="video">Video</option>
                        </select></div>
                    <div class="col-md-8"><label class="small font-weight-bold">Search</label>
                        <div class="input-group input-group-sm"><input type="text" name="search" id="table_search"
                                class="form-control">
                            <div class="input-group-append"><button type="button" id="btnSearch"
                                    class="btn btn-primary"><i class="fas fa-search"></i></button></div>
                        </div>
                    </div>
                    <div class="col-md-1"><button type="button" id="btnResetFilter"
                            class="btn btn-outline-danger btn-sm btn-block"><i class="fas fa-sync-alt"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white text-danger">
            <h3 class="card-title font-weight-bold">Testimonials Trash</h3>
        </div>
        <div class="card-body p-0" id="content-wrapper">
            @include('backoffice.admin.testimonials.partials.table',['testimonials'=>$testimonials,'isTrash'=>true])
        </div>
    </div>
</div>
@endsection
@section('plugins.Sweetalert2',true)
@section('js')
@include('backoffice.admin.testimonials.partials.script')
@endsection