<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadFormAnswer;
use App\Models\LeadFormField;
use App\Models\LeadNote;
use App\Models\LeadService;
use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LeadWorkflowService
{
    public function normalizePhone(string $value): string
    {
        $value = trim($value);
        $value = preg_replace('/[\s().-]+/', '', $value) ?? $value;

        if (str_starts_with($value, '00')) {
            $value = '+' . substr($value, 2);
        }

        if (! preg_match('/^\+[1-9]\d{7,14}$/', $value)) {
            throw ValidationException::withMessages([
                'phone' => 'Phone/WhatsApp must use international E.164 format, for example +6591234567.',
            ]);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $answers keyed by LeadFormField.field_key
     */
    public function snapshotDynamicAnswers(Lead $lead, array $answers): void
    {
        $fields = LeadFormField::query()
            ->active()
            ->ordered()
            ->get();

        $knownKeys = $fields->pluck('field_key')->all();
        $unknownKeys = array_values(array_diff(array_keys($answers), $knownKeys));

        if ($unknownKeys !== []) {
            throw ValidationException::withMessages([
                'dynamic_answers' => 'One or more booking answers do not match an active booking field.',
            ]);
        }

        foreach ($fields as $field) {
            $raw = $answers[$field->field_key] ?? null;
            $answer = is_scalar($raw) ? trim((string) $raw) : '';

            if ($field->is_required && $answer === '') {
                throw ValidationException::withMessages([
                    'dynamic_answers.' . $field->field_key => $field->label . ' is required.',
                ]);
            }

            if ($answer === '') {
                continue;
            }

            $allowed = collect($field->options ?? [])
                ->filter(fn ($option) => is_scalar($option))
                ->map(fn ($option) => trim((string) $option))
                ->filter()
                ->values()
                ->all();

            if (! in_array($answer, $allowed, true)) {
                throw ValidationException::withMessages([
                    'dynamic_answers.' . $field->field_key => 'The selected answer is no longer available for ' . $field->label . '.',
                ]);
            }

            LeadFormAnswer::query()->create([
                'lead_id' => $lead->id,
                'lead_form_field_id' => $field->id,
                'field_key' => $field->field_key,
                'field_label' => $field->label,
                'answer' => $answer,
                'sort_order' => $field->sort_order,
            ]);
        }
    }

    /**
     * @param array<int, int|string> $serviceIds
     * @param array<int|string, string|null> $serviceNotes
     */
    public function syncServices(Lead $lead, array $serviceIds, array $serviceNotes = []): void
    {
        $ids = collect($serviceIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isNotEmpty()) {
            $existingCount = Service::query()
                ->whereNull('deleted_at')
                ->whereIn('id', $ids->all())
                ->count();

            if ($existingCount !== $ids->count()) {
                throw ValidationException::withMessages([
                    'service_ids' => 'One or more selected services are unavailable.',
                ]);
            }
        }

        $activeLinks = LeadService::query()
            ->where('lead_id', $lead->id)
            ->get();

        foreach ($activeLinks as $link) {
            if (! $ids->contains((int) $link->service_id)) {
                $link->delete();
            }
        }

        foreach ($ids as $serviceId) {
            $note = $serviceNotes[$serviceId] ?? $serviceNotes[(string) $serviceId] ?? null;

            $link = LeadService::withTrashed()
                ->where('lead_id', $lead->id)
                ->where('service_id', $serviceId)
                ->first();

            if ($link) {
                $link->notes = $note;
                $link->save();

                if ($link->trashed()) {
                    $link->restore();
                }

                continue;
            }

            LeadService::query()->create([
                'lead_id' => $lead->id,
                'service_id' => $serviceId,
                'notes' => $note,
            ]);
        }
    }

    public function createNote(
        Lead $lead,
        Model $author,
        string $note,
        bool $visibleToUser,
    ): LeadNote {
        return $lead->notes()->create([
            'author_type' => $author->getMorphClass(),
            'author_id' => $author->getKey(),
            'note' => $note,
            'visible_to_user' => $visibleToUser,
        ]);
    }

    public function generateReference(Lead $lead): string
    {
        return sprintf('BP-%s-%06d', now()->format('Y'), $lead->id);
    }

    /**
     * Options shown in Admin create forms.
     *
     * @return Collection<int, LeadFormField>
     */
    public function activeBookingFields(): Collection
    {
        return LeadFormField::query()->active()->ordered()->get();
    }

    public function safeSourcePageUrl(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (str_starts_with($value, '/')) {
            if (str_starts_with($value, '//')) {
                throw ValidationException::withMessages([
                    'source_page_url' => 'Protocol-relative URLs are not allowed.',
                ]);
            }

            return Str::limit($value, 255, '');
        }

        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));

        if (! in_array($scheme, ['http', 'https'], true) || filter_var($value, FILTER_VALIDATE_URL) === false) {
            throw ValidationException::withMessages([
                'source_page_url' => 'Source page URL must be a valid HTTP/HTTPS URL or a relative path beginning with "/".',
            ]);
        }

        return Str::limit($value, 255, '');
    }
}
