@extends('backoffice.admin.layouts.app')

@section('content')
    <div id="faq-manager"
         class="container-fluid py-3"
         data-mode="active"
         data-index-url="{{ route('admin.faqs.index') }}"
         data-create-url="{{ route('admin.faqs.create') }}"
         data-bulk-url="{{ route('admin.faqs.multiple_action') }}"
         data-trash-url="{{ route('admin.faqs.trashed') }}">

        <section class="card shadow-sm border-0 mb-3" aria-labelledby="faq-nav-title">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold" id="faq-nav-title"><i class="fas fa-bars text-primary mr-2"></i>FAQ by Primary Navbar</h3>
            </div>
            <div class="card-body">
                <p class="text-muted small">Every internal primary-navbar item (including inactive/draft links) is manageable here. Inactive navigation remains hidden on the public website. Individual FAQ status is independent.</p>
                <div class="row" id="faq-nav-sections">
                    @forelse($navItems as $navItem)
                        <div class="col-xl-3 col-md-4 col-sm-6 mb-2">
                            <div class="faq-nav-card border rounded p-3 h-100 {{ $selectedNavId === $navItem->id ? 'border-primary bg-light' : '' }}">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <strong class="text-dark">{{ $navItem->label }}</strong>
                                    <span class="badge badge-info">{{ $navItem->faqs_count }} FAQs</span>
                                </div>
                                @if(! $navItem->is_active || ! $navItem->menu->is_active)
                                    <div class="small text-warning mb-2"><i class="fas fa-eye-slash mr-1"></i>Navbar inactive (not public)</div>
                                @endif
                                <div class="d-flex align-items-center flex-wrap" style="gap:6px;">
                                    <button type="button" class="btn btn-sm {{ $selectedNavId === $navItem->id ? 'btn-primary' : 'btn-outline-primary' }} btn-nav-filter" data-id="{{ $navItem->id }}">
                                        <i class="fas fa-folder-open mr-1"></i>Manage
                                    </button>
                                    @can('faq_toggle')
                                        <button type="button" class="btn btn-sm {{ $navItem->faq_section_enabled ? 'btn-outline-success' : 'btn-outline-secondary' }} btn-faq-section-toggle" data-url="{{ route('admin.faqs.menu_section_toggle', $navItem->id) }}" aria-label="Toggle FAQ section on {{ $navItem->label }}">
                                            {{ $navItem->faq_section_enabled ? 'Section ON' : 'Section OFF' }}
                                        </button>
                                    @else
                                        <span class="badge badge-{{ $navItem->faq_section_enabled ? 'success' : 'secondary' }}">{{ $navItem->faq_section_enabled ? 'Section ON' : 'Section OFF' }}</span>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><div class="alert alert-info mb-0">No internal header-primary navigation items yet. Create them in Site Configuration → Menus.</div></div>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="card shadow-sm border-0 mb-3" aria-labelledby="faq-pages-title">
            <div class="card-header bg-white">
                <h3 class="card-title font-weight-bold" id="faq-pages-title"><i class="fas fa-file-alt text-primary mr-2"></i>FAQ by CMS Pages</h3>
            </div>
            <div class="card-body">
                <p class="text-muted small">Every non-deleted CMS Page has its own FAQs, including draft and pages not in the navbar. Manage FAQs and switch each page's FAQ section on/off independently.</p>
                <div class="row" id="faq-page-sections">
                    @forelse($pageItems as $pageItem)
                        <div class="col-xl-3 col-md-4 col-sm-6 mb-2">
                            <div class="faq-page-card border rounded p-3 h-100 {{ $selectedPageId === $pageItem->id ? 'border-primary bg-light' : '' }}">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <strong class="text-dark">{{ $pageItem->title }}</strong>
                                    <span class="badge badge-info">{{ $pageItem->faqs_count }} FAQs</span>
                                </div>
                                <div class="text-muted small mb-2">/{{ $pageItem->is_homepage ? '' : $pageItem->slug }} · {{ ucfirst($pageItem->status) }}</div>
                                <div class="d-flex align-items-center flex-wrap" style="gap:6px;">
                                    <button type="button" class="btn btn-sm {{ $selectedPageId === $pageItem->id ? 'btn-primary' : 'btn-outline-primary' }} btn-page-filter" data-id="{{ $pageItem->id }}" data-label="{{ $pageItem->title }}">
                                        <i class="fas fa-folder-open mr-1"></i>Manage
                                    </button>
                                    @can('faq_toggle')
                                        <button type="button" class="btn btn-sm {{ $pageItem->faq_section_enabled ? 'btn-outline-success' : 'btn-outline-secondary' }} btn-faq-section-toggle" data-url="{{ route('admin.faqs.page_section_toggle', $pageItem->id) }}" aria-label="Toggle FAQ section on {{ $pageItem->title }}">
                                            {{ $pageItem->faq_section_enabled ? 'Section ON' : 'Section OFF' }}
                                        </button>
                                    @else
                                        <span class="badge badge-{{ $pageItem->faq_section_enabled ? 'success' : 'secondary' }}">{{ $pageItem->faq_section_enabled ? 'Section ON' : 'Section OFF' }}</span>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><div class="alert alert-info mb-0">There are no CMS pages yet. Create one in CMS Pages → Pages.</div></div>
                    @endforelse
                </div>
            </div>
        </section>

        <div class="d-flex align-items-center flex-wrap mb-3" style="gap: 6px;">
            @can('faq_create')
                <button type="button" class="btn btn-primary btn-sm font-weight-bold shadow-sm" id="btnAddRecord">
                    <i class="fas fa-plus mr-1"></i>Add New FAQ
                </button>
            @endcan

            @can('faq_trash')
                <a href="{{ route('admin.faqs.trashed') }}" class="btn btn-outline-danger btn-sm font-weight-bold shadow-sm">
                    <i class="fas fa-trash-alt mr-1"></i>Trash Bin
                </a>
            @endcan

            <select id="bulk_action" class="form-control form-control-sm shadow-none" style="width: 200px;">
                <option value="">-- Bulk Actions --</option>
                @can('faq_toggle')
                    <option value="activate">Mark as Active</option>
                    <option value="deactivate">Mark as Inactive</option>
                @endcan
                @can('faq_delete')
                    <option value="delete">Move to Trash</option>
                @endcan
            </select>

            <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 shadow-sm" id="btnApplyBulk">APPLY</button>
        </div>

        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body px-3 py-2">
                <form id="filterForm" action="{{ route('admin.faqs.index') }}" method="GET">
                    <input type="hidden" id="filter_menu_item_id" name="menu_item_id" value="{{ $selectedNavId ?: '' }}">
                    <input type="hidden" id="filter_page_id" name="page_id" value="{{ $selectedPageId ?: '' }}">
                    <div class="row align-items-end">
                        <div class="col-md-2 mb-2 mb-md-0">
                            <label for="filter_status" class="text-muted small font-weight-bold text-uppercase mb-1">Status</label>
                            <select name="status" id="filter_status" class="form-control form-control-sm shadow-none">
                                <option value="">All Statuses</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-3 mb-2 mb-md-0">
                            <label for="filter_target_type" class="text-muted small font-weight-bold text-uppercase mb-1">Assigned To</label>
                            <select name="target_type" id="filter_target_type" class="form-control form-control-sm shadow-none">
                                <option value="">All Targets</option>
                                <option value="page" {{ request('target_type') === 'page' ? 'selected' : '' }}>Pages</option>
                                <option value="service" {{ request('target_type') === 'service' ? 'selected' : '' }}>Services</option>
                                <option value="location" {{ request('target_type') === 'location' ? 'selected' : '' }}>Locations</option>
                                <option value="menu_item" {{ request('target_type') === 'menu_item' ? 'selected' : '' }}>Primary Navbar</option>
                                <option value="unassigned" {{ request('target_type') === 'unassigned' ? 'selected' : '' }}>Unassigned</option>
                            </select>
                        </div>

                        <div class="col-md-5 mb-2 mb-md-0">
                            <label for="table_search" class="text-muted small font-weight-bold text-uppercase mb-1">Search FAQs</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="search" id="table_search" class="form-control shadow-none" value="{{ request('search') }}" autocomplete="off" placeholder="Search question or answer...">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-primary" id="btnSearch" title="Search"><i class="fas fa-search"></i></button>
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

        <div id="faq-target-selection" class="alert alert-info py-2" {{ ($selectedNavId && $navItems->contains('id', $selectedNavId)) || ($selectedPageId && $pageItems->contains('id', $selectedPageId)) ? '' : 'hidden' }}>
            <strong id="faq-target-selection-label">{{ $selectedPageId ? optional($pageItems->firstWhere('id', $selectedPageId))->title : optional($navItems->firstWhere('id', $selectedNavId))->label }}</strong> FAQs selected. Only matching FAQs are shown below.
            <button type="button" id="btnClearTarget" class="btn btn-link btn-sm">Show all</button>
        </div>

        @can('faq_list')
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white px-3 py-3 border-bottom">
                    <h3 class="card-title font-weight-bold text-dark mb-0">
                        <i class="fas fa-question-circle text-primary mr-1"></i>FAQ List
                    </h3>
                </div>
                <div class="card-body p-0" id="content-wrapper" style="min-height: 340px;">
                    @include('backoffice.admin.faqs.partials.table', ['faqs' => $faqs, 'isTrash' => false])
                </div>
            </div>
        @else
            <div class="alert alert-warning border-0 shadow-sm mt-4 font-weight-bold">
                <i class="fas fa-exclamation-triangle mr-2"></i>You do not have permission to view FAQs.
            </div>
        @endcan
    </div>

    @include('backoffice.admin.faqs.partials.script')
@endsection

@section('plugins.Sweetalert2', true)
@section('plugins.Select2', true)
