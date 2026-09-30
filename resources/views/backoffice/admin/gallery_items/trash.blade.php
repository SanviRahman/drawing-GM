@extends('backoffice.admin.layouts.app')
@section('content')

<div id="page-manager" class="container-fluid py-3" data-mode="trash"
    data-index-url="{{ route('admin.gallery_items.trashed') }}"
    data-bulk-url="{{ route('admin.gallery_items.multiple_action') }}">

    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">

        @can('gallery_item_list')
        <a href="{{ route('admin.gallery_items.index') }}"
            class="btn btn-secondary btn-sm font-weight-bold shadow-sm"><i class="fas fa-arrow-left mr-1"></i>Back to
            Gallery Items</a>
        @endcan

        <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width:210px;">
            <option value="">-- Bulk Actions --</option>

            @can('gallery_item_restore')
            <option value="restore">Restore Selected</option>
            @endcan

            @can('gallery_item_force_delete')
            <option value="force_delete">Permanently Delete</option>
            @endcan

        </select>

        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm"
            id="btnApplyBulk">APPLY</button>

    </div>


    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-3 py-2">

            <div class="row align-items-end">

                <div class="col-md-10">
                    <label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search
                        Trashed Gallery Items</label>

                    <div class="input-group input-group-sm">

                        <input type="text" id="table_search" class="form-control shadow-none" autocomplete="off"
                            placeholder="Search title or caption...">

                        <div class="input-group-append">
                            <button type="button" class="btn btn-outline-secondary" id="btnClearSearch"><i
                                    class="fas fa-times"></i></button>
                            <button type="button" class="btn btn-primary" id="btnSearch"><i
                                    class="fas fa-search"></i></button>
                        </div>

                    </div>
                </div>


                <div class="col-md-2">
                    <button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm"
                        id="btnResetFilter"><i class="fas fa-sync-alt mr-1"></i>Reset</button>
                </div>

            </div>

        </div>
    </div>


    @can('gallery_item_trash')

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white px-3 py-3 border-bottom text-danger">

            <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-trash-alt mr-2"></i>Trash Bin</h3>

        </div>


        <div class="card-body p-0" id="content-wrapper" style="min-height:340px;">

            @include('backoffice.admin.gallery_items.partials.table',['items'=>$items,'isTrash'=>true])

        </div>

    </div>

    @else

    <div class="alert alert-warning border-0 shadow-sm mt-4 font-weight-bold"><i
            class="fas fa-exclamation-triangle mr-2"></i>You do not have permission to view trashed gallery items.</div>

    @endcan


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
</style>
@endpush

@section('js')
@include('backoffice.admin.gallery_items.partials.script')
@endsection