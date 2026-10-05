@php
    $isTrashMode = isset($isTrash) && (bool) $isTrash;
@endphp

<div class="table-responsive">
    <table class="table table-hover mb-0 text-nowrap">
        <thead class="thead-light">
            <tr>
                <th class="text-center" style="width:40px"><input type="checkbox" id="checkAll"></th>
                <th>Content</th>
                <th>Meta Title</th>
                <th>Robots</th>
                <th class="text-center">Sitemap</th>
                <th>Social</th>
                <th class="text-center">Updated</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($seoMetas as $seoMeta)
            @php
            $typeKey = $seoMeta->typeKey();
            $typeLabel = $typeKey ? (\App\Models\SeoMeta::SEOABLE_LABELS[$typeKey] ?? $typeKey) :
            class_basename($seoMeta->seoable_type);
            @endphp
            <tr>
                <td class="text-center align-middle"><input type="checkbox" class="row-checkbox"
                        value="{{ $seoMeta->id }}"></td>
                <td class="align-middle">
                    <div class="font-weight-bold">{{ $seoMeta->ownerLabel() }}</div><small
                        class="text-muted">{{ $typeLabel }} #{{ $seoMeta->seoable_id }}</small>
                </td>
                <td class="align-middle">
                    <div>{{ \Illuminate\Support\Str::limit($seoMeta->meta_title ?: '—', 60) }}</div><small
                        class="text-muted">{{ \Illuminate\Support\Str::limit($seoMeta->canonical_url ?: '', 55) }}</small>
                </td>
                <td class="align-middle"><code>{{ $seoMeta->robots }}</code></td>
                <td class="text-center align-middle"><span
                        class="badge badge-{{ $seoMeta->include_in_sitemap ? 'success':'secondary' }}">{{ $seoMeta->include_in_sitemap ? 'Yes':'No' }}</span>
                </td>
                <td class="align-middle">@if($seoMeta->social_image_url)<img src="{{ $seoMeta->social_image_url }}"
                        class="seo-social-thumb border" alt="Social">@else<span class="text-muted">—</span>@endif</td>
                <td class="text-center align-middle small text-muted">
                    {{ $seoMeta->updated_at?->format('d M Y H:i') ?? '—' }}</td>
                <td class="text-center align-middle">
                    @if($isTrashMode)
                    @can('seo_meta_restore')<button class="btn btn-sm btn-outline-success btn-restore"
                        data-url="{{ route('admin.seo.restore',$seoMeta->id) }}" title="Restore"><i
                            class="fas fa-undo"></i></button>@endcan
                    @can('seo_meta_force_delete')<button class="btn btn-sm btn-outline-danger btn-force-delete"
                        data-url="{{ route('admin.seo.force_delete',$seoMeta->id) }}" title="Delete permanently"><i
                            class="fas fa-trash"></i></button>@endcan
                    @else
                    @can('seo_meta_view')<button class="btn btn-sm btn-outline-info btn-show"
                        data-url="{{ route('admin.seo.show',$seoMeta->id) }}" title="View"><i
                            class="fas fa-eye"></i></button>@endcan
                    @can('seo_meta_update')<button class="btn btn-sm btn-outline-primary btn-edit"
                        data-url="{{ route('admin.seo.edit',$seoMeta->id) }}" title="Edit"><i
                            class="fas fa-pen"></i></button>@endcan
                    @can('seo_meta_delete')<button class="btn btn-sm btn-outline-danger btn-delete"
                        data-url="{{ route('admin.seo.destroy',$seoMeta->id) }}" title="Trash"><i
                            class="fas fa-trash"></i></button>@endcan
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center text-muted py-5"><i class="fas fa-search fa-2x mb-2 d-block"></i>No
                    SEO metadata found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@if($seoMetas->hasPages())<div class="p-3">{{ $seoMetas->links() }}</div>@endif