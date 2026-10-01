@extends('backoffice.admin.layouts.app')
@section('title',$title)
@section('content')
<div id="testimonial-manager" class="container-fluid py-3" data-mode="active"
    data-index-url="{{ route('admin.testimonials.index') }}" data-create-url="{{ route('admin.testimonials.create') }}"
    data-bulk-url="{{ route('admin.testimonials.multiple_action') }}">
    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">
        @can('testimonial_create')
        <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="btnAddRecord"><i
                class="fas fa-plus mr-1"></i>Add Testimonial</button>
        @endcan
        @can('testimonial_trash')
        <a href="{{ route('admin.testimonials.trashed') }}"
            class="btn btn-outline-danger btn-sm font-weight-bold shadow-sm"><i class="fas fa-trash-alt mr-1"></i>Trash
            Bin</a>
        @endcan
        <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width:210px;">
            <option value="">-- Bulk Actions --</option>
            @can('testimonial_update')
            <option value="active">Mark Active</option>
            <option value="inactive">Mark Inactive</option>
            <option value="featured">Mark Featured</option>
            <option value="unfeatured">Remove Featured</option>
            @endcan
            @can('testimonial_delete')
            <option value="delete">Move to Trash</option>
            @endcan
        </select>
        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm"
            id="btnApplyBulk">APPLY</button>
    </div>
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-3 py-2">
            <form id="filterForm" action="{{ route('admin.testimonials.index') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-2"><label
                            class="text-muted small font-weight-bold text-uppercase mb-1">Type</label><select
                            name="type" id="filter_type" class="form-control form-control-sm">
                            <option value="">All Types</option>
                            <option value="text">Text</option>
                            <option value="whatsapp_screenshot">WhatsApp Screenshot</option>
                            <option value="video">Video</option>
                        </select></div>
                    <div class="col-md-2"><label
                            class="text-muted small font-weight-bold text-uppercase mb-1">Source</label><select
                            name="source" id="filter_source" class="form-control form-control-sm">
                            <option value="">All Sources</option>
                            <option value="google">Google</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="facebook">Facebook</option>
                            <option value="direct">Direct</option>
                        </select></div>
                    <div class="col-md-2"><label
                            class="text-muted small font-weight-bold text-uppercase mb-1">Status</label><select
                            name="status" id="filter_status" class="form-control form-control-sm">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select></div>
                    <div class="col-md-2"><label
                            class="text-muted small font-weight-bold text-uppercase mb-1">Featured</label><select
                            name="featured" id="filter_featured" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="yes">Featured</option>
                            <option value="no">Not Featured</option>
                        </select></div>
                    <div class="col-md-3"><label
                            class="text-muted small font-weight-bold text-uppercase mb-1">Search</label>
                        <div class="input-group input-group-sm"><input type="text" name="search" id="table_search"
                                class="form-control" placeholder="Customer or review...">
                            <div class="input-group-append"><button type="button" class="btn btn-primary"
                                    id="btnSearch"><i class="fas fa-search"></i></button></div>
                        </div>
                    </div>
                    <div class="col-md-1"><button type="button" class="btn btn-outline-danger btn-sm btn-block"
                            id="btnResetFilter"><i class="fas fa-sync-alt"></i></button></div>
                </div>
            </form>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white">
            <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-comments text-primary mr-1"></i>Testimonials
                List</h3>
        </div>
        <div class="card-body p-0" id="content-wrapper">
            @include('backoffice.admin.testimonials.partials.table',['testimonials'=>$testimonials,'isTrash'=>false])
        </div>
    </div>
</div>
<div class="modal fade" id="ajaxModal" data-backdrop="static" data-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold text-primary" id="modal-title">Testimonial</h5><button
                    type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body" id="modal-body"></div>
        </div>
    </div>
</div>
@endsection
@section('plugins.Sweetalert2',true)
@section('plugins.Select2',true)
@section('js')
@include('backoffice.admin.testimonials.partials.script')
@endsection