<?php

namespace App\Services\Website;

use App\Models\PageSection;
use Illuminate\Support\Collection;

/**
 * Combines genuine domain Gallery/Testimonial media with images assigned to
 * their corresponding Home CMS Page Section. No fake review or project data.
 */
class HomeMediaSlides
{
    public function gallery(Collection $galleries, ?PageSection $section): Collection
    {
        $slides = collect();
        $usedMediaIds = [];

        // Preferred source: real Gallery Items that explicitly own before/after media.
        foreach ($galleries->flatMap(fn ($gallery) => $gallery->items) as $item) {
            $before = $item->getFirstMedia('before');
            $after = $item->getFirstMedia('after');
            $single = $item->getFirstMedia('image');

            // A Before/After section should not turn arbitrary single images into fake pairs.
            if (! $before && ! $after && ! $single) {
                continue;
            }

            $slides->push([
                'title' => trim((string) $item->title) ?: 'Project photograph',
                'caption' => trim(strip_tags((string) $item->caption)),
                'before' => $before?->getUrl(),
                'after' => $after?->getUrl(),
                'single' => $single?->getUrl(),
            ]);

            foreach ([$before, $after, $single] as $media) {
                if ($media) {
                    $usedMediaIds[(int) $media->id] = true;
                }
            }
        }

        $assigned = $this->sectionImages($section)
            ->reject(fn ($entry) => isset($usedMediaIds[(int) $entry->media->id]))
            ->sortBy(fn ($entry) => sprintf('%010d-%010d', (int) $entry->sort_order, (int) $entry->id))
            ->values();

        // First honour explicit CMS roles: same sort_order + role before/after = one project.
        $explicit = $assigned
            ->filter(fn ($entry) => in_array(strtolower(trim((string) $entry->role)), ['before', 'after'], true))
            ->groupBy(fn ($entry) => (string) $entry->sort_order);

        $consumedSectionIds = [];
        foreach ($explicit as $entries) {
            $before = $entries->first(fn ($entry) => strtolower(trim((string) $entry->role)) === 'before');
            $after = $entries->first(fn ($entry) => strtolower(trim((string) $entry->role)) === 'after');
            if (! $before || ! $after) {
                continue;
            }

            $slides->push([
                'title' => trim(strip_tags((string) ($after->caption_override ?: $before->caption_override))) ?: 'Before & after project',
                'caption' => '',
                'before' => $before->media->getUrl(),
                'after' => $after->media->getUrl(),
                'single' => null,
            ]);
            $consumedSectionIds[(int) $before->id] = true;
            $consumedSectionIds[(int) $after->id] = true;
        }

        // Your existing Section Media records use role=primary. For this specific
        // Before/After section, pair remaining primary images in visible sort order:
        // 1st+2nd, 3rd+4th, ... . This makes multiple uploads render as the UI expects
        // without changing the database schema or the Admin module.
        $pairable = $assigned
            ->reject(fn ($entry) => isset($consumedSectionIds[(int) $entry->id]))
            ->filter(fn ($entry) => in_array(strtolower(trim((string) $entry->role)), ['', 'primary', 'before_after'], true))
            ->values();

        foreach ($pairable->chunk(2) as $pair) {
            if ($pair->count() < 2) {
                continue; // never invent an After image from a single upload
            }

            $before = $pair->get(0);
            $after = $pair->get(1);
            $slides->push([
                'title' => trim(strip_tags((string) ($after->caption_override ?: $before->caption_override))) ?: 'Before & after project',
                'caption' => '',
                'before' => $before->media->getUrl(),
                'after' => $after->media->getUrl(),
                'single' => null,
            ]);
            $consumedSectionIds[(int) $before->id] = true;
            $consumedSectionIds[(int) $after->id] = true;
        }

        return $slides->take(36)->values();
    }

    public function chats(Collection $testimonials, ?PageSection $section): Collection
    {
        $slides = collect();
        $used = [];
        foreach ($testimonials as $review) {
            if ($review->type !== 'whatsapp_screenshot') continue;
            $image = $review->getFirstMedia('testimonial_screenshot');
            if (! $image || ! $image->isImage() || ! $image->isPickerSafe()) continue;
            $used[(int) $image->id] = true;
            $slides->push([
                'title' => trim((string) $review->customer_name) ?: 'Customer message',
                'caption' => trim(strip_tags((string) $review->review)),
                'image' => $image->getUrl(),
            ]);
        }
        foreach ($this->sectionImages($section) as $entry) {
            if (isset($used[(int) $entry->media->id])) continue;
            $slides->push([
                'title' => 'Customer message screenshot',
                'caption' => trim(strip_tags((string) $entry->caption_override)),
                'image' => $entry->media->getUrl(),
            ]);
        }
        return $slides->take(36)->values();
    }

    private function sectionImages(?PageSection $section): Collection
    {
        return collect($section?->mediaItems ?? [])
            ->filter(fn ($entry) => $entry->media && ! $entry->media->trashed()
                && $entry->media->isImage() && $entry->media->isPickerSafe())
            ->values();
    }
}
