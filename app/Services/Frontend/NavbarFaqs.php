<?php

namespace App\Services\Frontend;

use App\Models\Faq;
use App\Models\Location;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

/** The public FAQ section is keyed to the CMS primary-navigation item, never to its label. */
class NavbarFaqs
{
    public function navigationItems(bool $includeInactive = false): Collection
    {
        return MenuItem::query()
            ->whereHas('menu', fn ($q) => $q->where('location', 'header-primary')
                ->when(! $includeInactive, fn ($active) => $active->where('is_active', true)))
            ->when(! $includeInactive, fn ($active) => $active->where('is_active', true))
            ->where('link_type', '!=', 'text')
            ->with('menu')
            ->withCount('faqs')
            ->orderBy('sort_order')->orderBy('id')->get()
            ->filter(fn (MenuItem $item) => $this->isInternal($item))
            ->values();
    }

    /** Inactive/draft CMS pages remain editable in admin but cannot publish FAQs. */
    public function forPage(Page $page): Collection
    {
        if ($page->status !== 'published' || ! $page->faq_section_enabled
            || ! $page->published_at || $page->published_at->isFuture()) {
            return new Collection();
        }

        return $page->faqs()->active()->get();
    }

    /** Resolve an existing published page for routes like /, /about, /pages/about. */
    public function forPagePath(string $path): ?Page
    {
        $path = $this->normalizePath($path);

        if ($path === '/') {
            return Page::query()->published()->homepage()->first();
        }

        $slug = ltrim(str_starts_with($path, '/pages/') ? substr($path, 7) : $path, '/');

        if ($slug === '' || str_contains($slug, '/')) {
            return null;
        }

        return Page::query()->published()->where('slug', $slug)->first();
    }

    /** @return Collection<int, Faq> */
    public function forMenuItem(MenuItem $item): Collection
    {
        if (! $item->is_active || ! $item->faq_section_enabled || ! $this->isInternal($item)
            || ! $item->menu()->where('location', 'header-primary')->where('is_active', true)->exists()) {
            return new Collection();
        }

        return $item->faqs()->active()->get();
    }

    /**
     * Resolve custom internal menu URLs, or supported typed CMS destinations.
     * Explicit menuItemId should be preferred when a frontend route aliases its canonical URL.
     */
    public function forRequest(Request $request, ?int $menuItemId = null, ?int $pageId = null): ?array
    {
        if ($pageId !== null) {
            $page = Page::query()->find($pageId);
            if (! $page) {
                return null;
            }
            $faqs = $this->forPage($page);
            return $faqs->isEmpty() ? null : ['page' => $page, 'faqs' => $faqs];
        }

        if ($menuItemId !== null) {
            $item = $this->navigationItems()->firstWhere('id', $menuItemId);
        } else {
            $path = $this->normalizePath($request->getPathInfo());
            $item = $this->navigationItems()->first(function (MenuItem $candidate) use ($path): bool {
                return $this->itemPath($candidate) === $path;
            });
        }

        if ($item) {
            // A switched-off navbar FAQ section must not be bypassed by URL fallback.
            if (! $item->faq_section_enabled) {
                return null;
            }
            $faqs = $this->forMenuItem($item);
            // An explicit navbar destination must respect its section OFF state.
            if ($menuItemId !== null || ! $faqs->isEmpty()) {
                return $faqs->isEmpty() ? null : ['menuItem' => $item, 'faqs' => $faqs];
            }
        }

        $page = $this->forPagePath($request->getPathInfo());
        if (! $page) {
            return null;
        }
        $faqs = $this->forPage($page);
        return $faqs->isEmpty() ? null : ['page' => $page, 'faqs' => $faqs];
    }

    private function isInternal(MenuItem $item): bool
    {
        if ($item->target === '_blank') {
            return false;
        }

        if ($item->link_type === 'linkable') {
            return in_array($item->linkable_type, [Page::class, Service::class, Location::class], true);
        }

        if ($item->link_type !== 'url') {
            return false;
        }

        $url = trim((string) $item->url);
        return str_starts_with($url, '/') && ! str_starts_with($url, '//');
    }

    private function itemPath(MenuItem $item): ?string
    {
        if (! $this->isInternal($item)) {
            return null;
        }

        if ($item->link_type === 'url') {
            return $this->normalizePath(parse_url((string) $item->url, PHP_URL_PATH) ?: '/');
        }

        $linked = $item->linkable;
        if ($linked instanceof Page && $linked->status === 'published') {
            return $linked->is_homepage ? '/' : '/pages/'. $linked->slug;
        }
        if ($linked instanceof Service && $linked->status === 'published') {
            return '/services/'.$linked->slug;
        }
        if ($linked instanceof Location && $linked->status === 'published') {
            return '/locations/'.$linked->slug;
        }

        return null;
    }

    private function normalizePath(string $path): string
    {
        $path = '/'.trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }
}
