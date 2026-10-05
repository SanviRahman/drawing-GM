@php($typeKey = $seoMeta->typeKey())
<div class="row">
<div class="col-md-6 mb-3"><small class="text-muted d-block">Content</small><strong>{{ $seoMeta->ownerLabel() }}</strong><div>{{ $typeKey ? (\App\Models\SeoMeta::SEOABLE_LABELS[$typeKey] ?? $typeKey) : class_basename($seoMeta->seoable_type) }} #{{ $seoMeta->seoable_id }}</div></div>
<div class="col-md-6 mb-3"><small class="text-muted d-block">Robots</small><code>{{ $seoMeta->robots }}</code></div>
<div class="col-md-12 mb-3"><small class="text-muted d-block">Meta Title</small>{{ $seoMeta->meta_title ?: '—' }}</div>
<div class="col-md-12 mb-3"><small class="text-muted d-block">Meta Description</small><div class="border rounded p-2 bg-light">{!! $seoMeta->meta_description ?: '—' !!}</div></div>
<div class="col-md-12 mb-3"><small class="text-muted d-block">Canonical URL</small>{{ $seoMeta->canonical_url ?: '—' }}</div>
<div class="col-md-12 mb-3"><small class="text-muted d-block">OG Title / Description</small><strong>{{ $seoMeta->og_title ?: '—' }}</strong><div class="border rounded p-2 bg-light mt-1">{!! $seoMeta->og_description ?: '—' !!}</div></div>
<div class="col-md-4 mb-3"><small class="text-muted d-block">Sitemap</small>{{ $seoMeta->include_in_sitemap ? 'Included':'Excluded' }}</div>
<div class="col-md-4 mb-3"><small class="text-muted d-block">Priority</small>{{ $seoMeta->sitemap_priority ?? '—' }}</div>
<div class="col-md-4 mb-3"><small class="text-muted d-block">Changefreq</small>{{ $seoMeta->sitemap_changefreq ?? '—' }}</div>
<div class="col-md-12 mb-3"><small class="text-muted d-block">Social Image</small>@if($seoMeta->social_image_url)<img src="{{ $seoMeta->social_image_url }}" class="img-fluid rounded border mt-1" style="max-height:220px" alt="Social image">@else — @endif</div>
<div class="col-md-12"><small class="text-muted d-block">Schema Overrides</small><pre class="bg-light border rounded p-3 mb-0">{{ $seoMeta->schema_overrides ? json_encode($seoMeta->schema_overrides,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) : '—' }}</pre></div>
</div>
