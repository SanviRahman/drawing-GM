<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\PricingPackage;
use App\Models\Service;
use App\Services\Website\HomeData;
use App\Services\Website\HomeMediaSlides;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

/** Dedicated public Pricing and Services pages; ContentController stays untouched. */
class PricingServicesController extends Controller
{
    public function pricing(HomeData $homeData, HomeMediaSlides $slides): View
    {
        $page = Page::query()->published()->where('slug', 'pricing')->first();
        $data = $this->shared($homeData, $slides, $page);

        // A pricing page must not silently lose items after HomeData's homepage limit.
        // Only active, approved packages and items for published services/locations.
        $data['packages'] = PricingPackage::query()->active()
            ->where('name', 'not like', '[Draft]%')
            ->where('name', 'not like', 'Reference ·%')
            ->whereHas('items', fn ($query) => $query->active())
            ->where(function ($query) {
                $query->whereNull('service_id')
                    ->orWhereHas('service', fn ($service) => $service->published());
            })
            ->where(function ($query) {
                $query->whereNull('location_id')
                    ->orWhereHas('location', fn ($location) => $location->published());
            })
            ->with([
                'service',
                'items' => fn ($query) => $query->active()->ordered(),
                'pricingAddons' => fn ($query) => $query->active()->ordered(),
            ])
            ->orderByDesc('is_featured')->ordered()->limit(80)->get();

        $pageSections = $this->sections($page);
        $data['calculatorSection'] = $pageSections->first(fn ($section) => $section->sectionDefinition?->key === 'paint_calculator')
            ?? $data['sharedHomeSections']->first(fn ($section) => $section->sectionDefinition?->key === 'paint_calculator');
        $data['calculatorAsPageHeading'] = true;
        $data['quoteHeadingOverride'] = 'Request Your Painting Price & Quote';
        $data['quoteDescriptionOverride'] = 'Select a published pricing option or describe your project. We will confirm the work scope, materials and final quotation.';
        $data['quotePointsOverride'] = [
            'Compare the active package and material options',
            'Share your property size and preferred work scope',
            'Receive a quotation based on your project requirements',
        ];
        return view('website.pages.pricing', $data);
    }

    public function services(HomeData $homeData, HomeMediaSlides $slides): View
    {
        $page = Page::query()->published()->where('slug', 'services')->first();
        $data = $this->shared($homeData, $slides, $page);
        $data['catalogServices'] = Service::query()->published()->ordered()->get();
        $data['quoteHeadingOverride'] = 'Request a Service Quotation';
        $data['quoteDescriptionOverride'] = 'Tell us which service you need and share your property details. Our team will review your requirements and advise on the next steps.';
        $data['quotePointsOverride'] = [
            'Browse available services published by our team',
            'Select the service relevant to your property',
            'Send your project details for a tailored quotation',
        ];
        return view('website.pages.services', $data);
    }

    /** Preserve the existing service-detail destination without routing through ContentController. */
    public function serviceDetail(string $slug, HomeData $homeData): View
    {
        $service = Service::query()->published()->with('media')->where('slug', $slug)->firstOrFail();
        $data = $homeData->load();
        $data['contentPage'] = $service;
        $data['quoteServiceIdOverride'] = $service->id;
        // The selected service can be outside the six featured Home services.
        $data['services'] = collect($data['services'])->push($service)->unique('id')->values();
        $data['articles'] = collect();
        $data['customPageSections'] = collect();
        $data['faqs'] = $service->faqs()->active()->get();
        return view('website.pages.content', $data);
    }

    private function shared(HomeData $homeData, HomeMediaSlides $slides, ?Page $page): array
    {
        $data = $homeData->load();
        $data['sharedHomeSections'] = collect($data['sections'] ?? []);
        $data['home'] = $page ?? $data['home'];
        $data['contentPage'] = $page;
        $data['services'] = Service::query()->published()->ordered()->get();

        $shared = $data['sharedHomeSections']->keyBy(fn ($section) => $section->sectionDefinition?->key);
        $data['videoSection'] = $shared->get('video_gallery');
        $data['reviewSection'] = $shared->get('testimonials');
        $data['chatSection'] = $shared->get('whatsapp_reviews');
        $data['faqSection'] = $shared->get('faq');
        $data['textReviews'] = collect($data['testimonials'])->filter(fn ($review) => $review->type === 'text'
            && filled(trim(strip_tags((string) $review->review))))->take(24)->values();
        $data['chatSlides'] = $slides->chats($data['testimonials'], $data['chatSection']);
        $data['whatsapps'] = collect($data['contacts'])->where('type', 'whatsapp')->values();

        if ($page && $page->faq_section_enabled) {
            $pageFaqs = $page->faqs()->active()->get();
            if ($pageFaqs->isNotEmpty()) $data['faqs'] = $pageFaqs;
        } elseif ($page && ! $page->faq_section_enabled) {
            $data['faqs'] = collect();
        }
        return $data;
    }

    private function sections(?Page $page): Collection
    {
        if (! $page) return collect();
        return $page->sections()->currentlyVisible()
            ->whereHas('sectionDefinition', fn ($query) => $query->active())
            ->with('sectionDefinition')->ordered()->get();
    }
}
