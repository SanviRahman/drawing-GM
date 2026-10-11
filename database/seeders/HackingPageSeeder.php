<?php
namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Page;
use App\Models\PageSection;
use App\Models\SectionDefinition;
use Illuminate\Database\Seeder;

/** Idempotent CMS defaults. Only missing rows created; user edits stay intact. */
class HackingPageSeeder extends Seeder
{
    public function run(): void
    {
        $page = Page::query()->where('slug', 'hacking')->first();
        if (! $page) {
            $this->command?->warn('Missing CMS Page hacking; create it in Admin -> CMS Pages -> Pages first.');
            return;
        }
        $sections = [
            'hero' => [0, 'Wall & Tile Hacking Services', 'Discuss wall, floor and tile removal options with the team.', []],
            'benefit_grid' => [10, 'Plan Your Hacking Project with Confidence', 'Discuss the work scope, site requirements and applicable approvals.', [
                'items' => [
                    ['title' => 'Define the removal scope', 'description' => 'Confirm rooms, tiles, surfaces and fittings to remove.'],
                    ['title' => 'Check necessary permissions', 'description' => 'Verify applicable HDB or building-management approvals before work.'],
                    ['title' => 'Protect your home', 'description' => 'Discuss dust control and site access with the contractor.'],
                    ['title' => 'Plan debris clearing', 'description' => 'Confirm disposal, cleanup and handover requirements.'],
                ],
            ]],
            'rich_text' => [15, 'What Wall & Tile Hacking Can Include', 'Choose what is relevant to your project and confirm the exact scope in writing.', [
                'groups' => [
                    ['title' => 'Kitchen', 'items' => ['Wall tile removal', 'Floor tile and skirting removal', 'Cabinet dismantling', 'Countertop removal where suitable']],
                    ['title' => 'Bathroom / Toilets', 'items' => ['Wall and floor tile removal', 'Removal of selected sanitary fixtures', 'Door and frame removal if approved', 'Debris clearing']],
                    ['title' => 'Living Room / Bedrooms', 'items' => ['Floor tile and skirting removal', 'Approved non-structural wall work', 'Selected built-in carpentry removal', 'Coordination of floor preparation']],
                    ['title' => 'Planning & Additional Work', 'items' => ['Applicable HDB or MCST permissions', 'Protection and haulage', 'Site access and work schedule', 'Follow-on plastering, tiling or painting']],
                ],
            ]],
            'testimonials' => [20, 'Customer Experiences', 'Published customer feedback from Testimonials Management.', []],
            'service_carousel' => [25, 'What We Can Help With', 'Explore published related renovation services.', []],
            'video_gallery' => [30, 'See How the Work Is Done', 'Published project videos from Video Management.', []],
            'pricing' => [40, 'Wall & Tile Hacking Quotation', 'Prices depend on the approved project scope and site conditions.', []],
            'before_after' => [50, 'Project Photos', 'Images from approved Hacking projects, when available.', []],
            'whatsapp_reviews' => [60, 'Customer Chat Screenshots', 'Published WhatsApp testimonial screenshots.', []],
            'faq' => [70, 'Frequently Asked Hacking Questions', 'Important questions about approvals, scheduling and finishing.', []],
            'contact_form' => [80, 'Request a Hacking Quote', 'Share the work areas and property details.', []],
        ];
        foreach ($sections as $key => [$order, $heading, $subheading, $payload]) {
            $definition = SectionDefinition::query()->where('key', $key)->where('is_active', true)->first();
            if (! $definition) {
                $this->command?->warn("Section definition {$key} missing/inactive; skipped.");
                continue;
            }
            PageSection::query()->firstOrCreate(
                ['page_id' => $page->id, 'section_definition_id' => $definition->id],
                ['sort_order' => $order, 'heading' => $heading, 'subheading' => $subheading,
                    'payload' => $payload, 'theme' => 'default', 'is_active' => true]
            );
        }
        $faqs = [
            'Is approval required before HDB wall hacking?' => 'Some renovation or structural work requires approval. Verify the applicable HDB, MCST and structural rules with qualified contractors before proceeding.',
            'How long does tile or wall hacking take?' => 'Duration depends on area, material, access, approvals and waste removal. Request an estimate for your specific project.',
            'Can hacking work take place on weekends?' => 'Allowed dates and hours depend on local and building-specific restrictions. Confirm permissions and scheduling before booking.',
            'Can floor screeding or wall plastering be included after hacking?' => 'Yes, you can request a combined quotation for related work. The team must confirm scope, timing and prices before work starts.',
            'What details should I provide for a hacking quote?' => 'Share room measurements, surface photos, the items to remove, property type, access conditions and known approval requirements.',
        ];
        $order = 10;
        foreach ($faqs as $question => $answer) {
            $faq = Faq::query()->firstOrCreate(['question' => $question], ['answer' => $answer, 'is_active' => true]);
            if (! $page->faqs()->whereKey($faq->id)->exists()) {
                $page->faqs()->attach($faq->id, ['sort_order' => $order]);
            }
            $order += 10;
        }
        $this->command?->info('Hacking CMS sections and FAQs ensured. Existing Admin content unchanged.');
    }
}
