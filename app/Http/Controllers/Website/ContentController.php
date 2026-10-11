<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Gallery;
use App\Models\Video;
use App\Models\Post;
use App\Models\Service;
use App\Models\PricingPackage;
use App\Services\Website\HomeData;
use Illuminate\Contracts\View\View;

/** Read-only frontend for published CMS destinations; no admin route changes. */
class ContentController extends Controller
{
    public function page(string $slug, HomeData $data): View
    {
        $page = Page::query()->published()->where('slug', $slug)->firstOrFail();
        $viewData = $data->load();
        $viewData['home'] = $page; // Reuse shared header/footer visibility settings.
        $viewData['contentPage'] = $page;
        $viewData['customPageSections'] = $page->sections()->currentlyVisible()
            ->whereHas('sectionDefinition', fn ($query) => $query->active())
            ->with(['sectionDefinition', 'mediaItems.media'])->ordered()->get()
            ->filter(fn ($section) => $section->sectionDefinition?->key === 'guarantee'
                || ! array_key_exists((string) $section->sectionDefinition?->key, \App\Models\SectionDefinition::ALLOWED_KEYS))
            ->values();
        if ($slug === 'plastering') {
            return $this->plasteringPage($page, $viewData);
        }

        $viewData['articles'] = $slug === 'blog'
            ? Post::query()->published()->ordered()->limit(24)->get()
            : collect();

        return view('website.pages.content', $viewData);
    }

    /**
     * Dedicated Plastering landing page: existing CMS Page, shared Home Blade sections,
     * page-specific images/media and only published/active domain records.
     */
    private function plasteringPage(Page $page, array $viewData): View
    {
        $pageSections = $page->sections()->currentlyVisible()
            ->whereHas('sectionDefinition', fn ($query) => $query->active())
            ->with(['sectionDefinition', 'mediaItems.media'])->ordered()->get();

        // HomeData already loaded the published galleries displayed on Home.
        // Keep them available for Plastering's shared Before/After component
        // before the Plastering-only gallery query replaces 'galleries'.
        $viewData['sharedHomeGalleries'] = $viewData['galleries'] ?? collect();

        $plastering = Service::query()->published()->where('slug', 'wall-plastering')->first();
        $serviceId = $plastering?->id;
        $serviceMorph = (new Service())->getMorphClass();
        $pageMorph = $page->getMorphClass();

        // Do not mix unrelated painting galleries into the plastering page.
        $viewData['galleries'] = Gallery::query()->where('is_active', true)
            ->where(function ($query) use ($page, $pageMorph, $serviceId, $serviceMorph) {
                $query->where(fn ($q) => $q->whereNull('attachable_type')->where('name', 'like', '%plaster%'))
                    ->orWhere(fn ($q) => $q->where('attachable_type', $pageMorph)->where('attachable_id', $page->id));
                if ($serviceId) {
                    $query->orWhere(fn ($q) => $q->where('attachable_type', $serviceMorph)->where('attachable_id', $serviceId));
                }
            })
            ->with(['items' => fn ($query) => $query->where('is_active', true)->with('media')->orderBy('sort_order')])
            ->orderBy('id')->limit(20)->get();

        // Shared/unattached project videos may be shown, along with those linked
        // specifically to the plastering Page/Service. Exclude unrelated services.
        $viewData['videos'] = Video::query()->active()
            ->where(function ($query) use ($page, $pageMorph, $serviceId, $serviceMorph) {
                $query->whereNull('attachable_type')
                    ->orWhere(fn ($q) => $q->where('attachable_type', $pageMorph)->where('attachable_id', $page->id));
                if ($serviceId) {
                    $query->orWhere(fn ($q) => $q->where('attachable_type', $serviceMorph)->where('attachable_id', $serviceId));
                }
            })->ordered()->limit(20)->get()
            ->filter(fn (Video $video) => $video->has_playable_source
                && ($video->resolved_source_type === 'upload'
                    || in_array(strtolower((string) parse_url((string) $video->playback_url, PHP_URL_HOST)),
                        ['www.youtube.com', 'www.youtube-nocookie.com', 'youtube-nocookie.com', 'player.vimeo.com'], true)))
            ->values();

        // First use Plastering Page FAQs; preserve the existing navbar FAQ resolver
        // when there are no explicitly linked page FAQs.
        $pageFaqs = $page->faq_section_enabled ? $page->faqs()->active()->get() : collect();
        $viewData['faqs'] = ! $page->faq_section_enabled ? collect()
            : ($pageFaqs->isNotEmpty() ? $pageFaqs : collect($viewData['faqs'] ?? []));

        // Query the Admin Pricing Packages source directly. HomeData::load() has a
        // homepage-specific whereHas('items') constraint and a global row limit:
        // either can silently discard ACTIVE, FEATURED global packages before this
        // page gets to apply its service filter. A package's publication status is
        // independent of whether a pricing item has been entered/published yet.
        //
        // Only global or Wall Plastering-linked packages belong on this page.
        // Never show draft/reference competitor entries or unrelated services.
        $viewData['plasterPackages'] = PricingPackage::query()
            ->active()
            ->where('name', 'not like', '[Draft]%')
            ->where('name', 'not like', 'Reference ·%')
            ->where(function ($query) use ($serviceId) {
                $query->whereNull('service_id');
                if ($serviceId !== null) {
                    $query->orWhere('service_id', $serviceId);
                }
            })
            ->where(function ($query) {
                $query->whereNull('location_id')
                    ->orWhereHas('location', fn ($location) => $location->published());
            })
            ->with(['items' => fn ($items) => $items->active()->ordered()])
            ->orderByDesc('is_featured')
            ->ordered()
            ->limit(20)
            ->get();

        $viewData['plasterSections'] = $pageSections;
        $viewData['plasterService'] = $plastering;
        $viewData['home'] = $page; // Shared layout header/footer visibility and metadata.

        return view('website.pages.plastering', $viewData);
    }

    public function service(string $slug, HomeData $data): View
    {
        $service = Service::query()->published()->where('slug', $slug)->firstOrFail();
        $viewData = $data->load();
        $viewData['contentPage'] = $service;
        $viewData['faqs'] = $service->faqs()->active()->get();
        $viewData['articles'] = collect();

        return view('website.pages.content', $viewData);
    }

    public function article(string $slug, HomeData $data): View
    {
        $article = Post::query()->published()->where('slug', $slug)->firstOrFail();
        $viewData = $data->load();
        $viewData['contentPage'] = $article;

        return view('website.pages.article', $viewData);
    }
}
