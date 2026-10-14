<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Expand the original painting blog guides without replacing editorial changes.
 *
 * Only posts whose excerpt AND body match the old ProjectDefaultSeeder text
 * are migrated. User-authored posts, altered posts, trashed posts and drafts
 * remain untouched. This seeder may safely be re-run.
 */
class BlogDescriptionsSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->articles() as $slug => $article) {
            $post = DB::table('posts')
                ->where('slug', $slug.'-starter')
                ->whereNull('deleted_at')
                ->first(['id', 'status', 'excerpt', 'body']);

            if (! $post || $post->status !== 'published') {
                continue;
            }

            $legacyExcerpt = $article['old_excerpt'];
            $legacyBody = '<p>'.e($legacyExcerpt).'</p>'
                .'<p>For a specific home, ask the team to review the site conditions and provide an agreed written scope.</p>';

            // Do not overwrite even a single hand-edited field.
            if ((string) $post->excerpt !== $legacyExcerpt || (string) $post->body !== $legacyBody) {
                continue;
            }

            $body = $this->renderArticle($article);
            $minutes = max(1, (int) ceil(str_word_count(strip_tags($body)) / 200));

            DB::table('posts')
                ->where('id', $post->id)
                ->where('excerpt', $legacyExcerpt)
                ->where('body', $legacyBody)
                ->update([
                    'excerpt' => $article['excerpt'],
                    'body' => $body,
                    'reading_minutes' => $minutes,
                    'updated_at' => now(),
                ]);
        }

        // The original welcome post is seeded separately and is included too.
        $welcome = DB::table('posts')
            ->where('slug', 'welcome-to-our-blog')
            ->whereNull('deleted_at')
            ->first(['id', 'status', 'excerpt', 'body']);

        $oldWelcomeExcerpt = 'An introduction to planning preparation, quotes and finishing work.';
        $oldWelcomeBody = '<p>Before requesting painting or plastering work, identify the rooms and surfaces involved, existing problems and desired finish. Compare the proposed scope and materials in a quotation.</p>';

        if ($welcome && $welcome->status === 'published'
            && (string) $welcome->excerpt === $oldWelcomeExcerpt
            && (string) $welcome->body === $oldWelcomeBody) {
            $article = $this->welcomeArticle();
            $body = $this->renderArticle($article);
            DB::table('posts')->where('id', $welcome->id)
                ->where('excerpt', $oldWelcomeExcerpt)->where('body', $oldWelcomeBody)
                ->update([
                    'excerpt' => $article['excerpt'],
                    'body' => $body,
                    'reading_minutes' => max(1, (int) ceil(str_word_count(strip_tags($body)) / 200)),
                    'updated_at' => now(),
                ]);
        }
    }

    /** Escape every editorial string and keep article HTML predictable. */
    private function renderArticle(array $article): string
    {
        $html = '<p>'.e($article['intro']).'</p>';
        foreach ($article['sections'] as $heading => $paragraphs) {
            $html .= '<h2>'.e($heading).'</h2>';
            foreach ($paragraphs as $paragraph) {
                $html .= '<p>'.e($paragraph).'</p>';
            }
        }
        $html .= '<h2>Next steps</h2><p>'.e($article['closing']).'</p>';
        return $html;
    }

    private function articles(): array
    {
        return [
            'painting-guides' => [
                'old_excerpt' => 'Move loose items, discuss masking and protect furniture and flooring before the work begins.',
                'excerpt' => 'Prepare your room before painters arrive: clear access, protect furniture and floors, check wall damage, and agree on the work scope, materials and cleanup.',
                'intro' => 'A well-prepared room makes it easier to inspect surfaces, plan the work and protect belongings during painting. Preparation is more than moving furniture: it includes deciding which areas will be painted, recording existing defects and discussing how the room will be used while work is under way.',
                'sections' => [
                    'Clear and protect the work area' => [
                        'Remove small objects, wall decorations and valuables before work begins. Move larger furniture away from the walls where practical, and ask the painting team how they will cover items that must remain in the room.',
                        'Discuss floor protection, masking around switches and fittings, and safe access to windows, doors and corners. Confirm who will remove or reinstall fixtures so nothing is damaged or accidentally discarded.',
                    ],
                    'Inspect walls and ceilings' => [
                        'Look for loose paint, small cracks, stains, uneven patches or areas that feel damp. Take clear photographs and tell the team where each issue occurs instead of assuming a new coat of paint will conceal it.',
                        'Some surfaces need cleaning, filling, sanding or a suitable primer before finishing coats. Moisture problems should be investigated before painting because covering the visible mark does not remove its underlying cause.',
                    ],
                    'Agree on the painting sequence' => [
                        'List the rooms, surfaces, doors and ceilings included in the quotation. Ask about paint type, sheen, number of coats, colour confirmation and how unexpected repairs will be priced.',
                        'Plan room access, ventilation and the movement of occupants or pets. Keep walkways clear, and follow the product manufacturer’s recommendations for drying and reoccupation.',
                    ],
                ],
                'closing' => 'Use a written room-by-room checklist before work starts. Review the final scope and any changes with the contractor, then inspect the completed surfaces together before removing protective coverings.',
            ],
            'wall-preparation' => [
                'old_excerpt' => 'Identify cracking, peeling paint, uneven patches and moisture before choosing a preparation method.',
                'excerpt' => 'Inspect peeling paint, cracks, stains, uneven plaster and possible moisture before repainting. Learn what to photograph and which repairs to discuss first.',
                'intro' => 'Paint quality depends partly on the condition of the surface beneath it. Before deciding on colours or finishes, inspect the walls in good light and note any damaged, powdery or uneven areas. Different symptoms may require different preparation methods.',
                'sections' => [
                    'Identify visible surface problems' => [
                        'Peeling paint may indicate poor adhesion, contamination or moisture. Fine hairline cracks are different from recurring or widening cracks, and deep holes need a different repair method from shallow surface marks.',
                        'Check around windows, bathrooms, air-conditioning areas and previously repaired patches. Photograph defects with an object for scale, and record whether the problem is new, recurring or spreading.',
                    ],
                    'Decide what must happen before painting' => [
                        'Discuss cleaning, removal of loose material, filling, sanding and priming with the contractor. A sound existing coating may need relatively little work, while flaking or uneven areas can require more extensive treatment.',
                        'Do not simply paint over active leaks or persistent dampness. The source of moisture should be identified and addressed first, with professional assessment when the cause is unclear.',
                    ],
                    'Check the proposed materials and finish' => [
                        'Ask which filler, plaster, primer and topcoat will be used, and whether they suit the existing substrate. Request sample patches where texture or colour matching is important.',
                        'Confirm what level of smoothness is included and whether old repairs may remain visible in certain lighting. A written scope avoids confusion between touch-up work and complete surface preparation.',
                    ],
                ],
                'closing' => 'Keep photos and a room-by-room defect list with your quotation. Recheck repaired areas before the final coats so concerns can be discussed while the work remains accessible.',
            ],
            'hdb-renovation' => [
                'old_excerpt' => 'Discuss access, occupied rooms, drying time and relevant renovation restrictions before booking.',
                'excerpt' => 'Plan HDB flat painting with room access, furniture protection, ventilation and renovation rules in mind. Confirm timing and scope before booking contractors.',
                'intro' => 'Painting an occupied HDB flat requires practical scheduling as well as a clear work scope. Family routines, stored belongings, shared access and other ongoing renovation jobs can affect the order in which rooms should be prepared and painted.',
                'sections' => [
                    'Check access and building requirements' => [
                        'Before confirming a start date, verify any current requirements relevant to your work with the appropriate building or estate authority. Ordinary painting and work involving structural changes, electrical installations or other regulated activities should not be treated as interchangeable.',
                        'Discuss delivery access, parking or loading, protection of common areas and arrangements for workers entering the flat. Make sure the contractor knows who can provide access during working hours.',
                    ],
                    'Plan around occupied rooms' => [
                        'Decide whether the home will remain occupied during painting. Identify which rooms need to stay usable and where movable items can be stored while walls and ceilings are prepared.',
                        'Talk about ventilation, paint selection and manufacturer-stated drying times. Keep children and pets away from active work areas and follow appropriate safety guidance for coatings and cleaning products.',
                    ],
                    'Coordinate the work scope' => [
                        'Write down the room dimensions, surfaces, existing damage, paint colours and finishes. Ask whether ceiling work, patch repairs, door frames and cleanup are part of the quoted amount.',
                        'If carpentry, plastering or other renovation is scheduled, agree on the sequence so freshly painted surfaces are not unnecessarily damaged by later work. Confirm who handles touch-ups after other trades leave.',
                    ],
                ],
                'closing' => 'Use a shared schedule and a written quotation to track preparations, agreed materials and handover checks. Confirm any building-specific restrictions before work begins.',
            ],
            'condo-renovation' => [
                'old_excerpt' => 'Check building management rules, permitted work times and lift or loading access.',
                'excerpt' => 'Before condo painting, check management approval, permitted work hours, service-lift access, delivery arrangements and protection of shared spaces.',
                'intro' => 'Painting work in a condominium may involve management procedures in addition to the preparation required inside the unit. Requirements vary between developments, so homeowners and contractors should confirm the current rules rather than relying on assumptions.',
                'sections' => [
                    'Confirm management procedures' => [
                        'Ask your management office whether the planned scope needs an application, contractor registration, security arrangements or other documents. Check permitted work periods and any conditions that apply to deliveries or noisy preparation.',
                        'If more than decorative painting is proposed, such as changes to fixtures or other renovation work, confirm whether separate approvals or qualified trades are needed. Keep written responses for the contractor.',
                    ],
                    'Prepare access and protection' => [
                        'Find out how materials may be brought to the unit, whether a service lift must be booked and where workers may load or unload. Discuss protecting lift interiors, corridors and the route into the property.',
                        'Inside the unit, plan furniture movement, surface coverings and access to windows, built-in cabinets and ceiling edges. Identify fragile finishes that need special masking or handling.',
                    ],
                    'Set a realistic sequence' => [
                        'Review wall condition and expected preparation before selecting start and handover dates. Drying conditions, room availability and coordination with other contractors can influence the programme.',
                        'Confirm the proposed paint system, colours, cleaning responsibilities and how changes will be approved. A room-by-room checklist helps distinguish work included in the quotation from optional repairs.',
                    ],
                ],
                'closing' => 'Keep the management requirements, access plan and approved work scope together. Confirm the handover inspection and removal of protective materials before considering the job complete.',
            ],
            'plastering-guides' => [
                'old_excerpt' => 'Plastering requirements depend on the surface condition, product system and desired finish.',
                'excerpt' => 'Understand when wall plastering may be needed, how surface defects affect preparation, and what finish, drying and quotation details to confirm.',
                'intro' => 'Wall plastering and skim-coating can help prepare an uneven surface for decoration, but the right approach depends on the existing substrate and the finish you want. A visual inspection is the starting point; not every mark or crack requires the same treatment.',
                'sections' => [
                    'Assess the substrate' => [
                        'Identify loose material, uneven patches, old repairs and signs of moisture. Ask whether the existing surface is suitable for the proposed plastering system and what cleaning or removal is required first.',
                        'For recurring cracks, water damage or areas that sound hollow, request an appropriate assessment rather than immediately covering the symptoms. Surface defects can reappear if their cause remains unresolved.',
                    ],
                    'Discuss the proposed finish' => [
                        'Clarify whether the work is a local patch, a full skim coat or another preparation method. Different levels of smoothness can affect labour, material selection and the visibility of imperfections under lighting.',
                        'Ask what primer or finishing coat is recommended after plastering and whether a sample area can be prepared for your approval. Confirm which walls and ceilings are included.',
                    ],
                    'Understand curing and handover' => [
                        'The material manufacturer and site conditions determine appropriate drying and subsequent coating guidance. Avoid assuming that all plaster products can be painted after the same interval.',
                        'Check repaired edges, corners, surface continuity and any agreed touch-up work before final painting. Request a clear explanation of exclusions and how changes to damaged areas will be handled.',
                    ],
                ],
                'closing' => 'Provide clear photographs and the desired finish when requesting a quotation. Agree on surface preparation, materials, drying guidance and the final inspection criteria in writing.',
            ],
            'paint-selection' => [
                'old_excerpt' => 'Compare finish, durability, cleaning needs and manufacturer guidance for each room.',
                'excerpt' => 'Compare paint sheen, cleanability, lighting and room use before choosing a finish. Review manufacturer guidance and test sample colours on your walls.',
                'intro' => 'Choosing paint involves more than selecting a colour from a chart. Sheen, surface preparation, room use and the manufacturer’s product guidance affect how the finish looks and how it can be maintained.',
                'sections' => [
                    'Match paint to the room' => [
                        'Think about how each space is used, how often surfaces are touched and the sort of cleaning they may need. A busy hallway, quiet bedroom and moisture-prone area can present different demands.',
                        'Ask whether the proposed coating is designed for the relevant surface and environment. Avoid assuming a product is suitable for damp areas, wet surfaces or specialist substrates without checking its technical guidance.',
                    ],
                    'Consider sheen and appearance' => [
                        'Matte, low-sheen and glossier finishes reflect light differently. More reflective finishes can make certain uneven patches noticeable, while the appearance of a colour may shift with sunlight and artificial lighting.',
                        'Request sample patches on the actual wall if possible. Check them at different times of day and alongside furniture, flooring and existing trim before approving the final selection.',
                    ],
                    'Confirm care and application' => [
                        'Review compatibility between primer, existing paint and finishing coats. Ask the contractor about surface preparation, application method and expected number of coats for the agreed product system.',
                        'Follow the manufacturer’s instructions on drying, curing and cleaning. Keep the paint name, finish and colour reference so later touch-ups or future maintenance are easier to plan.',
                    ],
                ],
                'closing' => 'Confirm the exact product and finish on the written quotation instead of relying on a general description such as “washable paint.” Approve sample colours before the main application.',
            ],
            'renovation-checklists' => [
                'old_excerpt' => 'Confirm material specifications, included preparation, scope changes and cleanup responsibilities.',
                'excerpt' => 'Review quotations carefully: compare preparation, paint products, excluded repairs, site protection, payment stages and cleanup before accepting the job.',
                'intro' => 'A clear quotation helps homeowners compare proposals fairly. Two prices may cover different preparation work, materials or cleanup responsibilities, so a low total alone does not show whether the offers are equivalent.',
                'sections' => [
                    'Check the scope and materials' => [
                        'Ask for the rooms and surfaces to be listed, including ceilings, doors, trims and any areas excluded from the price. Clarify whether peeling paint, cracks, stains or uneven plaster will be repaired.',
                        'Confirm paint brand or product specification, finish, colour selection, priming where required and the basis for the proposed number of coats. Ask how unexpected substrate issues will be assessed.',
                    ],
                    'Understand costs and changes' => [
                        'Check whether the quote includes labour, materials, protection of furniture and floors, waste handling and final cleaning. Request a clear explanation of any provisional amounts or optional work.',
                        'Agree on how additional repairs, changed colours or an expanded scope will be approved and priced. Review payment milestones, scheduling and what happens if work is delayed.',
                    ],
                    'Plan inspection and handover' => [
                        'Ask who supervises the work, how you can raise concerns and when a final walk-through will happen. Record any agreed touch-ups with photographs and a target for completion.',
                        'Check any written warranty terms carefully, including what is covered and excluded. Do not rely on verbal claims about performance or duration where a written explanation is available.',
                    ],
                ],
                'closing' => 'Keep the accepted quotation and all approved changes together. A brief written checklist makes the scope easier to understand and reduces avoidable misunderstandings during the project.',
            ],
            'colour-planning' => [
                'old_excerpt' => 'Try colour samples in your room under daylight and evening lighting before final selection.',
                'excerpt' => 'Choose interior colours with confidence by testing samples in daylight and evening light, comparing undertones and checking them beside your furnishings.',
                'intro' => 'A paint colour can look different on a small swatch, a large wall and a finished room. Natural light, artificial lamps, neighbouring colours and surface sheen all influence how the colour is perceived.',
                'sections' => [
                    'Start with the room, not the catalogue' => [
                        'Look at flooring, curtains, cabinets and furniture that will remain in place. Decide whether you want the walls to feel light, calm, warm or contrasting, and narrow your options to a manageable selection.',
                        'Consider how each room connects to adjacent spaces. You do not need to use identical colours throughout, but checking the transition between rooms can avoid combinations that feel unexpected.',
                    ],
                    'Test colours under real lighting' => [
                        'Apply manufacturer-approved sample patches or use suitable test boards near different parts of the room. Observe them in morning, afternoon and evening conditions rather than selecting a colour under showroom lighting alone.',
                        'Check how the samples look beside white ceilings, wood finishes and large furnishings. Shadows and reflected colours can shift the appearance of undertones, especially across large wall areas.',
                    ],
                    'Confirm finish and final selection' => [
                        'The same colour may appear different in matte and more reflective finishes. Choose the product and sheen with the room’s use and cleaning needs in mind, following the manufacturer’s guidance.',
                        'Record the exact colour code, product name and finish before work starts. If several rooms are involved, use a written schedule to avoid mix-ups during ordering and application.',
                    ],
                ],
                'closing' => 'Approve the selected samples on your own walls whenever practical. Keep the final colour schedule with your quotation so future touch-ups and extensions are easier to coordinate.',
            ],
            'surface-care' => [
                'old_excerpt' => 'Follow the paint manufacturer’s curing and cleaning instructions before washing surfaces.',
                'excerpt' => 'Protect freshly painted walls by allowing proper curing, preventing early marks and choosing cleaning methods suited to the paint manufacturer’s guidance.',
                'intro' => 'A freshly painted wall may feel dry before the coating has fully developed its intended performance. Gentle use and the manufacturer’s guidance during the early period can help prevent avoidable marks and premature cleaning damage.',
                'sections' => [
                    'Understand drying versus curing' => [
                        'Dry-to-touch times and full curing periods are not always the same. They depend on the product and conditions, so ask the contractor for the actual paint manufacturer’s instructions instead of assuming one schedule suits every coating.',
                        'During the recommended period, avoid rubbing, scrubbing or attaching items to new paint without checking that it is suitable. Maintain appropriate ventilation and keep vulnerable areas clear.',
                    ],
                    'Use a suitable cleaning method' => [
                        'For routine dust, start with the mildest method recommended for the coating. Spot-test cleaning on an inconspicuous area and avoid abrasive tools or harsh chemicals unless the product guidance specifically permits them.',
                        'Different sheens and paint formulations have different cleaning properties. If a stain does not lift easily, stop before damaging the surface and seek product-specific advice.',
                    ],
                    'Keep records for maintenance' => [
                        'Store the paint product, sheen, colour code and date of application. These details help when discussing a repair or selecting a compatible touch-up material later.',
                        'Photograph persistent cracking, bubbling, staining or peeling rather than repeatedly covering it. Recurring defects may have an underlying cause that should be assessed before repainting.',
                    ],
                ],
                'closing' => 'Follow the coating manufacturer’s current cleaning and curing directions, and ask the installer about any areas that need special care. Treat new damage promptly without aggressive cleaning.',
            ],
            'project-planning' => [
                'old_excerpt' => 'Coordinate room access, furnishings, occupants and any other renovation works before scheduling.',
                'excerpt' => 'Create a practical painting schedule that covers surface preparation, room access, other trades, drying time, inspections and final handover.',
                'intro' => 'Painting schedules work best when everyone understands which rooms are available, what preparation is needed and how the work fits around daily activities. A practical sequence should allow time for inspection and unexpected surface repairs.',
                'sections' => [
                    'List tasks before dates' => [
                        'Record every area to be painted and identify work that must happen first, such as checking moisture problems, completing plaster repairs or moving bulky furniture. Ask the contractor which tasks depend on earlier steps.',
                        'Confirm material selections and colour approvals before the intended start date. Late changes can affect ordering and the order in which rooms are completed.',
                    ],
                    'Coordinate access and other trades' => [
                        'Decide which rooms must remain usable and when occupants can safely return, based on the chosen product guidance. Protect belongings and establish a secure place for equipment and materials.',
                        'If electrical, carpentry, floor or ceiling work is also planned, clarify the preferred sequence with those contractors. Painting too early can lead to unnecessary patching and repainting.',
                    ],
                    'Allow for checks and adjustments' => [
                        'Ask for a realistic programme rather than a date unsupported by the scope. Site conditions, extra preparation and manufacturer-stated drying intervals may influence the schedule.',
                        'Agree on regular progress updates, how added work will be authorised and when the final walk-through will happen. Set aside time to review touch-ups and removal of protective materials.',
                    ],
                ],
                'closing' => 'Keep the schedule, room list, colour references and accepted quotation in one place. Review changes as they occur so everyone shares the same expectations about completion and handover.',
            ],
        ];
    }

    private function welcomeArticle(): array
    {
        return [
            'excerpt' => 'Start planning painting or plastering with a clear scope, room measurements, surface-condition photos, suitable materials and an agreed written quotation.',
            'intro' => 'A home painting or plastering project is easier to plan when the key decisions are made before work starts. You do not need to know every technical detail, but you should be able to explain what you want to improve and which surfaces are involved.',
            'sections' => [
                'Describe the project clearly' => [
                    'List the rooms, walls and ceilings involved. Take photographs of peeling paint, cracks, uneven plaster, stains or other areas of concern. Where possible, provide measurements and note whether the property is occupied.',
                    'Explain the finish you want, including colour preferences and whether cleaning, durability or low odour matters for the room. A site inspection may still be needed before the final scope can be confirmed.',
                ],
                'Compare preparation and materials' => [
                    'Ask what surface preparation is included, whether problem areas need assessment, and what primer or finishing system the contractor proposes. The visible colour is only one part of the specification.',
                    'Review sample colours under the room’s lighting and keep the exact product and finish references. Do not assume that all existing coatings or damaged surfaces can be treated in the same way.',
                ],
                'Confirm the quotation and handover' => [
                    'A written quotation should state what is included and excluded, the planned programme and how variations will be approved. Check site protection, waste removal, cleaning and payment arrangements.',
                    'Before handover, inspect the agreed work together and record outstanding touch-ups. Keep the final scope and colour schedule for future maintenance.',
                ],
            ],
            'closing' => 'Begin with a concise checklist and photographs, then ask the painting team to verify the actual site conditions. Use the agreed written scope to guide the job from preparation to final inspection.',
        ];
    }
}
