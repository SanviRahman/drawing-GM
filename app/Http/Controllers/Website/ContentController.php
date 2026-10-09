<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Post;
use App\Models\Service;
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
        $viewData['articles'] = $slug === 'blog'
            ? Post::query()->published()->ordered()->limit(24)->get()
            : collect();

        return view('website.pages.content', $viewData);
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
