<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\SectionDefinition;
use Illuminate\Database\Seeder;

/**
 * Add only MISSING, editable Plastering landing-page sections and FAQ links.
 * No competitor prices, media, testimonials, phone numbers, or guarantees seeded.
 * Never replace content already edited in Admin.
 */
class PlasteringPageSeeder extends Seeder
{
    public function run(): void
    {
        $page = Page::query()->where('slug', 'plastering')->first();
        if (! $page) {
            $this->command?->warn('Plastering CMS page is missing. Create it in Admin -> CMS Pages -> Pages first.');
            return;
        }

        $sections = [
            'hero' => [0, 'Wall Plastering Services', 'Explore wall preparation and plastering options for your project.', []],
            'benefit_grid' => [10, 'Wall Plastering for Your Home', 'Discuss suitable preparation, surface condition and finishes before work begins.', [
                'items' => [
                    ['title' => 'Surface inspection', 'description' => 'Identify cracks, uneven walls, loose coatings or water damage before preparing the quotation.'],
                    ['title' => 'Material selection', 'description' => 'Confirm suitable products and application methods for the existing substrate.'],
                    ['title' => 'Clear project scope', 'description' => 'Agree on protection, preparation, coats, scheduling and cleanup.'],
                ],
            ]],
            'testimonials' => [20, 'Customer Reviews', 'Feedback saved and activated in Testimonials Management.', []],
            'video_gallery' => [30, 'See How Wall Plastering Is Done', 'Watch published project videos added through Admin -> Videos.', []],
            'pricing' => [40, 'Wall Plastering Pricing & Quotation', 'Review approved pricing options or request a detailed quotation.', []],
            'before_after' => [50, 'Before & After Wall Plastering', 'Project photographs added through Gallery Management and Section Media.', []],
            'whatsapp_reviews' => [60, 'Customer Chat Screenshots', 'Approved screenshot reviews from Testimonials Management.', []],
            'faq' => [70, 'Frequently Asked Plastering Questions', 'Common questions to clarify with the team before starting.', []],
            'contact_form' => [80, 'Request a Plastering Quotation', 'Tell us about your property and the work you need.', []],
        ];

        foreach ($sections as $key => [$order, $heading, $subheading, $payload]) {
            $definition = SectionDefinition::query()->where('key', $key)->where('is_active', true)->first();
            if (! $definition) {
                $this->command?->warn("Section Definition '{$key}' is missing/inactive; skipped.");
                continue;
            }
            PageSection::query()->firstOrCreate(
                ['page_id' => $page->id, 'section_definition_id' => $definition->id],
                [
                    'sort_order' => $order, 'heading' => $heading, 'subheading' => $subheading,
                    'payload' => $payload, 'theme' => 'default', 'is_active' => true,
                ]
            );
        }

        $questions = [
            'Do existing tiles need to be removed before plastering?' => 'The required surface preparation depends on the existing tiles, bond and coating system. Have the substrate assessed before deciding on hacking or an alternative approach.',
            'How long might a wall plastering project take?' => 'The time depends on wall area, surface condition, repair requirements, product curing time and site access. Request a schedule based on your property.',
            'How many plaster coats might be required?' => 'The number of coats depends on the selected product and the wall condition. Your final quotation should specify the proposed application system.',
            'How long should plaster dry before further work?' => 'Drying and curing depend on the product, coat thickness, humidity and ventilation. Always follow product guidance and your contractor’s site assessment.',
            'Can plastering be included with painting or hacking?' => 'You can request a combined quotation. Describe each work area so the required trades, preparation and sequence can be reviewed together.',
            'What information helps when asking for a plastering quote?' => 'Share your property type, rooms involved, approximate work area, close-up photos of problem walls and the desired finish.',
        ];
        foreach ($questions as $index => $answer) {
            $question = $index;
            $faq = Faq::query()->firstOrCreate(['question' => $question], ['answer' => $answer, 'is_active' => true]);
            $page->faqs()->syncWithoutDetaching([$faq->id => ['sort_order' => (array_search($question, array_keys($questions), true) + 1) * 10]]);
        }

        $this->command?->info('Plastering sections and FAQs seeded without overwriting existing CMS content.');
    }
}
