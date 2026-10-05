@php
    $isTrash = $isTrash ?? false;
@endphp

<div class="table-responsive table-responsive-custom">
    <table class="table table-hover border-bottom faq-table mb-0 text-nowrap">
        <thead class="thead-light">
        <tr>
            <th style="width: 40px;" class="text-center align-middle"><input type="checkbox" id="checkAll"></th>
            <th class="align-middle">Question</th>
            <th style="width: 230px;" class="align-middle">Mappings</th>
            <th style="width: 100px;" class="text-center align-middle">Status</th>
            <th style="width: 145px;" class="text-center align-middle">{{ $isTrash ? 'Deleted' : 'Updated' }}</th>
            <th style="width: 190px;" class="text-center align-middle">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($faqs as $faq)
            @php
                $questionText = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags((string) $faq->question), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
                $mappingCount = (int) ($faq->pages_count ?? 0) + (int) ($faq->services_count ?? 0) + (int) ($faq->locations_count ?? 0);
            @endphp
            <tr>
                <td data-label="Select" class="text-center align-middle">
                    <input type="checkbox" class="row-checkbox" value="{{ $faq->id }}">
                </td>
                <td data-label="Question" class="align-middle">
                    <div class="font-weight-bold text-dark text-wrap" style="max-width: 560px;">
                        {{ \Illuminate\Support\Str::limit($questionText, 110) }}
                    </div>
                    <small class="text-muted">FAQ #{{ $faq->id }}</small>
                </td>
                <td data-label="Mappings" class="align-middle">
                    @if($mappingCount > 0)
                        @if(($faq->pages_count ?? 0) > 0)<span class="badge badge-primary mr-1">Pages {{ $faq->pages_count }}</span>@endif
                        @if(($faq->services_count ?? 0) > 0)<span class="badge badge-info mr-1">Services {{ $faq->services_count }}</span>@endif
                        @if(($faq->locations_count ?? 0) > 0)<span class="badge badge-warning mr-1">Locations {{ $faq->locations_count }}</span>@endif
                    @else
                        <span class="text-muted small">Global / unassigned</span>
                    @endif
                </td>
                <td data-label="Status" class="text-center align-middle">
                    <span class="badge badge-{{ $faq->is_active ? 'success' : 'secondary' }} px-2 py-1">
                        {{ $faq->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </td>
                <td data-label="{{ $isTrash ? 'Deleted' : 'Updated' }}" class="text-center align-middle text-muted small">
                    {{ optional($isTrash ? $faq->deleted_at : $faq->updated_at)->format('d M Y') }}
                    <div>{{ optional($isTrash ? $faq->deleted_at : $faq->updated_at)->format('h:i A') }}</div>
                </td>
                <td data-label="Actions" class="text-center align-middle action-cell">
                    @if($isTrash)
                        @can('faq_restore')
                            <button type="button" class="btn btn-sm btn-outline-success btn-restore shadow-sm mx-1" data-url="{{ route('admin.faqs.restore', $faq->id) }}" title="Restore"><i class="fas fa-undo"></i></button>
                        @endcan
                        @can('faq_force_delete')
                            <button type="button" class="btn btn-sm btn-outline-danger btn-force-delete shadow-sm mx-1" data-url="{{ route('admin.faqs.force_delete', $faq->id) }}" title="Permanent Delete"><i class="fas fa-trash-alt"></i></button>
                        @endcan
                    @else
                        @can('faq_view')
                            <button type="button" class="btn btn-sm btn-outline-info btn-show shadow-sm mx-1" data-url="{{ route('admin.faqs.show', $faq->id) }}" title="View"><i class="fas fa-eye"></i></button>
                        @endcan
                        @can('faq_update')
                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit shadow-sm mx-1" data-url="{{ route('admin.faqs.edit', $faq->id) }}" title="Edit"><i class="fas fa-pen"></i></button>
                        @endcan
                        @can('faq_toggle')
                            <button type="button" class="btn btn-sm btn-outline-{{ $faq->is_active ? 'secondary' : 'success' }} btn-toggle shadow-sm mx-1" data-url="{{ route('admin.faqs.toggle', $faq->id) }}" title="{{ $faq->is_active ? 'Deactivate' : 'Activate' }}"><i class="fas fa-power-off"></i></button>
                        @endcan
                        @can('faq_delete')
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete shadow-sm mx-1" data-url="{{ route('admin.faqs.destroy', $faq->id) }}" title="Trash"><i class="fas fa-trash"></i></button>
                        @endcan
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-question-circle fa-3x mb-3 text-light"></i>
                        <h5 class="font-weight-bold">No FAQs Found</h5>
                        <p class="mb-0 small">No data available in the table.</p>
                    </div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($faqs->hasPages())
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-3 border-top bg-light">
        <div class="text-muted small font-weight-bold mb-2 mb-md-0">
            Showing {{ $faqs->firstItem() ?? 0 }} to {{ $faqs->lastItem() ?? 0 }} of {{ $faqs->total() }} entries
        </div>
        <div class="m-0 pagination-sm">{!! $faqs->appends(request()->query())->links('pagination::bootstrap-4') !!}</div>
    </div>
@endif

<style>
    @media (max-width: 767.98px) {
        .table-responsive-custom { border: none !important; }
        .faq-table, .faq-table tbody, .faq-table tr, .faq-table td { display: block; width: 100%; }
        .faq-table thead { display: none; }
        .faq-table tr { margin-bottom: 1rem; border: 1px solid #e3e6f0 !important; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,.05); background: #fff; overflow: hidden; }
        .faq-table td { display: flex !important; justify-content: space-between; align-items: center; border: none !important; border-bottom: 1px solid #f1f5f9 !important; padding: 12px 15px !important; text-align: right; white-space: normal; }
        .faq-table td::before { content: attr(data-label); font-weight: 700; font-size: 12px; text-transform: uppercase; color: #858796; margin-right: 20px; text-align: left; }
        .faq-table td.action-cell { justify-content: center; background: #f8f9fc; padding: 15px !important; }
        .faq-table td.action-cell::before { display: none; }
    }
</style>
