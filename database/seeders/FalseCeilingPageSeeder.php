<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\SectionDefinition;
use Illuminate\Database\Seeder;

/**
 * Idempotent, copy-only CMS defaults. Does not invent customer reviews, photos,
 * business guarantees or prices. Does not replace content edited in Admin.
 */
class FalseCeilingPageSeeder extends Seeder
{
    public function run(): void
    {
        $page = Page::query()->where('slug', 'false-ceiling')->first();
        if (! $page) {
            $this->command?->warn('False Ceiling page not found. Create /false-ceiling in Admin → CMS Pages → Pages first.');
            return;
        }

        $sections = [
            'hero' => [0, 'False Ceiling & Partition Wall Services',
                'Plan a false ceiling or partition project with clear material, layout and installation requirements.', []],
            'benefit_grid' => [10, 'False Ceiling Installation for Your Space',
                'From ceiling profiles to material options, discuss the right setup for your property.', [
                    'items' => [
                        ['title' => 'Review the ceiling layout', 'description' => 'Discuss flat ceiling profiles, L-boxes and lighting coves.'],
                        ['title' => 'Choose suitable materials', 'description' => 'Consider moisture resistance, room conditions and access panels.'],
                        ['title' => 'Coordinate lighting and services', 'description' => 'Confirm light fittings, access and any licensed electrical work.'],
                    ],
                ]],
            'rich_text' => [15, 'What We Can Help With',
                'Explore common ceiling and partition options; your project quotation will confirm which items are included.', [
                    'groups' => [
                        ['title' => 'False Ceiling Designs', 'description' => 'Discuss your preferred ceiling profile and finish.',
                            'items' => ['Standard flat false ceiling', 'L-box and perimeter detail', 'Lighting cove and feature detailing', 'Access panels and maintenance clearance']],
                        ['title' => 'Partition Wall Installation', 'description' => 'Choose materials appropriate to each room.',
                            'items' => ['Regular drywall partitions', 'Acoustic partition options', 'Moisture-resistant board options', 'Fire-rated specifications if required']],
                        ['title' => 'Preparation & Handover', 'description' => 'Confirm site requirements before work starts.',
                            'items' => ['Room measurements and ceiling height', 'Lighting and electrical coordination', 'Building-management requirements', 'Finishing, painting and cleanup scope']],
                    ],
                ]],
            'testimonials' => [20, 'Customer Feedback', 'Active customer reviews from your CMS.', []],
            'pricing' => [30, 'False Ceiling & Partition Wall Prices',
                'Review live published package items. Confirm design, site conditions and the final quotation with the team.', []],
            'video_gallery' => [40, 'See Our Ceiling & Partition Projects',
                'Watch videos assigned to the False Ceiling page or service when available.', []],
            'before_after' => [50, 'Ceiling & Partition Project Gallery',
                'Photographs from published false ceiling and partition projects.', []],
            'whatsapp_reviews' => [60, 'Client Chat Screenshots',
                'Approved customer messages shared through the existing testimonials module.', []],
            'faq' => [70, 'Frequently Asked False Ceiling Questions',
                'Useful information about ceiling materials, measurements, lighting and installation.', []],
            'contact_form' => [80, 'Request a False Ceiling Quotation',
                'Share your preferred design, approximate size and required services.', []],
        ];

        foreach ($sections as $key => [$order, $heading, $subheading, $payload]) {
            $definition = SectionDefinition::query()->where('key', $key)->where('is_active', true)->first();
            if (! $definition) {
                $this->command?->warn("Missing or inactive Section Definition: {$key}; skipped.");
                continue;
            }
            PageSection::query()->firstOrCreate(
                ['page_id' => $page->id, 'section_definition_id' => $definition->id],
                ['heading' => $heading, 'subheading' => $subheading, 'payload' => $payload,
                    'sort_order' => $order, 'theme' => 'default', 'is_active' => true]
            );
        }

        $faqs = [
            'Can a false ceiling be installed in a bathroom or kitchen?' =>
                'The appropriate board system depends on moisture, ventilation and exposure. Discuss moisture-resistant materials and maintenance access before deciding.',
            'How low can a false ceiling be installed?' =>
                'The final ceiling level depends on room height, services and applicable building requirements. Confirm the allowed clearance and measurements before installation.',
            'What is the difference between regular and moisture-resistant boards?' =>
                'They are designed for different exposure conditions. Ask which board specifications and finishing system are suitable for the room.',
            'Can recessed lighting and access panels be included?' =>
                'Yes, they can be discussed as part of the project scope. Confirm electrical coordination, fitting sizes, safe access and separate charges before booking.',
            'What affects the false ceiling or partition wall price?' =>
                'Area, board specification, frame design, complexity, access, finishing and additional fittings all affect the price. Published rates are only a guide until the site scope is confirmed.',
            'Do I need building-management approval for partition work?' =>
                'Requirements depend on the property and proposed work. Check applicable HDB, MCST and other building rules before proceeding.',
        ];

        $order = 30;
        foreach ($faqs as $question => $answer) {
            $faq = Faq::query()->firstOrCreate(['question' => $question], ['answer' => $answer, 'is_active' => true]);
            if (! $page->faqs()->whereKey($faq->id)->exists()) {
                $page->faqs()->attach($faq->id, ['sort_order' => $order]);
            }
            $order += 10;
        }

        $this->command?->info('False Ceiling page sections and FAQs ensured. Existing media, prices, and Admin edits kept.');
    }
}
