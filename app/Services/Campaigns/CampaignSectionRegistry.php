<?php

namespace App\Services\Campaigns;

use App\Models\CampaignSection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CampaignSectionRegistry
{
    public function labels(): array
    {
        return CampaignSection::TYPES;
    }

    public function componentFor(string $key): string
    {
        $this->assertAllowed($key);

        return 'components.campaign-sections.' . str_replace('_', '-', $key);
    }

    public function validate(string $key, array $payload): array
    {
        $this->assertAllowed($key);

        $rules = match ($key) {
            'hero' => [
                'headline' => ['nullable', 'string', 'max:190'],
                'subheadline' => ['nullable', 'string', 'max:500'],
                'cta_label' => ['nullable', 'string', 'max:120'],
                'cta_url' => ['nullable', 'string', 'max:2048'],
            ],
            'hero_benefits' => [
                'items' => ['nullable', 'array', 'max:12'],
                'items.*.title' => ['required_with:items', 'string', 'max:120'],
                'items.*.text' => ['nullable', 'string', 'max:300'],
                'items.*.icon' => ['nullable', 'string', 'max:100'],
            ],
            'service_grid' => [
                'service_ids' => ['nullable', 'array', 'max:30'],
                'service_ids.*' => ['integer', 'distinct', Rule::exists('services', 'id')->whereNull('deleted_at')],
            ],
            'category_brand' => [
                'items' => ['nullable', 'array', 'max:30'],
                'items.*.name' => ['required_with:items', 'string', 'max:150'],
                'items.*.url' => ['nullable', 'string', 'max:2048'],
            ],
            'pricing' => [
                'pricing_package_ids' => ['nullable', 'array', 'max:30'],
                'pricing_package_ids.*' => ['integer', 'distinct', Rule::exists('pricing_packages', 'id')->whereNull('deleted_at')],
            ],
            'gallery' => [
                'gallery_ids' => ['nullable', 'array', 'max:30'],
                'gallery_ids.*' => ['integer', 'distinct', Rule::exists('galleries', 'id')->whereNull('deleted_at')],
            ],
            'video_gallery' => [
                'video_ids' => ['nullable', 'array', 'max:30'],
                'video_ids.*' => ['integer', 'distinct', Rule::exists('videos', 'id')->whereNull('deleted_at')],
            ],
            'testimonials', 'whatsapp_reviews' => [
                'testimonial_ids' => ['nullable', 'array', 'max:30'],
                'testimonial_ids.*' => ['integer', 'distinct', Rule::exists('testimonials', 'id')->whereNull('deleted_at')],
            ],
            'faq' => [
                'faq_ids' => ['nullable', 'array', 'max:50'],
                'faq_ids.*' => ['integer', 'distinct', Rule::exists('faqs', 'id')->whereNull('deleted_at')],
            ],
            'cta' => [
                'heading' => ['nullable', 'string', 'max:190'],
                'body' => ['nullable', 'string', 'max:2000'],
                'button_label' => ['nullable', 'string', 'max:120'],
                'button_url' => ['nullable', 'string', 'max:2048'],
            ],
            'lead_form' => [
                'heading' => ['nullable', 'string', 'max:190'],
                'subheading' => ['nullable', 'string', 'max:500'],
                'submit_label' => ['nullable', 'string', 'max:120'],
            ],
            default => [],
        };

        $validator = Validator::make($payload, $rules);

        if ($validator->fails()) {
            throw ValidationException::withMessages([
                'payload' => $validator->errors()->all(),
            ]);
        }

        return $payload;
    }

    public function assertAllowed(string $key): void
    {
        if (! array_key_exists($key, CampaignSection::TYPES)) {
            throw ValidationException::withMessages([
                'section_key' => 'Unknown campaign section type.',
            ]);
        }
    }
}
