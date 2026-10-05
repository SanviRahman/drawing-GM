@extends('backoffice.admin.layouts.app')

@section('title', $title)

@section('content')
<div id="post-manager"
     class="container-fluid py-3"
     data-index-url="{{ route('admin.posts.index') }}"
     data-create-url="{{ route('admin.posts.create') }}"
     data-bulk-url="{{ route('admin.posts.multiple_action') }}">

    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">
        @can('blog_post_create')
            <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="btnAddRecord">
                <i class="fas fa-plus mr-1"></i>Add Blog Post
            </button>
        @endcan

        @can('blog_post_trash')
            <a href="{{ route('admin.posts.trashed') }}" class="btn btn-outline-danger btn-sm font-weight-bold shadow-sm">
                <i class="fas fa-trash-alt mr-1"></i>Trash Bin
            </a>
        @endcan

        <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width:190px;">
            <option value="">-- Bulk Actions --</option>
            @can('blog_post_publish')<option value="publish">Publish</option>@endcan
            @can('blog_post_unpublish')<option value="unpublish">Move to Draft</option>@endcan
            @can('blog_post_update')<option value="archive">Archive</option>@endcan
            @can('blog_post_delete')<option value="delete">Move to Trash</option>@endcan
        </select>

        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">
            APPLY
        </button>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-3 py-2">
            <form id="filterForm" method="GET" action="{{ route('admin.posts.index') }}">
                <div class="row align-items-end">
                    <div class="col-xl-2 col-md-3 mb-2 mb-xl-0">
                        <label for="filter_status" class="text-muted small font-weight-bold text-uppercase mb-1">Status</label>
                        <select name="status" id="filter_status" class="form-control form-control-sm shadow-none">
                            <option value="">All Statuses</option>
                            @foreach(\App\Models\Post::STATUSES as $value => $label)
                                <option value="{{ $value }}" {{ request('status') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-2 col-md-3 mb-2 mb-xl-0">
                        <label for="filter_category" class="text-muted small font-weight-bold text-uppercase mb-1">Category</label>
                        <select name="category_id" id="filter_category" class="form-control form-control-sm shadow-none">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ (string) request('category_id') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-2 col-md-3 mb-2 mb-xl-0">
                        <label for="filter_author" class="text-muted small font-weight-bold text-uppercase mb-1">Author</label>
                        <select name="author_id" id="filter_author" class="form-control form-control-sm shadow-none">
                            <option value="">All Authors</option>
                            @foreach($authors as $author)
                                <option value="{{ $author->id }}" {{ (string) request('author_id') === (string) $author->id ? 'selected' : '' }}>{{ $author->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-5 col-md-9 mb-2 mb-md-0">
                        <label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <input type="text"
                                   name="search"
                                   id="table_search"
                                   class="form-control shadow-none"
                                   value="{{ request('search') }}"
                                   autocomplete="off"
                                   placeholder="Title, slug or excerpt...">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit" title="Search"><i class="fas fa-search"></i></button>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-1 col-md-3">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm" id="btnResetFilter" title="Reset">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @can('blog_post_list')
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white px-3 py-3 border-bottom">
                <h3 class="card-title font-weight-bold text-dark mb-0">
                    <i class="fas fa-newspaper text-primary mr-1"></i>Blog Posts List
                </h3>
            </div>
            <div class="card-body p-0" id="content-wrapper" style="min-height:340px;">
                @include('backoffice.admin.posts.partials.table', ['posts' => $posts, 'isTrash' => false])
            </div>
        </div>
    @else
        <div class="alert alert-warning border-0 shadow-sm mt-4 font-weight-bold">
            <i class="fas fa-exclamation-triangle mr-2"></i>You do not have permission to view blog posts.
        </div>
    @endcan
</div>

<div class="modal fade" id="ajaxModal" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title font-weight-bold text-primary" id="modal-title">Blog Post Management</h5>
                <button type="button" class="close px-4 shadow-none" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4" id="modal-body"></div>
        </div>
    </div>
</div>
@endsection

@section('plugins.Sweetalert2', true)
@section('plugins.Select2', true)

@push('css')
<style>
    #content-wrapper.loading { opacity:.55; pointer-events:none; transition:opacity .2s ease-in-out; }
    .post-table td { vertical-align:middle; }
    .post-title-cell { min-width:230px; white-space:normal; }
    .post-thumb { width:72px; height:52px; object-fit:cover; border-radius:6px; }
    .post-rich-content img { max-width:100%; height:auto; }

    @media (max-width:767.98px) {
        .post-table, .post-table tbody, .post-table tr, .post-table td { display:block; width:100%; }
        .post-table thead { display:none; }
        .post-table tr { margin-bottom:1rem; border:1px solid #e3e6f0 !important; border-radius:10px; overflow:hidden; background:#fff; box-shadow:0 4px 6px rgba(0,0,0,.05); }
        .post-table td { display:flex !important; justify-content:space-between; align-items:center; border:0 !important; border-bottom:1px solid #f1f5f9 !important; padding:12px 15px !important; text-align:right; white-space:normal !important; }
        .post-table td::before { content:attr(data-label); margin-right:1rem; font-weight:700; font-size:12px; text-transform:uppercase; color:#858796; text-align:left; }
        .post-table td.action-cell { justify-content:center; background:#f8f9fc; }
        .post-table td.action-cell::before { display:none; }
    }
</style>
@endpush

@section('js')
@include('backoffice.admin.posts.partials.script')
@endsection
