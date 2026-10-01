<?php
namespace App\Services;

use App\Models\Media;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class TestimonialService
{
    public function create(array $data, Request $request): Testimonial
    {
        $testimonial = Testimonial::create($data);
        $this->syncTargets($request, $testimonial);
        $this->syncMedia($request, $testimonial);
        return $testimonial->fresh();
    }
    public function update(Testimonial $testimonial, array $data, Request $request): Testimonial
    {
        $testimonial->update($data);
        $this->syncTargets($request, $testimonial);
        $this->syncMedia($request, $testimonial);
        return $testimonial->fresh();
    }
    public function purgeMedia(Testimonial $testimonial): void
    {
        $testimonial->clearMediaCollection('photo');
        $testimonial->clearMediaCollection('testimonial_screenshot');
    }
    private function syncTargets(Request $request, Testimonial $testimonial): void
    {
        $testimonial->services()->sync($request->input('service_ids', []));
        $testimonial->locations()->sync($request->input('location_ids', []));
    }
    private function syncMedia(Request $request, Testimonial $testimonial): void
    {
        $this->syncCollection($request, $testimonial, 'photo');
        $this->syncCollection($request, $testimonial, 'testimonial_screenshot');
    }
    private function syncCollection(Request $request, Testimonial $testimonial, string $collection): void
    {
        $removeField = $collection . '_remove';
        $mediaField  = $collection . '_media_id';
        if ($request->boolean($removeField)) {
            $testimonial->clearMediaCollection($collection);
            return;
        }
        if ($request->hasFile($collection)) {
            $testimonial->clearMediaCollection($collection);
            $testimonial->addMediaFromRequest($collection)->toMediaCollection($collection, 'public');
            return;
        }
        if ($request->filled($mediaField)) {
            $media = Media::query()->find((int) $request->input($mediaField));
            if (! $media || ! $media->isPickerSafe() || ! $media->isImage()) {
                throw ValidationException::withMessages([$mediaField => 'The selected media must be an active reusable image.']);
            }
            $testimonial->clearMediaCollection($collection);
            $media->copy($testimonial, $collection, 'public');
        }
    }
}
