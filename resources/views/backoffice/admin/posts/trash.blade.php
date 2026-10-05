@extends('backoffice.admin.layouts.app')

@section('title', $title)

@section('content')
<div id="post-manager"
     class="container-fluid py-3"
     data-index-url="{{ route('admin.posts.trashed') }}"
     data-create-url=""
     data-bulk-url="{{ route('admin.posts.multiple_action') }}">

    <div class="d-flex align-items-center flex-wrap mb-3" style="gap:6px;">
        @can('blog_post_list')
            <a href="{{ route('admin.posts.index') }}" class="btn btn-secondary btn-sm font-weight-bold shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i>Back to Blog Posts
            </a>
        @endcan

        <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width:210px;">
            <option value="">-- Bulk Actions --</option>
            @can('blog_post_restore')<option value="restore">Restore Selected</option>@endcan
            @can('blog_post_force_delete')<option value="force_delete">Permanently Delete</option>@endcan
        </select>

        <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">APPLY</button>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body px-3 py-2">
            <form id="filterForm" method="GET" action="{{ route('admin.posts.trashed') }}">
                <div class="row align-items-end">
                    <div class="col-xl-2 col-md-3 mb-2 mb-xl-0">
                        <label for="filter_status" class="text-muted small font-weight-bold text-uppercase mb-1">Previous Status</label>
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
                                <option value="{{ $category->id }}" {{ (string) request('category_id') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}{{ $category->trashed() ? ' [Trashed]' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-2 col-md-3 mb-2 mb-xl-0">
                        <label for="filter_author" class="text-muted small font-weight-bold text-uppercase mb-1">Author</label>
                        <select name="author_id" id="filter_author" class="form-control form-control-sm shadow-none">
                            <option value="">All Authors</option>
                            @foreach($authors as $author)
                                <option value="{{ $author->id }}" {{ (string) request('author_id') === (string) $author->id ? 'selected' : '' }}>{{ $author->name }}{{ $author->trashed() ? ' [Trashed]' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-xl-5 col-md-9 mb-2 mb-md-0">
                        <label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search Trashed Posts</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" id="table_search" class="form-control shadow-none" value="{{ request('search') }}" autocomplete="off" placeholder="Title, slug or excerpt...">
                            <div class="input-group-append"><button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button></div>
                        </div>
                    </div>

                    <div class="col-xl-1 col-md-3">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-block font-weight-bold shadow-sm" id="btnResetFilter" title="Reset"><i class="fas fa-sync-alt"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white px-3 py-3 border-bottom text-danger">
            <h3 class="card-title font-weight-bold mb-0"><i class="fas fa-trash-alt mr-2"></i>Trashed Blog Posts</h3>
        </div>
        <div class="card-body p-0" id="content-wrapper" style="min-height:340px;">
            @include('backoffice.admin.posts.partials.table', ['posts' => $posts, 'isTrash' => true])
        </div>
    </div>
</div>
@endsection

@section('plugins.Sweetalert2', true)

@push('css')
<style>
    #content-wrapper.loading { opacity:.55; pointer-events:none; transition:opacity .2s ease-in-out; }
    .post-table td { vertical-align:middle; }
    .post-title-cell { min-width:230px; white-space:normal; }
    .post-thumb { width:72px; height:52px; object-fit:cover; border-radius:6px; }
</style>
@endpush

@section('js')
@include('backoffice.admin.posts.partials.script')
@endsection
