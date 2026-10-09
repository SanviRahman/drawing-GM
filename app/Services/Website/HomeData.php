<?php

namespace App\Services\Website;

use App\Models\ContactChannel;
use App\Models\Gallery;
use App\Models\Location;
use App\Models\LeadFormField;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\PricingPackage;
use App\Models\PricingAddon;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use App\Models\Video;
use App\Services\Frontend\NavbarFaqs;
use Illuminate\Support\Collection;

class HomeData
{
    public function __construct(private readonly NavbarFaqs $faqResolver) {}

    public function load(): array
    {
        $page = Page::query()->published()->homepage()->first();
        $settings = SiteSetting::query()->public()->get()->keyBy('setting_key');
        $menuRows = Menu::query()->active()->whereIn('location', Menu::LOCATIONS)
            ->with(['items' => fn ($query) => $query->active()->with('linkable')->ordered()])
            ->get()->keyBy('location');

        $menus = [];
        foreach (Menu::LOCATIONS as $location) {
            $items = $menuRows->get($location)?->items ?? collect();
            $menus[$location] = $this->menuTree($items);
        }

        $sections = $page
            ? $page->sections()->currentlyVisible()
                ->whereHas('sectionDefinition', fn ($query) => $query->active())
                ->with(['sectionDefinition', 'mediaItems.media'])->ordered()->get()
            : collect();

        $services = Service::query()->published()->featured()->ordered()->limit(6)->get();
        if ($services->isEmpty()) {
            $services = Service::query()->published()->ordered()->limit(6)->get();
        }

        $packages = PricingPackage::query()->active()
            ->where('name', 'not like', '[Draft]%')
            ->where('name', 'not like', 'Reference ·%')
            ->whereHas('items', fn ($q) => $q->active())
            ->where(function ($q) {
                $q->whereNull('service_id')->orWhereHas('service', fn ($s) => $s->published());
            })
            ->where(function ($q) {
                $q->whereNull('location_id')->orWhereHas('location', fn ($l) => $l->published());
            })
            ->with([
                'items' => fn ($query) => $query->active()->ordered(),
                'pricingAddons' => fn ($query) => $query->active()->with('media')->ordered(),
            ])
            ->orderByDesc('is_featured')->ordered()->limit(30)->get();

        // Home also showcases published service portfolios. The old query excluded
        // ALL service-attached galleries, so approved before/after media never appeared.
        $publishedServiceIds = Service::query()->published()->pluck('id')->all();
        $publishedLocationIds = Location::query()->published()->pluck('id')->all();
        $galleries = Gallery::query()->where('is_active', true)
            ->where(function ($q) use ($page, $publishedServiceIds, $publishedLocationIds) {
                $q->whereNull('attachable_type');
                if ($page) {
                    $q->orWhere(fn ($p) => $p
                        ->where('attachable_type', $page->getMorphClass())
                        ->where('attachable_id', $page->id));
                }
                if ($publishedServiceIds !== []) {
                    $q->orWhere(fn ($p) => $p
                        ->where('attachable_type', (new Service())->getMorphClass())
                        ->whereIn('attachable_id', $publishedServiceIds));
                }
                if ($publishedLocationIds !== []) {
                    $q->orWhere(fn ($p) => $p
                        ->where('attachable_type', (new Location())->getMorphClass())
                        ->whereIn('attachable_id', $publishedLocationIds));
                }
            })
            ->with(['items' => fn ($query) => $query->where('is_active', true)
                ->with('media')->orderBy('sort_order')])
            ->orderBy('id')->limit(24)->get();

        $videos = Video::query()->active()
            ->where(function ($q) use ($page, $publishedServiceIds) {
                $q->whereNull('attachable_type');
                if ($page) {
                    $q->orWhere(fn ($p) => $p->where('attachable_type', $page->getMorphClass())
                        ->where('attachable_id', $page->id));
                }
                if ($publishedServiceIds !== []) {
                    $q->orWhere(fn ($p) => $p->where('attachable_type', (new Service())->getMorphClass())
                        ->whereIn('attachable_id', $publishedServiceIds));
                }
            })
            ->ordered()->limit(16)->get()
            ->filter(function (Video $video): bool {
                if (! $video->has_playable_source) return false;
                if ($video->resolved_source_type === 'upload') return true;
                $host = strtolower((string) parse_url((string) $video->playback_url, PHP_URL_HOST));
                return in_array($host, ['www.youtube.com', 'www.youtube-nocookie.com', 'youtube-nocookie.com', 'player.vimeo.com'], true);
            })
            ->values();

        $faqGroup = $this->faqResolver->forRequest(request());
        $faqs = $faqGroup['faqs'] ?? collect();
        // Respect an explicit navbar FAQ OFF setting; never merge both groups as duplicates.

        return [
            'home' => $page,
            'settings' => $settings->map(fn ($setting) => $setting->value_type === 'encrypted' ? null : $setting->resolveValue()),
            'logo' => $this->siteLogo(),
            'menus' => $menus,
            'sections' => $sections,
            'services' => $services,
            'packages' => $packages,
            // Every active add-on saved in Admin is available to the calculator.
            // Package mappings can still override its price fields; inactive add-ons stay hidden.
            'pricingAddons' => PricingAddon::query()->active()->with('media')->ordered()->limit(100)->get(),
            'galleries' => $galleries,
            'videos' => $videos,
            'faqs' => $faqs,
            'testimonials' => Testimonial::query()->active()->with('media')->where('customer_name', 'not like', 'Demo Customer%')->orderBy('sort_order')->orderBy('id')->limit(48)->get(),
            'contacts' => ContactChannel::query()->active()->ordered()->get(),
            'fields' => LeadFormField::query()->active()->ordered()->get(),
        ];
    }

    private function siteLogo(): ?string
    {
        $setting = SiteSetting::query()->public()->whereHas('media', fn ($q) => $q->where('collection_name', 'site_logo'))->first();
        return $setting?->getFirstMediaUrl('site_logo') ?: null;
    }

    private function menuTree(Collection $items): Collection
    {
        $visible = $items->filter(fn ($item) => $this->urlFor($item) !== null);
        $byParent = $visible->groupBy(fn ($item) => (int) ($item->parent_id ?? 0));
        $build = function (int $parentId, int $level = 0) use (&$build, $byParent): Collection {
            if ($level > 5) return collect();
            return ($byParent->get($parentId) ?? collect())->map(function ($item) use ($build, $level) {
                return (object) [
                    'label' => $item->label,
                    'url' => $this->urlFor($item),
                    'target' => $item->target === '_blank' ? '_blank' : '_self',
                    'children' => $build((int) $item->id, $level + 1),
                ];
            })->values();
        };
        return $build(0);
    }

    public function urlFor(MenuItem $item): ?string
    {
        if ($item->link_type === 'text') return null;
        if ($item->link_type === 'linkable') {
            $linked = $item->linkable;
            if ($linked instanceof Page && $linked->status === 'published' && $linked->published_at?->isPast()) {
                return $linked->is_homepage ? url('/') : url('/'.$linked->slug);
            }
            if ($linked instanceof Service && $linked->status === 'published' && $linked->published_at?->isPast()) {
                return url('/services/'.$linked->slug);
            }
            return null; // Do not link unpublished/unsupported destinations.
        }
        $uri = trim((string) $item->url);
        if ($uri === '' || preg_match('~^(javascript|data|vbscript):~i', $uri)) return null;
        if (str_starts_with($uri, '/') && ! str_starts_with($uri, '//')) return url($uri);
        if (preg_match('~^https?://~i', $uri) && filter_var($uri, FILTER_VALIDATE_URL)) return $uri;
        if (preg_match('/^(tel:|mailto:)/i', $uri)) return $uri;
        return null;
    }
}
