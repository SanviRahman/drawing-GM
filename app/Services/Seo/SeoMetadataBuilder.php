<?php

namespace App\Services\Seo;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SeoMetadataBuilder
{
    public function for(Model $model, array $defaults = []): array
    {
        $seo = $model->relationLoaded('seoMeta')
            ? $model->getRelation('seoMeta')
            : (method_exists($model, 'seoMeta') ? $model->seoMeta()->first() : null);

        if (! $seo instanceof SeoMeta) {
            return $defaults;
        }

        $metaDescription = $this->plainText($seo->meta_description);
        $ogDescription = $this->plainText($seo->og_description);

        return array_filter([
            'title' => $seo->meta_title ?: ($defaults['title'] ?? null),
            'description' => $metaDescription ?: ($defaults['description'] ?? null),
            'canonical_url' => $seo->canonical_url ?: ($defaults['canonical_url'] ?? null),
            'robots' => $seo->robots ?: ($defaults['robots'] ?? 'index,follow'),
            'og_title' => $seo->og_title ?: $seo->meta_title ?: ($defaults['og_title'] ?? null),
            'og_description' => $ogDescription ?: $metaDescription ?: ($defaults['og_description'] ?? null),
            'social_image_url' => $seo->social_image_url ?: ($defaults['social_image_url'] ?? null),
            'schema_overrides' => $seo->schema_overrides,
            'include_in_sitemap' => $seo->include_in_sitemap,
            'sitemap_priority' => $seo->sitemap_priority,
            'sitemap_changefreq' => $seo->sitemap_changefreq,
        ], static fn ($value) => $value !== null);
    }

    private function plainText(?string $html): ?string
    {
        $value = Str::squish(html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        return $value !== '' ? $value : null;
    }
}
