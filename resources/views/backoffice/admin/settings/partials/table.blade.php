<div class="table-responsive table-responsive-custom">
    <table class="table table-hover border-bottom setting-table mb-0 text-nowrap">
        <thead class="thead-light">
        <tr>
            <th style="width: 40px;" class="text-center align-middle">
                <input type="checkbox" id="checkAll">
            </th>
            <th style="width: 76px;" class="text-center align-middle">Preview</th>
            <th class="align-middle">Setting Key</th>
            <th class="align-middle">Value</th>
            <th style="width: 110px;" class="text-center align-middle">Type</th>
            <th style="width: 100px;" class="text-center align-middle">Public</th>
            <th style="width: 130px;" class="align-middle">Updated</th>
            <th style="width: 150px;" class="text-center align-middle">Actions</th>
        </tr>
        </thead>

        <tbody>
        @forelse($settings as $setting)
            @php
                $previewMedia = $setting->media->first(fn ($media) => in_array($media->collection_name, \App\Models\SiteSetting::MEDIA_COLLECTIONS, true));
                $previewUrl = null;
                if ($previewMedia) {
                    try {
                        $previewUrl = $previewMedia->getUrl();
                    } catch (\Throwable) {
                        $previewUrl = null;
                    }
                }
            @endphp
            <tr>
                <td data-label="Select" class="text-center align-middle">
                    <input type="checkbox" class="row-checkbox" value="{{ $setting->id }}">
                </td>

                <td data-label="Preview" class="text-center align-middle">
                    <div class="border rounded bg-light d-inline-flex align-items-center justify-content-center overflow-hidden" style="width:52px;height:52px;">@if($previewUrl)<img src="{{ $previewUrl }}" alt="{{ $setting->setting_key }}" style="width:100%;height:100%;object-fit:contain;" loading="lazy">@else<i class="far fa-image text-muted"></i>@endif</div>
                </td>

                <td data-label="Setting Key" class="align-middle">
                    <span class="font-weight-bold text-dark">
                        <i class="fas fa-sliders-h text-primary mr-1"></i>{{ $setting->setting_key }}
                    </span>
                    <br>
                    <span class="badge badge-light px-2 py-1 text-secondary">
                        <i class="fas fa-folder mr-1"></i>{{ $setting->group_name }}
                    </span>
                </td>

                <td data-label="Value" class="align-middle text-muted">
                    {{ $setting->presentValue(60) }}
                </td>

                <td data-label="Type" class="text-center align-middle">
                    <span class="badge badge-{{ match($setting->value_type) {
                        'boolean' => 'info',
                        'integer' => 'success',
                        'json' => 'warning',
                        'encrypted' => 'dark',
                        default => 'secondary',
                    } }} px-2 py-1 text-uppercase">{{ $setting->value_type }}</span>
                </td>

                <td data-label="Public" class="text-center align-middle">
                    @if($setting->is_public)
                        <span class="badge badge-success px-2 py-1 shadow-sm">Yes</span>
                    @else
                        <span class="badge badge-secondary px-2 py-1 shadow-sm">No</span>
                    @endif
                </td>

                <td data-label="Updated" class="align-middle text-muted small">
                    {{ $setting->updated_at?->format('d M, Y h:i A') ?? '—' }}
                </td>

                <td data-label="Actions" class="text-center align-middle action-cell">
                    @if($isTrash ?? false)
                        @can('site_setting_restore')
                            <button type="button" class="btn btn-sm btn-outline-success btn-restore shadow-sm mx-1" data-url="{{ route('admin.settings.restore', $setting->id) }}" title="Restore"><i class="fas fa-undo"></i></button>
                        @endcan
                        @can('site_setting_force_delete')
                            <button type="button" class="btn btn-sm btn-outline-danger btn-force-delete shadow-sm mx-1" data-url="{{ route('admin.settings.force_delete', $setting->id) }}" title="Permanent Delete"><i class="fas fa-trash-alt"></i></button>
                        @endcan
                    @else
                        @can('site_setting_view')
                            <button type="button" class="btn btn-sm btn-outline-info btn-show shadow-sm mx-1" data-url="{{ route('admin.settings.show', $setting->id) }}" title="View"><i class="fas fa-eye"></i></button>
                        @endcan
                        @can('site_setting_update')
                            <button type="button" class="btn btn-sm btn-outline-primary btn-edit shadow-sm mx-1" data-url="{{ route('admin.settings.edit', $setting->id) }}" title="Edit"><i class="fas fa-pen"></i></button>
                        @endcan
                        @can('site_setting_delete')
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete shadow-sm mx-1" data-url="{{ route('admin.settings.destroy', $setting->id) }}" title="Trash"><i class="fas fa-trash"></i></button>
                        @endcan
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="text-center py-5">
                    <div class="text-muted">
                        <i class="fas fa-cogs fa-3x mb-3 text-light"></i>
                        <h5 class="font-weight-bold">No Settings Found</h5>
                        <p class="mb-0 small">No data available in the table.</p>
                    </div>
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Pagination --}}
@if($settings->hasPages())
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center px-3 py-3 border-top bg-light">
        <div class="text-muted small font-weight-bold mb-2 mb-md-0">
            Showing {{ $settings->firstItem() ?? 0 }} to {{ $settings->lastItem() ?? 0 }} of {{ $settings->total() }} entries
        </div>
        <div class="m-0 pagination-sm">
            {!! $settings->appends(request()->query())->links('pagination::bootstrap-4') !!}
        </div>
    </div>
@endif

<style>
    @media (max-width: 767.98px) {
        .table-responsive-custom { border: none !important; }
        .setting-table, .setting-table tbody, .setting-table tr, .setting-table td { display: block; width: 100%; }
        .setting-table thead { display: none; }
        .setting-table tr {
            margin-bottom: 1rem;
            border: 1px solid #e3e6f0 !important;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            background-color: #fff;
            overflow: hidden;
        }
        .setting-table td {
            display: flex !important;
            justify-content: space-between;
            align-items: center;
            border: none !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 12px 15px !important;
            text-align: right;
        }
        .setting-table td:last-child { border-bottom: none !important; }
        .setting-table td::before {
            content: attr(data-label);
            font-weight: 700;
            font-size: 12px;
            text-transform: uppercase;
            color: #858796;
            margin-right: auto;
            text-align: left;
        }
        .setting-table td.action-cell {
            justify-content: center;
            background-color: #f8f9fc;
            padding: 15px !important;
        }
        .setting-table td.action-cell::before { display: none; }
    }
</style>
